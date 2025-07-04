<?php

namespace Mattitja\Cision\Support;

use DOMNode;
use DOMText;
use DOMElement;

class HtmlCleaner
{
    /**
     * Render a DOM node into safe HTML, preserving allowed block and inline tags.
     */
    public static function renderNode(DOMNode $node, array $allowedTags, array $allowedInline, string $parentTag = ''): ?string
    {
        if ($node instanceof DOMText) {
            return $node->wholeText;
        }

        if (!($node instanceof DOMElement)) {
            return null;
        }

        $tag = $node->nodeName;

        // Special case: handle <br> within <p>
        if ($tag === 'br') {
            if ($parentTag === 'p') {
                $prev = $node->previousSibling;
                $next = $node->nextSibling;

                $hasTextBefore = $prev instanceof DOMText && trim($prev->textContent) !== '';
                $hasTextAfter = $next instanceof DOMText && trim($next->textContent) !== '';

                if ($hasTextBefore && $hasTextAfter) {
                    return '<br>';
                }
            }

            // Skip all other <br>
            return null;
        }

        // Recursively render children
        $inner = '';
        foreach ($node->childNodes as $child) {
            $rendered = self::renderNode($child, $allowedTags, $allowedInline, $tag);
            if ($rendered !== null) {
                $inner .= $rendered;
            }
        }

        // Handle block tags
        if (in_array($tag, $allowedTags)) {
            if ($tag === 'p') {
                $html = $node->C14N();
                $isItalic = str_starts_with(trim($html), '<em') || str_starts_with(trim($html), '<i');
                $class = $isItalic ? ' class="italic"' : '';
                return "<p{$class}>" . trim($inner) . "</p>";
            }

            return "<{$tag}>" . trim($inner) . "</{$tag}>";
        }

        // Handle inline tags
        if (in_array($tag, $allowedInline)) {
            return "<{$tag}>{$inner}</{$tag}>";
        }

        // Unknown tags are ignored but their content is preserved
        return $inner;
    }

    /**
     * Clean a raw HTML string: remove unwanted tags, empty elements, garbage phrases, and excessive whitespace.
     */
    public static function clean(string $html): string
    {
        // Remove first <h1>
        $html = preg_replace('/<h1\b[^>]*>.*?<\/h1>/i', '', $html, 1);

        // Remove empty tags like <i></i>, <span></span>
        $html = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/', '', $html);

        // Remove specific garbage strings
        $unwantedStrings = [
            'Report this content',
            '<p><strong>Taggar:</strong></p>',
        ];

        foreach ($unwantedStrings as $str) {
            $html = str_replace($str, '', $html);
        }

        // Remove localized date lines like: fre, jul 04, 2025 09:55 CET
        $html = preg_replace('/\b[a-z]{3},\s[a-z]{3}\s\d{2},\s\d{4}\s\d{2}:\d{2}\sCET\b/i', '', $html);

        // Normalize whitespace
        $html = str_replace(["\u{A0}", "\t", "\r"], ' ', $html);          // Remove non-breaking spaces and tabs
        $html = preg_replace('/[ ]{2,}/', ' ', $html);                    // Collapse double spaces
        $html = preg_replace("/(\n\s*){2,}/", "\n", $html);               // Collapse multiple newlines
        $html = str_replace(['  ', ' \n'], '', $html);                    // Additional spacing clean
        $html = trim($html);

        return $html;
    }
}