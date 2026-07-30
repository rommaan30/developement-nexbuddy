<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class VisitorNameDetector
{
    /**
     * Patterns use horizontal whitespace only so captures never spill onto the next line.
     *
     * @var array<int, string>
     */
    private const NAME_PATTERNS = [
        '/\bmy[ \t]+name[ \t]+is[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,3})/i',
        '/\bfull[ \t]+name[ \t]*(?:is|:|-)[ \t]*([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,3})/i',
        '/\bi[ \t]+am[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,2})/i',
        '/\bi[\'’]m[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,2})/i',
        '/\bthis[ \t]+is[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,2})/i',
        '/\bcall[ \t]+me[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,2})/i',
        '/\bi[ \t]+go[ \t]+by[ \t]+([A-Za-z][A-Za-z\'\-]*(?:[ \t]+[A-Za-z][A-Za-z\'\-]*){0,2})/i',
    ];

    /**
     * @var array<int, string>
     */
    private const BLOCKED_TOKENS = [
        'interested', 'looking', 'wondering', 'calling', 'writing', 'trying',
        'here', 'available', 'from', 'based', 'working', 'planning', 'hoping',
        'seeking', 'requesting', 'asking', 'reaching', 'contacting', 'emailing',
        'the', 'a', 'an', 'my', 'your', 'our', 'their', 'his', 'her',
        'not', 'also', 'just', 'still', 'already', 'really',
        'need', 'want', 'like', 'love', 'have', 'got', 'new',
        'and', 'or', 'but', 'for', 'with', 'in', 'on', 'at', 'to', 'of',
        'please', 'thanks', 'thank', 'hello', 'hi', 'hey', 'wait',
        'admin', 'user', 'customer', 'client', 'manager', 'director', 'founder',
        'ceo', 'cto', 'owner', 'anonymous', 'someone', 'anybody', 'everyone', 'nobody',
        'company', 'business', 'organisation', 'organization', 'firm', 'team',
        'technologies', 'technology', 'solutions', 'software', 'systems', 'services',
        'pvt', 'ltd', 'llc', 'llp', 'inc', 'corp', 'limited',
        'integration', 'api', 'website', 'web', 'app', 'mobile', 'chatbot', 'bot',
        'crm', 'hrms', 'pos', 'whatsapp', 'automation', 'pricing', 'price', 'quote',
        'quotation', 'demo', 'trial', 'support', 'sales', 'enterprise', 'partnership',
        'consultation', 'consulting', 'ecommerce', 'ecomm', 'ecom', 'marketing',
        'lead', 'generation', 'customer', 'service', 'product', 'requirement',
        'email', 'phone', 'number', 'contact', 'callback',
    ];

    public function __construct(
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * @return array{visitor_name: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        $text = $this->normalizer->normalize($text);

        foreach (self::NAME_PATTERNS as $pattern) {
            if (preg_match($pattern, $text, $matches) !== 1) {
                continue;
            }

            $name = $this->cleanName($matches[1]);

            if ($name === null) {
                continue;
            }

            return [
                'visitor_name' => $name,
                'found'        => true,
            ];
        }

        return [
            'visitor_name' => null,
            'found'        => false,
        ];
    }

    private function cleanName(string $raw): ?string
    {
        if (str_contains($raw, '@') || preg_match('/\d{3,}/', $raw) === 1) {
            return null;
        }

        $raw = preg_split('/\R|[.!,;:?]/', $raw)[0] ?? $raw;
        $raw = trim(preg_replace('/[ \t]+/', ' ', $raw) ?? '');
        $raw = trim($raw, " \t\n\r\0\x0B.,;:!?\"'");

        if ($raw === '') {
            return null;
        }

        $tokens = preg_split('/[ \t]+/', $raw) ?: [];
        $normalizedTokens = [];

        foreach ($tokens as $token) {
            $token = trim($token, ".,;:!?'\"-");

            if ($token === '' || mb_strlen($token) === 1 && ! ctype_alpha($token)) {
                break;
            }

            // Single-letter tokens (except initials mid-name) usually start a new sentence ("I need...").
            if (mb_strlen($token) === 1) {
                break;
            }

            if (! preg_match("/^[A-Za-z][A-Za-z'\\-]*$/", $token)) {
                break;
            }

            if (in_array(strtolower($token), self::BLOCKED_TOKENS, true)) {
                break;
            }

            $normalizedTokens[] = $this->titleCaseToken($token);

            if (count($normalizedTokens) >= 3) {
                break;
            }
        }

        if ($normalizedTokens === []) {
            return null;
        }

        $name = implode(' ', $normalizedTokens);

        if (mb_strlen($name) < 2 || mb_strlen($name) > 60) {
            return null;
        }

        if (strcasecmp($name, 'Anonymous User') === 0 || strcasecmp($name, 'Anonymous') === 0) {
            return null;
        }

        return $name;
    }

    private function titleCaseToken(string $token): string
    {
        if (str_contains($token, '-')) {
            return implode('-', array_map(
                static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)) . mb_strtolower(mb_substr($part, 1)),
                explode('-', $token)
            ));
        }

        if (str_contains($token, "'") || str_contains($token, '’')) {
            $token = str_replace('’', "'", $token);
            $parts = explode("'", $token);

            return implode("'", array_map(
                static fn (string $part): string => $part === ''
                    ? ''
                    : mb_strtoupper(mb_substr($part, 0, 1)) . mb_strtolower(mb_substr($part, 1)),
                $parts
            ));
        }

        return mb_strtoupper(mb_substr($token, 0, 1)) . mb_strtolower(mb_substr($token, 1));
    }
}
