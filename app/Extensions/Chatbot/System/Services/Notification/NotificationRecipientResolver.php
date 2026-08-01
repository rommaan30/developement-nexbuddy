<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Notification;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationRecipient;
use Illuminate\Support\Collection;

/**
 * Single source of truth for who receives a notification.
 *
 * Channel agnostic: any future Slack, WhatsApp, SMS, or Teams sender can ask
 * for the active recipients of a notification type through this resolver.
 */
class NotificationRecipientResolver
{
    /**
     * @return Collection<int, ChatbotNotificationRecipient>
     */
    public function active(NotificationTypeEnum $type): Collection
    {
        return ChatbotNotificationRecipient::query()
            ->active()
            ->ofType($type)
            ->orderBy('id')
            ->get()
            ->filter(static fn (ChatbotNotificationRecipient $recipient): bool => filter_var($recipient->email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique('email')
            ->values();
    }
}
