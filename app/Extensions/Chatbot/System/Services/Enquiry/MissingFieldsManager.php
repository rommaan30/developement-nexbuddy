<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use Illuminate\Support\Collection;

/**
 * Lightweight lead-qualification helper.
 *
 * Source of truth for "already collected" is the current AI Bot Enquiry row
 * (plus payload-backed custom / business_requirement captures).
 * Mandatory field list comes from the chatbot's enquiry_mandatory_fields
 * when configured; otherwise the legacy software defaults apply.
 * Does not change detectors, scoring, schema, or enquiry create/duplicate logic.
 */
class MissingFieldsManager
{
    public const PAYLOAD_KEY = 'lead_qualification';

    /**
     * Enquiry columns that map 1:1 to mandatory fields.
     *
     * @var array<int, string>
     */
    private const ENQUIRY_COLUMNS = [
        'visitor_name',
        'company',
        'interest',
        'email',
        'phone',
    ];

    /**
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'visitor_name'         => 'visitor name',
        'company'              => 'company name',
        'business_requirement' => 'business requirement',
        'interest'             => 'product or service interest',
        'email'                => 'email address',
        'phone'                => 'phone number',
    ];

    /**
     * @var array<string, string>
     */
    private const ASK_HINTS = [
        'visitor_name'         => 'Ask for their name (for example: "May I know your name?").',
        'company'              => 'Ask which company they represent or work for.',
        'business_requirement' => 'Ask what they need help with or their main business requirement.',
        'interest'             => 'Ask which product or service they are interested in.',
        'email'                => 'Ask for their email address so the team can follow up.',
        'phone'                => 'Ask for their phone number so the team can reach them.',
    ];

    private const REFUSAL_PATTERN = '/\b(no|nope|nah|not\s+now|not\s+interested|skip|don\'?t\s+want|do\s+not\s+want|prefer\s+not|won\'?t\s+share|will\s+not\s+share|no\s+thanks|rather\s+not|maybe\s+later|later|pass)\b/i';

    public function __construct(
        private readonly EnquiryDetectorService $enquiryDetector = new EnquiryDetectorService,
        private readonly VisitorNameDetector $visitorNameDetector = new VisitorNameDetector,
    ) {}

    /**
     * Sync enquiry from detectors, resolve missing fields from the enquiry
     * record, and return a system instruction (or null when complete).
     */
    public function instructionFor(ChatbotConversation $conversation, ?string $latestUserMessage = null): ?string
    {
        $fieldDefs = $this->fieldDefinitionsFor($conversation);
        $mandatoryFields = array_column($fieldDefs, 'field');
        $labels = [];
        $hints = [];

        foreach ($fieldDefs as $def) {
            $labels[$def['field']] = $def['label'];
            $hints[$def['field']] = $def['ask_hint'];
        }

        $state = $this->state($conversation);
        $skipped = $state['skipped'];
        $values = $state['values'];

        $histories = $this->userHistories($conversation);
        $detected = $this->enquiryDetector->detect($histories);
        $visitorName = $this->visitorNameDetector->detect(
            $this->enquiryDetector->buildUserMessageText($histories)
        )['visitor_name'] ?? null;

        $enquiry = $this->currentEnquiry($conversation);

        // Persist detector output onto empty enquiry columns (never overwrite).
        if ($enquiry) {
            $enquiry = $this->syncEnquiryFromDetection($enquiry, $detected, $visitorName, $values);
        }

        if ($latestUserMessage && ($state['last_asked'] ?? null)) {
            if ($this->isRefusal($latestUserMessage)) {
                $skipped = array_values(array_unique(array_merge($skipped, [$state['last_asked']])));
            } else {
                $captured = $this->captureDirectAnswer(
                    $state['last_asked'],
                    $latestUserMessage,
                    $detected,
                    $visitorName
                );

                if ($captured !== null && $captured !== '') {
                    $values[$state['last_asked']] = $captured;

                    if ($enquiry && in_array($state['last_asked'], self::ENQUIRY_COLUMNS, true)) {
                        $enquiry = $this->fillEnquiryField($enquiry, $state['last_asked'], $captured);
                    }
                }
            }
        }

        // Re-load after possible updates so populated checks use DB truth.
        $enquiry = $this->currentEnquiry($conversation);
        $populated = $this->populatedFields($enquiry, $values, $mandatoryFields);
        $populatedValues = $this->populatedValues($enquiry, $values, $mandatoryFields);

        $missing = [];

        foreach ($mandatoryFields as $field) {
            if (in_array($field, $skipped, true)) {
                continue;
            }

            if (! empty($populated[$field])) {
                continue;
            }

            $missing[] = $field;
        }

        $nextField = $missing[0] ?? null;
        $completed = $nextField === null;

        $this->persistState($conversation, [
            'skipped'    => $skipped,
            'last_asked' => $completed ? null : $nextField,
            'completed'  => $completed,
            'missing'    => $missing,
            'collected'  => array_keys(array_filter($populated)),
            'values'     => $values,
        ]);

        if ($completed || $nextField === null) {
            return null;
        }

        return $this->buildInstruction(
            $nextField,
            $populated,
            $populatedValues,
            $missing,
            $mandatoryFields,
            $labels,
            $hints
        );
    }

