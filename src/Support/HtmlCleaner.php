<?php

namespace Mattitja\Cision\Support;

use DOMNode;
use DOMText;
use DOMElement;

class HtmlCleaner
{
    /**
     * Renders a DOM node into safe HTML, preserving only allowed tags.
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
            return null;
        }

        $inner = '';

        foreach ($node->childNodes as $child) {
            $rendered = self::renderNode($child, $allowedTags, $allowedInline, $tag);
            if ($rendered !== null) {
                $inner .= $rendered;
            }
        }

        if (in_array($tag, $allowedTags)) {
            if ($tag === 'p') {
                $html = $node->C14N();
                $isItalic = str_starts_with(trim($html), '<em') || str_starts_with(trim($html), '<i');
                $class = $isItalic ? ' class="italic"' : '';
                return "<p{$class}>" . trim($inner) . "</p>";
            }
            return "<{$tag}>" . trim($inner) . "</{$tag}>";
        }

        if (in_array($tag, $allowedInline)) {
            return "<{$tag}>{$inner}</{$tag}>";
        }

        return $inner;
    }

    /**
     * Cleans a raw HTML string: removes unwanted content, empty tags, and excess whitespace.
     */
    public static function clean(string $html): string
    {
        $html = preg_replace('/<h1\b[^>]*>.*?<\/h1>/i', '', $html, 1);
        $html = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/', '', $html);

        $unwantedStrings = [
            'Report this content',
            '<p><strong>Taggar:</strong></p>',
        ];

        foreach ($unwantedStrings as $str) {
            $html = str_replace($str, '', $html);
        }

        $html = preg_replace('/\b[a-z]{3},\s[a-z]{3}\s\d{2},\s\d{4}\s\d{2}:\d{2}\sCET\b/i', '', $html);
        $html = str_replace(["\u{A0}", "\t", "\r"], ' ', $html);
        $html = preg_replace('/[ ]{2,}/', ' ', $html);
        $html = preg_replace("/(\n\s*){2,}/", "\n", $html);
        $html = str_replace(['  ', ' \n'], '', $html);

        return trim($html);
    }
}
