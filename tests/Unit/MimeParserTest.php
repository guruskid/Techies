<?php

namespace Tests\Unit;

use App\Services\Mail\MimeParser;
use PHPUnit\Framework\TestCase;

class MimeParserTest extends TestCase
{
    public function test_it_parses_a_multipart_alternative_message(): void
    {
        $html = base64_encode('<b>Hello world</b>');
        $raw = implode("\r\n", [
            'From: "Ada Lovelace" <ada@example.org>',
            'To: Community Team <hello@example.org>',
            'Subject: A multipart reply',
            'Message-ID: <abc123@example.org>',
            'Date: Wed, 19 Nov 2026 10:00:00 +0000',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="BOUNDARY"',
            '',
            '--BOUNDARY',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            'Hello=20world',
            '--BOUNDARY',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            $html,
            '--BOUNDARY--',
            '',
        ]);

        $parsed = (new MimeParser)->parse($raw);

        $this->assertSame('ada@example.org', $parsed['from_email']);
        $this->assertSame('Ada Lovelace', $parsed['from_name']);
        $this->assertSame('hello@example.org', $parsed['to_email']);
        $this->assertSame('A multipart reply', $parsed['subject']);
        $this->assertSame('Hello world', $parsed['body_text']);
        $this->assertSame('<b>Hello world</b>', $parsed['body_html']);
        $this->assertSame('abc123@example.org', $parsed['message_id']);
    }

    public function test_it_decodes_encoded_subject_headers(): void
    {
        $raw = implode("\r\n", [
            'From: grace@example.org',
            'Subject: =?UTF-8?B?'.base64_encode('Hello 👋').'?=',
            'Content-Type: text/plain; charset=UTF-8',
            '',
            'Plain body',
        ]);

        $parsed = (new MimeParser)->parse($raw);

        $this->assertSame('Hello 👋', $parsed['subject']);
        $this->assertSame('grace@example.org', $parsed['from_email']);
        $this->assertNull($parsed['from_name']);
    }

    public function test_it_parses_a_single_part_html_message(): void
    {
        $raw = implode("\r\n", [
            'From: hello@example.org',
            'Subject: HTML only',
            'Content-Type: text/html; charset=UTF-8',
            '',
            '<p>Hi there</p>',
        ]);

        $parsed = (new MimeParser)->parse($raw);

        $this->assertNull($parsed['body_text']);
        $this->assertStringContainsString('<p>Hi there</p>', (string) $parsed['body_html']);
    }
}
