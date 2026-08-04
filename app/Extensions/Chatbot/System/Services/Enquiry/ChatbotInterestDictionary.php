<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Enquiry;

/**
 * Source of truth for chatbot-owned interest dictionaries.
 *
 * Storage format on ext_chatbots.enquiry_interests:
 *   { "Label": ["keyword", "phrase"], ... }
 */
final class ChatbotInterestDictionary
{
    /**
     * Default dictionary applied to new chatbots (previous global list).
     *
     * @return array<string, array<int, string>>
     */
    public static function defaultSoftware(): array
    {
        return [
            'Website Development'    => ['website development', 'web development', 'website', 'web app'],
            'Mobile App Development' => ['mobile app development', 'mobile application', 'mobile app', 'android app', 'ios app'],
            'AI Chatbot'             => ['ai chatbot', 'ai bot', 'chatbot', 'chat bot'],
            'Business Automation'    => ['business automation', 'process automation', 'automation'],
            'Customer Support'       => ['customer support', 'customer service'],
            'Lead Generation'        => ['lead generation', 'generate leads'],
            'Enterprise Solution'    => ['enterprise solution', 'enterprise plan', 'enterprise'],
            'Technical Support'      => ['technical support', 'tech support'],
            'CRM'                    => ['crm'],
            'HRMS'                   => ['hrms', 'hr management'],
            'POS'                    => ['pos system', 'point of sale', 'pos'],
            'Pricing'                => ['pricing', 'price', 'quotation', 'quote', 'cost'],
            'Demo'                   => ['product demo', 'demo', 'trial'],
            'Consultation'           => ['consultation', 'consulting', 'consultancy'],
            'Partnership'            => ['partnership', 'partner with', 'reseller'],
            'Sales'                  => ['buy', 'purchase', 'subscription', 'ecommerce', 'e-commerce', 'ecomm', 'ecom', 'digital marketing'],
            'Support'                => ['issue', 'help', 'support'],
            'Callback'               => ['call me', 'callback', 'reach me', 'contact me'],
            'API Integration'        => ['api integration', 'rest api', 'api'],
            'WhatsApp Integration'   => ['whatsapp integration', 'whatsapp api', 'whatsapp'],
        ];
    }

    /**
     * Accept map or UI list entries and return a clean label => keywords map.
     *
     * @return array<string, array<int, string>>
     */
    public static function normalize(mixed $value): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        // List form from the editor: [{label, keywords}, ...]
        if (array_is_list($value)) {
            $map = [];

            foreach ($value as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $label = trim((string) ($row['label'] ?? ''));

                if ($label === '') {
                    continue;
                }

                $keywords = $row['keywords'] ?? [];

                if (is_string($keywords)) {
                    $keywords = preg_split('/\s*,\s*/', $keywords) ?: [];
                }

                if (! is_array($keywords)) {
                    continue;
                }

                $keywords = array_values(array_filter(array_map(
                    static fn ($keyword): string => mb_strtolower(trim((string) $keyword)),
                    $keywords
                ), static fn (string $keyword): bool => $keyword !== ''));

                if ($keywords === []) {
                    $keywords = [mb_strtolower($label)];
                }

                $map[$label] = $keywords;
            }

            return $map;
        }

        $map = [];

        foreach ($value as $label => $keywords) {
            if (! is_string($label) || trim($label) === '') {
                continue;
            }

            if (is_string($keywords)) {
                $keywords = preg_split('/\s*,\s*/', $keywords) ?: [];
            }

            if (! is_array($keywords)) {
                continue;
            }

            $normalized = array_values(array_filter(array_map(
                static fn ($keyword): string => mb_strtolower(trim((string) $keyword)),
                $keywords
            ), static fn (string $keyword): bool => $keyword !== ''));

            if ($normalized === []) {
                $normalized = [mb_strtolower(trim($label))];
            }

            $map[trim($label)] = $normalized;
        }

        return $map;
    }

    /**
     * UI-friendly list for the chatbot editor.
     *
     * @param  array<string, array<int, string>>|null  $map
     * @return array<int, array{label: string, keywords: string}>
     */
    public static function toEditorEntries(?array $map): array
    {
        $map = self::normalize($map ?? []);

        if ($map === []) {
            return [];
        }

        $entries = [];

        foreach ($map as $label => $keywords) {
            $entries[] = [
                'label'    => $label,
                'keywords' => implode(', ', $keywords),
            ];
        }

        return $entries;
    }
}