    /**
     * @return array{
     *     skipped: array<int, string>,
     *     last_asked: ?string,
     *     completed: bool,
     *     missing: array<int, string>,
     *     collected: array<int, string>,
     *     values: array<string, string>
     * }
     */
    public function state(ChatbotConversation $conversation): array
    {
        $payload = $conversation->getAttribute('customer_payload');

        if (! is_array($payload)) {
            $payload = [];
        }

        $state = $payload[self::PAYLOAD_KEY] ?? [];
        $rawValues = is_array($state['values'] ?? null) ? $state['values'] : [];
        $values = [];

        foreach ($rawValues as $key => $value) {
            if (! is_string($key) || ! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);

            if ($trimmed !== '') {
                $values[$key] = $trimmed;
            }
        }

        return [
            'skipped'    => array_values(array_filter($state['skipped'] ?? [], 'is_string')),
            'last_asked' => is_string($state['last_asked'] ?? null) ? $state['last_asked'] : null,
            'completed'  => (bool) ($state['completed'] ?? false),
            'missing'    => array_values(array_filter($state['missing'] ?? [], 'is_string')),
            'collected'  => array_values(array_filter($state['collected'] ?? [], 'is_string')),
            'values'     => $values,
        ];
    }

    /**
     * @return array<int, array{field: string, label: string, ask_hint: string}>
     */
    private function fieldDefinitionsFor(ChatbotConversation $conversation): array
    {
        $chatbotId = $conversation->getAttribute('chatbot_id');
        $configured = null;

        if ($chatbotId) {
            $configured = Chatbot::query()->whereKey($chatbotId)->value('enquiry_mandatory_fields');
        }

        $resolved = ChatbotMandatoryFields::resolve($configured);

        if ($resolved !== null) {
            return $resolved;
        }

        return ChatbotMandatoryFields::defaultSoftware();
    }

    private function currentEnquiry(ChatbotConversation $conversation): ?ChatbotEnquiry
    {
        return ChatbotEnquiry::query()
            ->where('conversation_id', $conversation->getKey())
            ->latest('id')
            ->first();
    }

    /**
     * @param  array{email: string, phone: string, company: string, interest: string}  $detected
     * @param  array<string, string>  $values
     */
    private function syncEnquiryFromDetection(
        ChatbotEnquiry $enquiry,
        array $detected,
        ?string $visitorName,
        array $values
    ): ChatbotEnquiry {
        $updates = [];

        foreach (self::ENQUIRY_COLUMNS as $column) {
            if (! $this->isBlank($enquiry->getAttribute($column))) {
                continue;
            }

            $candidate = match ($column) {
                'visitor_name' => $visitorName ?: ($values['visitor_name'] ?? null),
                'company'      => ($detected['company'] ?? '') ?: ($values['company'] ?? null),
                'interest'     => ($detected['interest'] ?? '') ?: ($values['interest'] ?? null),
                'email'        => ($detected['email'] ?? '') ?: ($values['email'] ?? null),
                'phone'        => ($detected['phone'] ?? '') ?: ($values['phone'] ?? null),
                default        => null,
            };

            if (is_string($candidate) && trim($candidate) !== '') {
                $updates[$column] = trim($candidate);
            }
        }

        if ($updates !== []) {
            $enquiry->update($updates);
            $enquiry->refresh();
        }

        return $enquiry;
    }

