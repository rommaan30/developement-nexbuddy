<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class InterestDetector
{
    /**
     * @param  array<string, array<int, string>>  $keywordGroups
     */
    public function __construct(
        private readonly array $keywordGroups = [
            'Pricing'     => ['pricing', 'price', 'quotation', 'quote', 'cost'],
            'Sales'       => ['buy', 'purchase', 'subscription', 'plan', 'enterprise'],
            'Demo'        => ['demo', 'trial', 'testing'],
            'Integration' => ['integration', 'api', 'website', 'embed'],
            'Support'     => ['issue', 'help', 'support'],
            'Callback'    => ['contact', 'call me', 'callback', 'reach me'],
        ],
    ) {}

    /**
     * Detect the first matching enquiry intent.
     *
     * @return array{interest: string|null, matched_keyword: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        foreach ($this->keywordGroups as $interest => $keywords) {
            foreach ($keywords as $keyword) {
                if ($this->containsKeyword($text, $keyword)) {
                    return [
                        'interest'        => $interest,
                        'matched_keyword' => $keyword,
                        'found'           => true,
                    ];
                }
            }
        }

        return [
            'interest'        => null,
            'matched_keyword' => null,
            'found'           => false,
        ];
    }

    public function countMatches(string $text): int
    {
        $count = 0;

        foreach ($this->keywordGroups as $keywords) {
            foreach ($keywords as $keyword) {
                if ($this->containsKeyword($text, $keyword)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function containsKeyword(string $text, string $keyword): bool
    {
        return preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text) === 1;
    }
}
