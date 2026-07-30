<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

/**
 * Shared text cleanup used by all enquiry detectors.
 * Keeps extraction resilient to messy chat formatting without changing architecture.
 */
class TextNormalizer
{
    public function normalize(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);

        // Normalize fancy quotes / dashes / bullets.
        $text = str_replace(
            ["\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}", "\u{2013}", "\u{2014}", "\u{2022}", "\u{00B7}", '•', '·'],
            ["'", "'", '"', '"', '-', '-', '-', '-', '-', '-'],
            $text
        );

        // Soft line breaks and markdown leftovers.
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/```[\s\S]*?```/', ' ', $text) ?? $text;
        $text = preg_replace('/`([^`]+)`/', '$1', $text) ?? $text;
        $text = preg_replace('/^\s{0,3}#{1,6}\s+/m', '', $text) ?? $text;
        $text = preg_replace('/^\s*[-*+]\s+/m', '', $text) ?? $text;
        $text = preg_replace('/^\s*\d+\.\s+/m', '', $text) ?? $text;

        // Collapse horizontal whitespace only — keep newlines as entity boundaries.
        // NOTE: do not use \v / \s here; in PCRE \v includes newlines.
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{2,}/', "\n", $text) ?? $text;

        return trim($text);
    }
}
