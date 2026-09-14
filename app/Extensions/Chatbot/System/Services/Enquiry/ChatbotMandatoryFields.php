<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Enquiry;

/**
 * Per-chatbot mandatory lead-qualification fields.
 *
 * Storage format on ext_chatbots.enquiry_mandatory_fields (JSON list):
 *   [
 *     {"field": "visitor_name", "label": "visitor name", "ask_hint": "Ask for their name..."},
 *     ...
 *   ]
 *
 * Null / empty means MissingFieldsManager falls back to the legacy software list.
 */
final class ChatbotMandatoryFields
{
    /**
     * Legacy software / B2B defaults (previous global MANDATORY_FIELDS).
     *
     * @return array<int, array{field: string, label: string, ask_hint: string}>
     */
    public static function defaultSoftware(): array
    {
        return [
            [
                'field'    => 'visitor_name',
                'label'    => 'visitor name',
                'ask_hint' => 'Ask for their name (for example: "May I know your name?").',
            ],
            [
                'field'    => 'company',
                'label'    => 'company name',
                'ask_hint' => 'Ask which company they represent or work for.',
            ],
            [
                'field'    => 'business_requirement',
                'label'    => 'business requirement',
                'ask_hint' => 'Ask what they need help with or their main business requirement.',
            ],
            [
                'field'    => 'interest',
                'label'    => 'product or service interest',
                'ask_hint' => 'Ask which product or service they are interested in.',
            ],
            [
                'field'    => 'email',
                'label'    => 'email address',
                'ask_hint' => 'Ask for their email address so the team can follow up.',
            ],
            [
                'field'    => 'phone',
                'label'    => 'phone number',
                'ask_hint' => 'Ask for their phone number so the team can reach them.',
            ],
        ];
    }

    /**
     * Clinic / medical example (e.g. eye care).
     *
     * @return array<int, array{field: string, label: string, ask_hint: string}>
     */
    public static function defaultMedical(): array
    {
        return [
            [
                'field'    => 'visitor_name',
                'label'    => 'visitor name',
                'ask_hint' => 'Ask for their name (for example: "May I know your name?").',
            ],
            [
                'field'    => 'symptoms',
                'label'    => 'symptoms',
                'ask_hint' => 'Ask what symptoms or eye problems they are experiencing.',
            ],
            [
                'field'    => 'interested_treatment',
                'label'    => 'treatment of interest',
                'ask_hint' => 'Ask which treatment or service they are interested in (for example eye check-up, cataract, LASIK).',
            ],
            [
                'field'    => 'email',
                'label'    => 'email address',
                'ask_hint' => 'Ask for their email address so the clinic can follow up.',
            ],
            [
                'field'    => 'phone',
                'label'    => 'phone number',
                'ask_hint' => 'Ask for their phone number so the clinic can reach them.',
            ],
            [
                'field'    => 'preferred_appointment_date',
                'label'    => 'preferred appointment date',
                'ask_hint' => 'Ask for their preferred appointment date or when they would like to visit.',
            ],
        ];
    }

    /**
     * @return array<int, array{field: string, label: string, ask_hint: string}>|null
     *         null when the chatbot has no custom config (use legacy fallback).
     */
    public static function resolve(mixed $value): ?array
    {
        if ($value === null || $value === [] || $value === '') {
            return null;
        }

        $normalized = self::normalize($value);

        return $normalized === [] ? null : $normalized;
    }

    /**
     * @return array<int, array{field: string, label: string, ask_hint: string}>
     */
    public static function normalize(mixed $value): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        $defaultsByField = [];

        foreach (self::defaultSoftware() as $row) {
            $defaultsByField[$row['field']] = $row;
        }

        foreach (self::defaultMedical() as $row) {
            if (! isset($defaultsByField[$row['field']])) {
                $defaultsByField[$row['field']] = $row;
            }
        }

        $out = [];
        $seen = [];

        foreach ($value as $row) {
            if (is_string($row)) {
                $field = self::slugField($row);

                if ($field === '' || isset($seen[$field])) {
                    continue;
                }

                $seen[$field] = true;
                $default = $defaultsByField[$field] ?? null;
                $out[] = [
                    'field'    => $field,
                    'label'    => $default['label'] ?? self::humanize($field),
                    'ask_hint' => $default['ask_hint'] ?? 'Ask for their ' . ($default['label'] ?? self::humanize($field)) . '.',
                ];

                continue;
            }

            if (! is_array($row)) {
                continue;
            }

            $field = self::slugField((string) ($row['field'] ?? $row['key'] ?? ''));

            if ($field === '' || isset($seen[$field])) {
                continue;
            }

            $seen[$field] = true;
            $default = $defaultsByField[$field] ?? null;
            $label = trim((string) ($row['label'] ?? ''));
            $hint = trim((string) ($row['ask_hint'] ?? $row['hint'] ?? ''));

            if ($label === '') {
                $label = $default['label'] ?? self::humanize($field);
            }

            if ($hint === '') {
                $hint = $default['ask_hint'] ?? "Ask for their {$label}.";
            }

            $out[] = [
                'field'    => $field,
                'label'    => $label,
                'ask_hint' => $hint,
            ];
        }

        return $out;
    }

    /**
     * Editor-friendly list (same shape as storage).
     *
     * @return array<int, array{field: string, label: string, ask_hint: string}>
     */
    public static function toEditorEntries(mixed $value): array
    {
        return self::normalize($value ?: self::defaultSoftware());
    }

    private static function slugField(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_]+/', '_', $value) ?? '';
        $value = trim($value, '_');

        return $value;
    }

    private static function humanize(string $field): string
    {
        return str_replace('_', ' ', $field);
    }
}
