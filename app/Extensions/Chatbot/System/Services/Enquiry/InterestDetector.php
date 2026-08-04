<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class InterestDetector
{
    /**
     * @param  array<string, array<int, string>>  $keywordGroups  label => match phrases
     */
    public function __construct(
        private readonly array $keywordGroups = [],
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * Detect all unique enquiry intents mentioned in the text.
     *
     * @return array{
     *     interest: string|null,
     *     matched_keyword: string|null,
     *     interests: array<int, string>,
     *     found: bool
     * }
     */
    public function detect(string $text): array
    {
        $text = $this->normalizer->normalize($text);
        $interests = [];
        $matchedKeywords = [];

        foreach ($this->keywordGroups as $interest => $keywords) {
            if (! is_array($keywords)) {
                continue;
            }

            foreach ($keywords as $keyword) {
                if (! is_string($keyword) || $keyword === '') {
                    continue;
                }

                if (! $this->containsKeyword($text, $keyword)) {
                    continue;
                }

                if (! in_array($interest, $interests, true)) {
                    $interests[] = (string) $interest;
                }

                if (! in_array($keyword, $matchedKeywords, true)) {
                    $matchedKeywords[] = $keyword;
                }

                break;
            }
        }

        if ($interests === []) {
            return [
                'interest'        => null,
                'matched_keyword' => null,
                'interests'       => [],
                'found'           => false,
            ];
        }

        return [
            'interest'        => implode(', ', $interests),
            'matched_keyword' => implode(', ', $matchedKeywords),
            'interests'       => $interests,
            'found'           => true,
        ];
    }

    public function countMatches(string $text): int
    {
        $text = $this->normalizer->normalize($text);
        $count = 0;

        foreach ($this->keywordGroups as $keywords) {
            if (! is_array($keywords)) {
                continue;
            }

            foreach ($keywords as $keyword) {
                if (is_string($keyword) && $keyword !== '' && $this->containsKeyword($text, $keyword)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    public function recognisedLabels(): array
    {
        return array_map('strval', array_keys($this->keywordGroups));
    }

    private function containsKeyword(string $text, string $keyword): bool
    {
        $pattern = '/\b' . preg_replace('/\s+/', '\s+', preg_quote($keyword, '/')) . '\b/i';

        return preg_match($pattern, $text) === 1;
    }
}
