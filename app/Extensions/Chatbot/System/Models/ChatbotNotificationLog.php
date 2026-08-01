<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Models;

use App\Extensions\Chatbot\System\Enums\NotificationChannelEnum;
use App\Extensions\Chatbot\System\Enums\NotificationStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotNotificationLog extends Model
{
    protected $table = 'ext_chatbot_notification_logs';

    protected $fillable = [
        'enquiry_id',
        'channel',
        'notification_type',
        'recipient',
        'status',
        'queue_job_id',
        'attempts',
        'error_message',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'enquiry_id' => 'integer',
        'attempts'   => 'integer',
        'channel'    => NotificationChannelEnum::class,
        'status'     => NotificationStatusEnum::class,
        'sent_at'    => 'datetime',
        'failed_at'  => 'datetime',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(ChatbotEnquiry::class, 'enquiry_id');
    }

    public function scopeForDelivery(
        Builder $query,
        ?int $enquiryId,
        NotificationChannelEnum $channel,
        string $notificationType,
        string $recipient
    ): Builder {
        return $query
            ->where('enquiry_id', $enquiryId)
            ->where('channel', $channel->value)
            ->where('notification_type', $notificationType)
            ->where('recipient', $recipient);
    }

    public function isSent(): bool
    {
        return $this->status === NotificationStatusEnum::sent;
    }
}
