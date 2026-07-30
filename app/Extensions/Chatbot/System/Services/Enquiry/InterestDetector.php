<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class InterestDetector
{
    /**
     * Recognised interest labels mapped to match phrases (longest / most specific first).
     *
     * @param  array<string, array<int, string>>  $keywordGroups
     */
    public function __construct(
        private readonly array $keywordGroups = [
            'Website Development'        => ['website development', 'web development', 'website', 'web app'],
            'Mobile App Development'     => ['mobile app development', 'mobile application', 'mobile app', 'android app', 'ios app'],
            'AI Chatbot'                 => ['ai chatbot', 'ai bot', 'chatbot', 'chat bot'],
            'Business Automation'        => ['business automation', 'process automation', 'automation'],
            'Customer Support'           => ['customer support', 'customer service'],
            'Lead Generation'            => ['lead generation', 'generate leads'],
            'Enterprise Solution'        => ['enterprise solution', 'enterprise plan', 'enterprise'],
            'Technical Support'          => ['technical support', 'tech support'],
            'CRM'                        => ['crm'],
            'HRMS'                       => ['hrms', 'hr management'],
            'POS'                        => ['pos system', 'point of sale', 'pos'],
            'Pricing'                    => ['pricing', 'price', 'quotation', 'quote', 'cost'],
            'Demo'                       => ['product demo', 'demo', 'trial'],
            'Consultation'               => ['consultation', 'consulting', 'consultancy'],
            'Partnership'                => ['partnership', 'partner with', 'reseller'],
            'Sales'                      => ['buy', 'purchase', 'subscription', 'ecommerce', 'e-commerce', 'ecomm', 'ecom', 'digital marketing'],
            'Support'                    => ['issue', 'help', 'support'],
            'Callback'                   => ['call me', 'callback', 'reach me', 'contact me'],
            'API Integration'            => ['api integration', 'rest api', 'api'],
            'WhatsApp Integration'       => ['whatsapp integration', 'whatsapp api', 'whatsapp'],
        ],
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
            foreach ($keywords as $keyword) {
                if (! $this->containsKeyword($text, $keyword)) {
                    continue;
                }

                if (! in_array($interest, $interests, true)) {
                    $interests[] = $interest;
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
            foreach ($keywords as $keyword) {
                if ($this->containsKeyword($text, $keyword)) {
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
        return array_keys($this->keywordGroups);
    }

    private function containsKeyword(string $text, string $keyword): bool
    {
        // Multi-word phrases: allow flexible whitespace.
        $pattern = '/\b' . preg_replace('/\s+/', '\s+', preg_quote($keyword, '/')) . '\b/i';

        return preg_match($pattern, $text) === 1;
    }
}
