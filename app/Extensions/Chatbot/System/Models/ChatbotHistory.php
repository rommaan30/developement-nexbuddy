<?php

namespace App\Extensions\Chatbot\System\Models;

use App\Extensions\Chatbot\System\Services\Enquiry\EnquiryDetectorService;
use App\Extensions\Chatbot\System\Services\Enquiry\VisitorNameDetector;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotHistory extends Model
{
    public $timestamps = false;

    protected $table = 'ext_chatbot_histories';

    protected $fillable = [
        'user_id',
        'chatbot_id',
        'conversation_id',
        'message_id',
        'model',
        'role',
        'message',
        'type',
        'media_url',
        'media_name',
        'message_type',
        'is_internal_note',
        'content_type',
        'read_at',
        'voice_call_duration',
        'created_at',
    ];

    protected $casts = [
        'created_at'       => 'datetime',
        'is_internal_note' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(static function (ChatbotHistory $history): void {
            $history->detectAndStoreEnquiry();
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    private function detectAndStoreEnquiry(): void
    {
        if ($this->getAttribute('role') !== 'user' || ! $this->getAttribute('conversation_id')) {
            return;
        }

        try {
            $conversation = $this->conversation()->first();

            if (! $conversation) {
                return;
            }

            $latestEnquiry = $conversation->enquiries()->latest('id')->first();
            $previousEnquiry = $conversation->enquiries()->latest('id')->skip(1)->first();

            $segmentHistories = $this->userHistoriesSince($conversation, $previousEnquiry?->created_at, inclusive: false);
            $newLeadHistories = $latestEnquiry
                ? $this->userHistoriesSince($conversation, $latestEnquiry->created_at, inclusive: false)
                : $segmentHistories;

            if (! $newLeadHistories->contains(fn (ChatbotHistory $history) => $history->is($this))) {
                $newLeadHistories->push($this);
            }

            if (! $segmentHistories->contains(fn (ChatbotHistory $history) => $history->is($this))) {
                $segmentHistories->push($this);
            }

            $detector = app(EnquiryDetectorService::class);

            if ($latestEnquiry) {
                $segmentDetected = $detector->detect($segmentHistories);

                if ($segmentDetected['is_enquiry']) {
                    $segmentLead = [
                        'email'    => $segmentDetected['email'] ?: null,
                        'phone'    => $segmentDetected['phone'] ?: null,
                        'interest' => $segmentDetected['interest'] ?: null,
                    ];
                    $segmentVisitorName = $this->detectVisitorName($detector, $segmentHistories);

                    if ($this->shouldEnrichExistingEnquiry($latestEnquiry, $segmentLead, $segmentDetected, $segmentVisitorName)) {
                        $latestEnquiry->update($this->enrichmentUpdates(
                            $latestEnquiry,
                            $segmentLead,
                            $segmentDetected,
                            $segmentVisitorName
                        ));

                        return;
                    }
                }
            }

            $detected = $detector->detect($newLeadHistories);

            if (! $detected['is_enquiry']) {
                return;
            }

            $lead = [
                'email'    => $detected['email'] ?: null,
                'phone'    => $detected['phone'] ?: null,
                'interest' => $detected['interest'] ?: null,
            ];

            $visitorName = $this->detectVisitorName($detector, $newLeadHistories);

            $duplicate = $conversation->enquiries()
                ->where(static function ($query) use ($lead) {
                    foreach ($lead as $column => $value) {
                        $value === null
                            ? $query->whereNull($column)
                            : $query->where($column, $value);
                    }
                })
                ->exists();

            if ($duplicate) {
                return;
            }

            $conversation->enquiries()->create([
                'chatbot_id'          => $conversation->getAttribute('chatbot_id'),
                'chatbot_customer_id' => $conversation->getAttribute('chatbot_customer_id'),
                'visitor_name'        => $visitorName,
                'email'               => $lead['email'],
                'phone'               => $lead['phone'],
                'company'             => $detected['company'] ?: null,
                'interest'            => $lead['interest'],
                'lead_score'          => $detected['lead_score'],
            ]);
        } catch (Throwable $e) {
            Log::warning('Chatbot enquiry detection failed.', [
                'chatbot_history_id' => $this->getKey(),
                'conversation_id'    => $this->getAttribute('conversation_id'),
                'message'            => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return Collection<int, ChatbotHistory>
     */
    private function userHistoriesSince(
        ChatbotConversation $conversation,
        ?\Illuminate\Support\Carbon $since,
        bool $inclusive = false
    ): Collection {
        return $conversation->histories()
            ->where('role', 'user')
            ->when($since, static function ($query) use ($since, $inclusive) {
                $inclusive
                    ? $query->where('created_at', '>=', $since)
                    : $query->where('created_at', '>', $since);
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Extract visitor name from the same history window used for qualification.
     * Runs only after the lead is qualified — never affects scoring or creation timing.
     *
     * @param  Collection<int, ChatbotHistory>  $histories
     */
    private function detectVisitorName(EnquiryDetectorService $detector, Collection $histories): ?string
    {
        $text = $detector->buildUserMessageText($histories);
        $detected = app(VisitorNameDetector::class)->detect($text);
        $visitorName = $detected['visitor_name'] ?? null;

        if ($visitorName === null || $visitorName === '') {
            return null;
        }

        // Final guard: never persist a name that still contains contact / intent tokens.
        if (
            str_contains($visitorName, '@')
            || preg_match('/\d{3,}/', $visitorName) === 1
            || preg_match('/\b(integration|api|website|pricing|demo|email|phone|company|support|crm|chatbot)\b/i', $visitorName) === 1
        ) {
            return null;
        }

        return $visitorName;
    }

    /**
     * @param  array{email: ?string, phone: ?string, interest: ?string}  $lead
     * @param  array{company?: string, lead_score?: int}  $detected
     */
    private function shouldEnrichExistingEnquiry(
        ChatbotEnquiry $existing,
        array $lead,
        array $detected,
        ?string $visitorName
    ): bool {
        if (($existing->getAttribute('email') ?: null) !== $lead['email']) {
            return false;
        }

        if (($existing->getAttribute('interest') ?: null) !== $lead['interest']) {
            return false;
        }

        return ($existing->getAttribute('phone') === null && $lead['phone'] !== null)
            || ($existing->getAttribute('visitor_name') === null && $visitorName !== null)
            || ($existing->getAttribute('company') === null && ($detected['company'] ?? '') !== '')
            || ((int) $existing->getAttribute('lead_score') < (int) ($detected['lead_score'] ?? 0));
    }

    /**
     * @param  array{email: ?string, phone: ?string, interest: ?string}  $lead
     * @param  array{company?: string, lead_score?: int}  $detected
     * @return array<string, mixed>
     */
    private function enrichmentUpdates(
        ChatbotEnquiry $existing,
        array $lead,
        array $detected,
        ?string $visitorName
    ): array {
        $updates = [];

        if ($existing->getAttribute('phone') === null && $lead['phone'] !== null) {
            $updates['phone'] = $lead['phone'];
        }

        if ($existing->getAttribute('visitor_name') === null && $visitorName !== null) {
            $updates['visitor_name'] = $visitorName;
        }

        if ($existing->getAttribute('company') === null && ($detected['company'] ?? '') !== '') {
            $updates['company'] = $detected['company'];
        }

        if ((int) $existing->getAttribute('lead_score') < (int) ($detected['lead_score'] ?? 0)) {
            $updates['lead_score'] = $detected['lead_score'];
        }

        return $updates;
    }
}