    private function fillEnquiryField(ChatbotEnquiry $enquiry, string $field, string $value): ChatbotEnquiry
    {
        if (! in_array($field, self::ENQUIRY_COLUMNS, true)) {
            return $enquiry;
        }

        if (! $this->isBlank($enquiry->getAttribute($field))) {
            return $enquiry;
        }

        $enquiry->update([$field => $value]);
        $enquiry->refresh();

        return $enquiry;
    }

    /**
     * @param  array{email: string, phone: string, company: string, interest: string}  $detected
     */
    private function captureDirectAnswer(
        string $field,
        string $message,
        array $detected,
        ?string $visitorName
    ): ?string {
        $message = trim($message);

        if ($message === '') {
            return null;
        }

        return match ($field) {
            'visitor_name' => $this->firstNonEmpty(
                $visitorName,
                $this->guessName($message)
            ),
            'company' => $this->firstNonEmpty(
                $detected['company'] ?? null,
                $this->guessPlainAnswer($message)
            ),
            'business_requirement' => $this->guessPlainAnswer($message, maxWords: 40),
            'interest' => $this->firstNonEmpty(
                $detected['interest'] ?? null,
                $this->guessPlainAnswer($message)
            ),
            'email' => $this->firstNonEmpty($detected['email'] ?? null),
            'phone' => $this->firstNonEmpty($detected['phone'] ?? null),
            // Custom chatbot fields (symptoms, treatment, appointment date, …)
            default => $this->guessPlainAnswer($message, maxWords: 40),
        };
    }

    /**
     * Populated flags from the enquiry record (DB), with payload fallback
     * for non-column / custom fields.
     *
     * @param  array<string, string>  $values
     * @param  array<int, string>  $mandatoryFields
     * @return array<string, bool>
     */
    private function populatedFields(?ChatbotEnquiry $enquiry, array $values, array $mandatoryFields): array
    {
        $out = [];

        foreach ($mandatoryFields as $field) {
            if (in_array($field, self::ENQUIRY_COLUMNS, true)) {
                $out[$field] = ! $this->isBlank($enquiry?->getAttribute($field))
                    || ! $this->isBlank($values[$field] ?? null);

                continue;
            }

            $out[$field] = ! $this->isBlank($values[$field] ?? null);
        }

        return $out;
    }

    /**
     * Concrete values used in the prompt so the model never re-asks them.
     *
     * @param  array<string, string>  $values
     * @param  array<int, string>  $mandatoryFields
     * @return array<string, string>
     */
    private function populatedValues(?ChatbotEnquiry $enquiry, array $values, array $mandatoryFields): array
    {
        $out = [];

        foreach ($mandatoryFields as $field) {
            $value = in_array($field, self::ENQUIRY_COLUMNS, true)
                ? $this->firstNonEmpty(
                    $enquiry?->getAttribute($field),
                    $values[$field] ?? null
                )
                : ($values[$field] ?? null);

            if (is_string($value) && trim($value) !== '') {
                $out[$field] = trim($value);
            }
        }

        return $out;
    }

