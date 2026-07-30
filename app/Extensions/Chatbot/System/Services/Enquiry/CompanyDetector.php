<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class CompanyDetector
{
    /**
     * Most specific company-context phrases first.
     * Captures are limited to the same line and stopped before contact/intent words.
     *
     * @var array<int, string>
     */
    private const COMPANY_CONTEXT_PATTERNS = [
        '/\b(?:company|organization|organisation|business|firm)[ \t]+name[ \t]*(?:is|:|-)[ \t]*([^\n@]{2,80})/i',
        '/\bour[ \t]+(?:company|organization|organisation|business|firm)[ \t]+(?:is|name[ \t]+is|:)[ \t]*([^\n@]{2,80})/i',
        '/\b(?:company|organization|organisation|business|firm)[ \t]+(?:is|:|-)[ \t]*([^\n@]{2,80})/i',
        '/\bi[ \t]+(?:work|am[ \t]+working)[ \t]+(?:at|for|with)[ \t]+([^\n@]{2,80})/i',
        '/\bi(?:\'|’)?m[ \t]+from[ \t]+([^\n@]{2,80})/i',
        '/\bi[ \t]+am[ \t]+from[ \t]+([^\n@]{2,80})/i',
        '/\bi(?:\'|’)?m[ \t]+repres(?:e)?nt(?:ing)?[ \t]+([^\n@]{2,80})/i',
        '/\bi[ \t]+am[ \t]+repres(?:e)?nt(?:ing)?[ \t]+([^\n@]{2,80})/i',
        '/\brepres(?:e)?nt(?:ing)?[ \t]+([^\n@]{2,80})/i',
        '/\bon[ \t]+behalf[ \t]+of[ \t]+([^\n@]{2,80})/i',
        '/\bi[ \t]+own[ \t]+([^\n@]{2,80})/i',
        '/\bfounder[ \t]+of[ \t]+([^\n@]{2,80})/i',
        '/\b(?:ceo|cto|coo|cfo|director|owner)[ \t]+(?:at|of)[ \t]+([^\n@]{2,80})/i',
        '/\bworking[ \t]+(?:at|for|with)[ \t]+([^\n@]{2,80})/i',
    ];

    private const COMPANY_SUFFIX_PATTERN = '/\b((?!I\b|We\b|My\b|A\b|An\b|The\b|For\b|To\b|Of\b|And\b|Work\b|At\b|With\b|From\b|Is\b|Name\b|Business\b)[A-Za-z][\w&.\'-]*(?:[ \t]+[A-Za-z][\w&.\'-]*){0,5}[ \t]+(?:Pvt[ \t]*\.?[ \t]*Ltd\.?|Private[ \t]+Limited|Ltd\.?|LLC|LLP|Inc\.?|Corp\.?|Corporation|Limited|PLC|GmbH|AG))\b/i';

    /**
     * @var array<int, string>
     */
    private const STOP_WORDS = [
        'and', 'but', 'because', 'since', 'while', 'when', 'where', 'who', 'which', 'that',
        'i', 'we', 'my', 'our', 'me', 'us', 'is', 'am', 'are',
        'want', 'need', 'looking', 'interested', 'please', 'thanks', 'thank',
        'email', 'phone', 'mobile', 'number', 'contact', 'call',
        'pricing', 'price', 'quote', 'demo', 'trial', 'integration', 'api',
        'website', 'support', 'help', 'crm', 'hrms', 'pos', 'chatbot',
        'business', 'name', 'organisation', 'organization', 'company', 'firm',
    ];

    /**
     * @var array<int, string>
     */
    private const BLOCKED_COMPANIES = [
        'a company', 'the company', 'our company', 'my company',
        'a business', 'the business', 'home', 'here', 'there',
        'email', 'phone', 'support', 'sales', 'work',
        'requirement', 'business requirement', 'product interest',
        'visitor name', 'company name', 'email address', 'phone number',
        'my name is', 'name is', 'anonymous', 'anonymous user',
    ];

    public function __construct(
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * @return array{company: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        $text = $this->normalizer->normalize($text);

        if (preg_match(self::COMPANY_SUFFIX_PATTERN, $text, $matches) === 1) {
            $company = $this->cleanCompanyName($matches[1]);

            if ($company !== null) {
                return [
                    'company' => $company,
                    'found'   => true,
                ];
            }
        }

        foreach (self::COMPANY_CONTEXT_PATTERNS as $pattern) {
            if (preg_match($pattern, $text, $matches) !== 1) {
                continue;
            }

            $company = $this->cleanCompanyName($matches[1]);

            if ($company === null) {
                continue;
            }

            return [
                'company' => $company,
                'found'   => true,
            ];
        }

        return [
            'company' => null,
            'found'   => false,
        ];
    }

    private function cleanCompanyName(string $company): ?string
    {
        $company = preg_split('/\R/u', $company)[0] ?? $company;
        $company = preg_split('/@|\bhttps?:\/\/|\bwww\./i', $company)[0] ?? $company;
        $company = preg_replace('/^(?:is|name|are)\s+/i', '', trim($company)) ?? $company;

        $tokens = preg_split('/[ \t]+/', trim($company)) ?: [];
        $kept = [];

        foreach ($tokens as $token) {
            $plain = strtolower(trim($token, ".,;:!?\"'()-"));

            if ($plain === '') {
                break;
            }

            if (in_array($plain, self::STOP_WORDS, true)) {
                break;
            }

            // Stop before email local-parts / phone digits / urls.
            if (preg_match('/\d{3,}/', $plain) === 1 || str_contains($plain, '@')) {
                break;
            }

            $kept[] = trim($token, ".,;:!?\"'");

            if (count($kept) >= 8) {
                break;
            }
        }

        $company = trim(preg_replace('/\s+/', ' ', implode(' ', $kept)) ?? '');
        $company = preg_replace('/^(?:work(?:ing)?\s+(?:at|for|with)|at|from|with|is)\s+/i', '', $company) ?? $company;
        $company = trim($company, " \t\n\r\0\x0B.,;:!?\"'•\-");

        if ($company === '' || mb_strlen($company) < 2 || mb_strlen($company) > 80) {
            return null;
        }

        $lower = strtolower($company);

        foreach (self::BLOCKED_COMPANIES as $blocked) {
            if ($lower === $blocked || str_starts_with($lower, $blocked . ' ')) {
                return null;
            }
        }

        if (preg_match('/^my\s+name\s+is\b/i', $company) === 1) {
            return null;
        }

        if (preg_match('/\b(integration|pricing|demo|email|phone|whatsapp|chatbot)\b/i', $company) === 1) {
            return null;
        }

        return $company;
    }
}
