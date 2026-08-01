<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Models;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatbotNotificationRecipient extends Model
{
    protected $table = 'ext_chatbot_notification_recipients';

    protected $fillable = [
        'name',
        'email',
        'notification_type',
        'is_active',
    ];

    protected $casts = [
        'notification_type' => NotificationTypeEnum::class,
        'is_active'         => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, NotificationTypeEnum $type): Builder
    {
        return $query->where('notification_type', $type->value);
    }
}
