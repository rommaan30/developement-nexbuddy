<?php

namespace App\Extensions\Chatbot\System\Models;

use App\Extensions\Chatbot\System\Services\Enquiry\EnquiryDetectorService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            $histories = $conversation->histories()
                ->where('role', 'user')
                ->when($latestEnquiry?->created_at, static function ($query) use ($latestEnquiry) {
                    $query->where('created_at', '>=', $latestEnquiry->created_at);
                })
                ->orderBy('id')
                ->get();

            if (! $histories->contains(fn (ChatbotHistory $history) => $history->is($this))) {
                $histories->push($this);
            }

            $candidateHistories = collect();

            foreach ($histories as $history) {
                $candidateHistories->push($history);
                $detected = app(EnquiryDetectorService::class)->detect($candidateHistories);

                if (! $detected['is_enquiry']) {
                    continue;
                }

                $lead = [
                    'email'    => $detected['email'] ?: null,
                    'phone'    => $detected['phone'] ?: null,
                    'interest' => $detected['interest'] ?: null,
                ];

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
                    $candidateHistories = collect();

                    continue;
                }

                $conversation->enquiries()->create([
                    'chatbot_id'          => $conversation->getAttribute('chatbot_id'),
                    'chatbot_customer_id' => $conversation->getAttribute('chatbot_customer_id'),
                    'email'               => $lead['email'],
                    'phone'               => $lead['phone'],
                    'company'             => $detected['company'] ?: null,
                    'interest'            => $lead['interest'],
                    'lead_score'          => $detected['lead_score'],
                ]);

                return;
            }
        } catch (Throwable $e) {
            Log::warning('Chatbot enquiry detection failed.', [
                'chatbot_history_id' => $this->getKey(),
                'conversation_id'    => $this->getAttribute('conversation_id'),
                'message'            => $e->getMessage(),
            ]);
        }
    }
}
