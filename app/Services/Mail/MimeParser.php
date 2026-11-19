<?php

namespace App\Services\Mail;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Minimal MIME parser for raw messages fetched from IMAP.
 *
 * Handles the common cases: single part text/HTML, alternatives and mixed
 * multipart messages, base64 and quoted-printable transfer encodings.
 */
class MimeParser
{
    /**
     * @var array<int, array{filename: string, mime_type: string, content: string}>
     */
    private array $attachments = [];

    /**
     * @return array{
     *     from_email: string|null,
     *     from_name: string|null,
     *     to_email: string|null,
     *     to_name: string|null,
     *     subject: string,
     *     body_text: string|null,
     *     body_html: string|null,
     *     message_id: string|null,
     *     in_reply_to: string|null,
     *     date: Carbon|null,
     *     attachments: array<int, array{filename: string, mime_type: string, content: string}>
     * }
     */
    public function parse(string $raw): array
    {
        $this->attachments = [];

        [$headerBlock, $body] = $this->split($raw);
        $headers = $this->headers($headerBlock);
        $parts = $this->parts($headers, $body);

        [$fromEmail, $fromName] = $this->address($headers['from'] ?? null);
        [$toEmail, $toName] = $this->address($headers['to'] ?? null);

        $date = null;
        if (filled($headers['date'] ?? null)) {
            try {
                $date = Carbon::parse($headers['date']);
            } catch (Throwable) {
                $date = null;
            }
        }

        return [
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $this->decodeHeader($headers['subject'] ?? '(no subject)'),
            'body_text' => $parts['text'] !== '' ? trim($parts['text']) : null,
            'body_html' => $parts['html'] !== '' ? trim($parts['html']) : null,
            'message_id' => $this->cleanId($headers['message-id'] ?? null),
            'in_reply_to' => $this->cleanId($headers['in-reply-to'] ?? null),
            'date' => $date,
            'attachments' => $this->attachments,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function split(string $raw): array
    {
        if (preg_match("/\r?\n\r?\n/", $raw, $match, PREG_OFFSET_CAPTURE)) {
            $position = $match[0][1];

            return [substr($raw, 0, $position), substr($raw, $position + strlen($match[0][0]))];
        }

        return [$raw, ''];
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $block): array
    {
        $block = preg_replace("/\r?\n[ \t]+/", ' ', $block) ?? $block;
        $headers = [];

        foreach (preg_split("/\r?\n/", $block) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $key = strtolower(trim($name));

            if (! array_key_exists($key, $headers)) {
                $headers[$key] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * @param  array<string, string>  $headers
     * @return array{text: string, html: string}
     */
    private function parts(array $headers, string $body): array
    {
        $contentType = $headers['content-type'] ?? 'text/plain';
        $boundary = $this->parameter($contentType, 'boundary');

        if (str_starts_with(strtolower($contentType), 'multipart/') && $boundary) {
            return $this->multipart($body, $boundary);
        }

        $decoded = $this->decodeBody($body, $headers);
        $mime = strtolower(strtok($contentType, ';') ?: 'text/plain');

        if ($mime === 'text/html') {
            return ['text' => '', 'html' => $decoded];
        }

        if ($mime === 'text/plain') {
            return ['text' => $decoded, 'html' => ''];
        }

        $filename = $this->attachmentName($headers);
        $disposition = strtolower($headers['content-disposition'] ?? '');

        if ($filename !== null || str_contains($disposition, 'attachment')) {
            $this->attachments[] = [
                'filename' => $filename ?: 'attachment-'.(count($this->attachments) + 1),
                'mime_type' => $mime ?: 'application/octet-stream',
                'content' => $this->decodeBinary($body, $headers),
            ];
        }

        return ['text' => '', 'html' => ''];
    }

    /**
     * @return array{text: string, html: string}
     */
    private function multipart(string $body, string $boundary): array
    {
        $result = ['text' => '', 'html' => ''];
        $segments = explode('--'.$boundary, $body);

        foreach (array_slice($segments, 1) as $segment) {
            if (str_starts_with(ltrim($segment), '--')) {
                break;
            }

            $segment = preg_replace("/^\r?\n/", '', $segment, 1) ?? $segment;
            [$partHeaders, $partBody] = $this->split($segment);
            $parts = $this->parts($this->headers($partHeaders), $partBody);

            if ($result['text'] === '' && $parts['text'] !== '') {
                $result['text'] = $parts['text'];
            }

            if ($result['html'] === '' && $parts['html'] !== '') {
                $result['html'] = $parts['html'];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function decodeBody(string $body, array $headers): string
    {
        $encoding = strtolower(trim($headers['content-transfer-encoding'] ?? ''));
        $charset = $this->parameter($headers['content-type'] ?? '', 'charset');

        $decoded = match ($encoding) {
            'base64' => (string) base64_decode(preg_replace('/\s+/', '', $body) ?? '', true),
            'quoted-printable' => quoted_printable_decode($body),
            default => $body,
        };

        if ($charset && ! in_array(strtolower($charset), ['utf-8', 'us-ascii', 'ascii'], true)) {
            try {
                $decoded = mb_convert_encoding($decoded, 'UTF-8', $charset);
            } catch (Throwable) {
                // Keep the original text when the charset is unknown.
            }
        }

        return $decoded;
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function decodeBinary(string $body, array $headers): string
    {
        $encoding = strtolower(trim($headers['content-transfer-encoding'] ?? ''));

        return match ($encoding) {
            'base64' => (string) base64_decode(preg_replace('/\s+/', '', $body) ?? '', true),
            'quoted-printable' => quoted_printable_decode($body),
            default => $body,
        };
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function attachmentName(array $headers): ?string
    {
        $name = $this->parameter($headers['content-disposition'] ?? '', 'filename')
            ?? $this->parameter($headers['content-type'] ?? '', 'name');

        if (blank($name)) {
            return null;
        }

        $name = basename(str_replace('\\', '/', $this->decodeHeader((string) $name)));

        return $name !== '' ? $name : null;
    }

    private function parameter(string $header, string $key): ?string
    {
        if (preg_match('/'.preg_quote($key, '/').'\s*=\s*"?([^";]+)"?/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function address(?string $header): array
    {
        if (blank($header)) {
            return [null, null];
        }

        $header = $this->decodeHeader($header);

        if (preg_match('/^(.*)<([^>]+)>$/', $header, $matches)) {
            return [trim($matches[2]), trim($matches[1], " \t\"'") ?: null];
        }

        return [trim($header), null];
    }

    private function decodeHeader(string $value): string
    {
        if (function_exists('iconv_mime_decode')) {
            $decoded = @iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return mb_decode_mimeheader($value);
    }

    private function cleanId(?string $value): ?string
    {
        return filled($value) ? trim($value, " \t<>") : null;
    }
}
