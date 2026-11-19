<?php

namespace App\Services\Mail;

use RuntimeException;

/**
 * Small IMAP client over a socket.
 *
 * PHP's IMAP extension is not always available, so this supports the few
 * commands the mail hub needs: STARTTLS/SSL, LOGIN, SELECT, UID SEARCH and
 * UID FETCH of the raw message body.
 */
class ImapClient
{
    /** @var resource|null */
    private $stream;

    private int $counter = 0;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $username,
        private readonly string $password,
        private readonly string $encryption = 'ssl',
    ) {}

    public function connect(): void
    {
        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $stream = @stream_socket_client(
            $scheme.'://'.$this->host.':'.$this->port,
            $errorNumber,
            $errorMessage,
            20,
        );

        if ($stream === false) {
            throw new RuntimeException("Could not connect to {$this->host}:{$this->port} — {$errorMessage}");
        }

        $this->stream = $stream;
        stream_set_timeout($this->stream, 20);

        $greeting = fgets($this->stream);

        if ($greeting === false || ! preg_match('/^\* (OK|PREAUTH)/i', $greeting)) {
            $this->close();

            throw new RuntimeException('The mail server did not accept the connection.');
        }

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS');

            if (! stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->close();

                throw new RuntimeException('Could not start a secure TLS session with the mail server.');
            }
        }

        $this->command('LOGIN '.$this->quote($this->username).' '.$this->quote($this->password));
    }

    /**
     * @return array<int, int>
     */
    public function search(string $criteria = 'ALL'): array
    {
        $response = $this->command('UID SEARCH '.$criteria);

        if (! preg_match('/^\* SEARCH(.*)$/mi', $response, $matches)) {
            return [];
        }

        return collect(explode(' ', trim($matches[1])))
            ->filter(fn (string $uid) => ctype_digit($uid))
            ->map(fn (string $uid) => (int) $uid)
            ->values()
            ->all();
    }

    public function select(string $mailbox = 'INBOX'): void
    {
        $this->command('SELECT '.$this->quote($mailbox));
    }

    public function fetchRaw(int $uid): ?string
    {
        $tag = $this->write('UID FETCH '.$uid.' (BODY.PEEK[])');
        $raw = null;

        while (($line = fgets($this->stream)) !== false) {
            if (preg_match('/\{(\d+)\}\r?\n$/', $line, $matches)) {
                $length = (int) $matches[1];
                $raw = $this->readBytes($length);

                continue;
            }

            if (str_starts_with($line, $tag.' ')) {
                if (preg_match('/^\S+ (BAD|NO)/i', $line)) {
                    throw new RuntimeException('IMAP fetch failed: '.trim($line));
                }

                break;
            }
        }

        return $raw;
    }

    public function close(): void
    {
        if (is_resource($this->stream)) {
            @fwrite($this->stream, 'A'.(++$this->counter)." LOGOUT\r\n");
            @fclose($this->stream);
        }

        $this->stream = null;
    }

    private function command(string $command): string
    {
        $tag = $this->write($command);
        $response = '';

        while (($line = fgets($this->stream)) !== false) {
            $response .= $line;

            if (str_starts_with($line, $tag.' ')) {
                if (! preg_match('/^\S+ OK/i', $line)) {
                    throw new RuntimeException('IMAP command failed: '.trim($line));
                }

                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('The mail server closed the connection unexpectedly.');
        }

        return $response;
    }

    private function write(string $command): string
    {
        if (! is_resource($this->stream)) {
            throw new RuntimeException('The IMAP connection is not open.');
        }

        $tag = 'A'.(++$this->counter);
        fwrite($this->stream, $tag.' '.$command."\r\n");

        return $tag;
    }

    private function readBytes(int $length): string
    {
        $data = '';

        while (strlen($data) < $length) {
            $chunk = fread($this->stream, $length - strlen($data));

            if ($chunk === false || $chunk === '') {
                break;
            }

            $data .= $chunk;
        }

        return $data;
    }

    private function quote(string $value): string
    {
        return '"'.addcslashes($value, '\\"').'"';
    }
}