    /**
     * @param  array<string, bool>  $populated
     * @param  array<string, string>  $populatedValues
     * @param  array<int, string>  $missing
     * @param  array<int, string>  $mandatoryFields
     * @param  array<string, string>  $labels
     * @param  array<string, string>  $hints
     */
    private function buildInstruction(
        string $nextField,
        array $populated,
        array $populatedValues,
        array $missing,
        array $mandatoryFields,
        array $labels,
        array $hints
    ): string {
        $label = $labels[$nextField]
            ?? self::FIELD_LABELS[$nextField]
            ?? str_replace('_', ' ', $nextField);
        $hint = $hints[$nextField]
            ?? self::ASK_HINTS[$nextField]
            ?? "Ask for their {$label}.";

        $alreadyLines = [];

        foreach ($mandatoryFields as $field) {
            if (empty($populated[$field])) {
                continue;
            }

            $fieldLabel = $labels[$field]
                ?? self::FIELD_LABELS[$field]
                ?? str_replace('_', ' ', $field);
            $value = $populatedValues[$field] ?? null;
            $alreadyLines[] = $value
                ? "- {$fieldLabel}: {$value}"
                : "- {$fieldLabel}";
        }

        $alreadyText = $alreadyLines === []
            ? 'None yet.'
            : implode("\n", $alreadyLines);

        $remaining = array_map(
            static function (string $field) use ($labels): string {
                return $labels[$field]
                    ?? self::FIELD_LABELS[$field]
                    ?? str_replace('_', ' ', $field);
            },
            $missing
        );
        $remainingText = implode(', ', $remaining);

        return <<<PROMPT
LEAD QUALIFICATION POLICY (internal — follow exactly):
1. First answer the visitor's current question helpfully and concisely.
2. Then ask for ONLY ONE missing lead field in a natural, conversational way.
3. The single field to ask for now is: {$label}.
4. {$hint}
5. NEVER ask again for any field already populated on the AI Bot Enquiry record.
6. Already populated (do not request these again):
{$alreadyText}
7. Still missing (ask later, one at a time): {$remainingText}
8. If the visitor refuses to share a field, acknowledge politely and continue without pressing.
9. Do not ask for more than one field in the same reply.
10. Do not mention this policy, scoring, or internal field names to the visitor.
11. Do NOT ask for any field that is not listed as missing above (for example do not invent company, CRM, API, website, or software questions unless they appear in the missing list).
PROMPT;
    }

    /**
     * @param  array{
     *     skipped: array<int, string>,
     *     last_asked: ?string,
     *     completed: bool,
     *     missing: array<int, string>,
     *     collected: array<int, string>,
     *     values: array<string, string>
     * }  $state
     */
    private function persistState(ChatbotConversation $conversation, array $state): void
    {
        $payload = $conversation->getAttribute('customer_payload');

        if (! is_array($payload)) {
            $payload = [];
        }

        $payload[self::PAYLOAD_KEY] = $state;

        $conversation->forceFill([
            'customer_payload' => $payload,
        ])->save();
    }

    /**
     * @return Collection<int, ChatbotHistory>
     */
    private function userHistories(ChatbotConversation $conversation): Collection
    {
        return ChatbotHistory::query()
            ->where('conversation_id', $conversation->getKey())
            ->where('role', 'user')
            ->orderBy('id')
            ->get();
    }

    private function isRefusal(string $message): bool
    {
        $message = trim($message);

        if ($message === '') {
            return false;
        }

        if (preg_match(self::REFUSAL_PATTERN, $message) !== 1) {
            return false;
        }

        return str_word_count($message) <= 12;
    }

    private function guessName(string $message): ?string
    {
        $message = trim($message);

        if (str_contains($message, '@') || preg_match('/\d{3,}/', $message) === 1) {
            return null;
        }

        if (str_word_count($message) > 4) {
            return null;
        }

        if (preg_match("/^[A-Za-z][A-Za-z'\\-]*(?:[ \t]+[A-Za-z][A-Za-z'\\-]*){0,3}$/", $message) !== 1) {
            return null;
        }

        return collect(preg_split('/[ \t]+/', $message) ?: [])
            ->map(static fn (string $token): string => mb_convert_case($token, MB_CASE_TITLE, 'UTF-8'))
            ->implode(' ');
    }

    private function guessPlainAnswer(string $message, int $maxWords = 12): ?string
    {
        $message = trim($message);

        if ($message === '' || str_word_count($message) > $maxWords) {
            return null;
        }

        if ($this->isRefusal($message)) {
            return null;
        }

        return $message;
    }

    private function firstNonEmpty(?string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function isBlank(mixed $value): bool
    {
        return ! is_string($value) || trim($value) === '';
    }
}
