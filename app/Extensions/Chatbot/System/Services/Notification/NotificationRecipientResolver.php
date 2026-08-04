<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Notification;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationRecipient;
use Illuminate\Support\Collection;

/**
 * Single source of truth for who receives a notification.
 *
 * Recipients are always scoped to one chatbot so tenants cannot receive
 * another chatbot's enquiry mail.
 */
class NotificationRecipientResolver
{
    /**
     * @return Collection<int, ChatbotNotificationRecipient>
     */
    public function active(NotificationTypeEnum $type, int $chatbotId): Collection
    {
        if ($chatbotId < 1) {
            return new Collection;
        }

        return ChatbotNotificationRecipient::query()
            ->forChatbot($chatbotId)
            ->active()
            ->ofType($type)
            ->orderBy('id')
            ->get()
            ->filter(static fn (ChatbotNotificationRecipient $recipient): bool => filter_var($recipient->email, FILTER_VALIDATE_EMAIL) !== false)
            // Deduplicate only within this chatbot's resolved set.
            ->unique('email')
            ->values();
    }
}
