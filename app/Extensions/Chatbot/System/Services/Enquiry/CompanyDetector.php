<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class CompanyDetector
{
    private const COMPANY_CONTEXT_PATTERN = '/\b(?:company|organization|organisation|business|firm)\b\s*(?:name\s*)?(?:is|:|-)?\s*([^\n.,;!?]{2,80})/i';

    private const COMPANY_SUFFIX_PATTERN = '/\b([A-Z][\w&.\'-]*(?:\s+[A-Z][\w&.\'-]*){0,5}\s+(?:Pvt\s+Ltd|Private\s+Limited|Ltd|Inc|LLP))\b/';

    /**
     * Detect a probable company name from enquiry text.
     *
     * @return array{company: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        foreach ([self::COMPANY_SUFFIX_PATTERN, self::COMPANY_CONTEXT_PATTERN] as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                return [
                    'company' => $this->cleanCompanyName($matches[1]),
                    'found'   => true,
                ];
            }
        }

        return [
            'company' => null,
            'found'   => false,
        ];
    }

    private function cleanCompanyName(string $company): string
    {
        $company = preg_split('/\s+\b(?:and|for|we|i|want|need|looking)\b/i', $company)[0] ?? $company;

        return trim(preg_replace('/\s+/', ' ', $company), " \t\n\r\0\x0B.,;:");
    }
}
