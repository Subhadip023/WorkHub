<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitizer for rich-text (Quill) content.
 *
 * Dependency-free (PHP DOM). Anything not explicitly allowed is removed:
 * - dangerous elements (script, style, iframe, object, svg, ...) are dropped with their content;
 * - other unknown elements are unwrapped (their text/children are kept);
 * - every attribute not on the allow-list is removed (on*, style, srcdoc, ...);
 * - URLs must be http(s), mailto, or same-site relative.
 */
class HtmlSanitizer
{
    /** @var array<string, list<string>> tag => allowed attributes */
    private const ALLOWED = [
        'p' => ['class'], 'br' => [], 'span' => ['class'], 'div' => ['class'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [],
        'sub' => [], 'sup' => [], 'mark' => [],
        'h1' => ['class'], 'h2' => ['class'], 'h3' => ['class'], 'h4' => ['class'], 'h5' => ['class'], 'h6' => ['class'],
        'ul' => ['class'], 'ol' => ['class'], 'li' => ['class', 'data-list'],
        'blockquote' => ['class'], 'pre' => ['class', 'spellcheck'], 'code' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        'hr' => [],
    ];

    /** Elements removed together with everything inside them. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
        'svg', 'math', 'template', 'noscript', 'form', 'input', 'button', 'textarea',
        'select', 'link', 'meta', 'base', 'head', 'title', 'video', 'audio', 'source',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        // Plain text without any tag start cannot form markup; keep it unchanged
        // (escaping here would double-encode entities in escaped previews).
        if (! preg_match('/<[a-z!\/?]/i', $html)) {
            return $html;
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"?><div id="__sanitize_root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__sanitize_root');
        if (! $root) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement) {
                self::sanitizeElement($node);
            } elseif ($node->nodeType === XML_COMMENT_NODE
                || $node->nodeType === XML_PI_NODE
                || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);
            }
        }
    }

    private static function sanitizeElement(DOMElement $el): void
    {
        $tag = strtolower($el->nodeName);

        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            $el->parentNode?->removeChild($el);

            return;
        }

        // Sanitize descendants first so unwrapped children are already clean.
        self::sanitizeChildren($el);

        if (! array_key_exists($tag, self::ALLOWED)) {
            $parent = $el->parentNode;
            if ($parent) {
                while ($el->firstChild) {
                    $parent->insertBefore($el->firstChild, $el);
                }
                $parent->removeChild($el);
            }

            return;
        }

        $allowedAttrs = self::ALLOWED[$tag];
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            if (! in_array($name, $allowedAttrs, true)) {
                $el->removeAttribute($attr->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! self::isSafeUrl($attr->nodeValue ?? '')) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        if ($tag === 'a' && $el->getAttribute('target') !== '') {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        // Strip whitespace/control chars browsers ignore (e.g. "java\nscript:").
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        if ($normalized === '') {
            return false;
        }

        if (str_starts_with($normalized, '//')) {
            return false; // protocol-relative to another host
        }

        if (preg_match('/^[a-z][a-z0-9+.\-]*:/', $normalized, $m)) {
            return in_array(rtrim($m[0], ':'), ['http', 'https', 'mailto'], true);
        }

        return true; // relative URL (/storage/..., #anchor, path)
    }
}
