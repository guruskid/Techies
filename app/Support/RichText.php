<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Minimal allow-list cleaner for HTML produced by the WYSIWYG editors.
 *
 * We do not depend on an external sanitizer package, so anything that is
 * rendered unescaped is passed through here first.
 */
class RichText
{
    /** @var array<int, string> */
    private const TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del',
        'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4',
        'blockquote', 'pre', 'code', 'table', 'thead', 'tbody',
        'tr', 'th', 'td', 'hr', 'figure', 'figcaption', 'img', 'span',
    ];

    /** @var array<string, array<int, string>> */
    private const ATTRIBUTES = [
        '*' => ['class'],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
    ];

    /**
     * Remove anything unsafe from editor HTML.
     */
    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div data-rich-root>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//script|//style|//iframe|//object|//embed|//form|//input|//button|//textarea|//select|//meta|//link|//svg|//math') as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach (iterator_to_array($xpath->query('//*')) as $element) {
            if (! $element instanceof DOMElement || $element->hasAttribute('data-rich-root')) {
                continue;
            }

            $tag = strtolower($element->nodeName);

            if (! in_array($tag, self::TAGS, true)) {
                self::unwrap($element);

                continue;
            }

            $allowed = array_merge(self::ATTRIBUTES['*'], self::ATTRIBUTES[$tag] ?? []);

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = (string) $attribute->nodeValue;

                if (! in_array($name, $allowed, true)) {
                    $element->removeAttribute($attribute->nodeName);

                    continue;
                }

                if (in_array($name, ['href', 'src'], true)
                    && preg_match('/^\s*(?:javascript|vbscript|data):/i', $value)) {
                    $element->removeAttribute($attribute->nodeName);
                }
            }

            if ($tag === 'a') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $root = $xpath->query('//*[@data-rich-root]')->item(0);
        if (! $root) {
            return '';
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * Render stored content: safe HTML for editor content, paragraphs for
     * older plain-text rows.
     */
    public static function render(?string $value): string
    {
        $value = (string) $value;

        if (trim($value) === '') {
            return '';
        }

        if ($value === strip_tags($value)) {
            $paragraphs = array_filter(array_map('trim', preg_split('/\R{2,}/', $value) ?: []));

            return implode('', array_map(
                fn (string $paragraph): string => '<p class="mb-5 last:mb-0 whitespace-pre-line">'.nl2br(e($paragraph)).'</p>',
                $paragraphs,
            ));
        }

        return self::clean($value) ?? '';
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
