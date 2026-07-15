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

            $detected = app(EnquiryDetectorService::class)->detect($conversation);

            if (! $detected['is_enquiry']) {
                return;
            }

            ChatbotEnquiry::query()->updateOrCreate(
                [
                    'conversation_id' => $conversation->getKey(),
                ],
                [
                    'chatbot_id'          => $conversation->getAttribute('chatbot_id'),
                    'chatbot_customer_id' => $conversation->getAttribute('chatbot_customer_id'),
                    'email'               => $detected['email'] ?: null,
                    'phone'               => $detected['phone'] ?: null,
                    'company'             => $detected['company'] ?: null,
                    'interest'            => $detected['interest'] ?: null,
                    'lead_score'          => $detected['lead_score'],
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Chatbot enquiry detection failed.', [
                'chatbot_history_id' => $this->getKey(),
                'conversation_id'    => $this->getAttribute('conversation_id'),
                'message'            => $e->getMessage(),
            ]);
        }
    }
}
