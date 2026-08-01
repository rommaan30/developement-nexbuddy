<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Notification;

use App\Extensions\Chatbot\System\Enums\NotificationChannelEnum;
use App\Extensions\Chatbot\System\Enums\NotificationStatusEnum;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationLog;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Audit trail for outbound enquiry notifications.
 *
 * Channel agnostic on purpose: mail today, Slack / WhatsApp / SMS / Teams can
 * reuse the same table by passing a different channel.
 *
 * Every method swallows its own failures. Auditing must never be the reason a
 * notification, an enquiry, or a conversation breaks.
 */
class NotificationLogger
{
    /**
     * Open (or reopen, on retry) the delivery record for one recipient.
     */
    public function starting(
        ?int $enquiryId,
        string $recipient,
        NotificationChannelEnum $channel = NotificationChannelEnum::mail,
        string $notificationType = 'enquiry_created',
        ?string $queueJobId = null,
        ?int $attempt = null,
    ): ?ChatbotNotificationLog {
        try {
            $log = ChatbotNotificationLog::query()
                ->forDelivery($enquiryId, $channel, $notificationType, $recipient)
                ->where('status', '!=', NotificationStatusEnum::sent->value)
                ->latest('id')
                ->first();

            $attempts = $attempt ?? (($log?->attempts ?? 0) + 1);

            if ($log === null) {
                return ChatbotNotificationLog::create([
                    'enquiry_id'        => $enquiryId,
                    'channel'           => $channel->value,
                    'notification_type' => $notificationType,
                    'recipient'         => $recipient,
                    'status'            => NotificationStatusEnum::pending->value,
                    'queue_job_id'      => $queueJobId,
                    'attempts'          => $attempts,
                ]);
            }

            // Same recipient being processed again: reuse the row so retries do
            // not create duplicate audit entries.
            $log->update([
                'status'       => $attempts > 1
                    ? NotificationStatusEnum::retrying->value
                    : NotificationStatusEnum::pending->value,
                'queue_job_id' => $queueJobId ?? $log->queue_job_id,
                'attempts'     => $attempts,
            ]);

            return $log;
        } catch (Throwable $exception) {
            $this->reportLoggingFailure($exception, $enquiryId, $recipient);

            return null;
        }
    }

    public function sent(?ChatbotNotificationLog $log): void
    {
        if ($log === null) {
            return;
        }

        try {
            $log->update([
                'status'        => NotificationStatusEnum::sent->value,
                'sent_at'       => now(),
                'failed_at'     => null,
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $this->reportLoggingFailure($exception, $log->enquiry_id, $log->recipient);
        }
    }

    public function failed(?ChatbotNotificationLog $log, Throwable $reason): void
    {
        if ($log === null) {
            return;
        }

        try {
            $log->update([
                'status'        => NotificationStatusEnum::failed->value,
                'failed_at'     => now(),
                'error_message' => mb_substr($reason->getMessage(), 0, 2000),
            ]);
        } catch (Throwable $exception) {
            $this->reportLoggingFailure($exception, $log->enquiry_id, $log->recipient);
        }
    }

    /**
     * Close out any record still open when the queue gives up on the job.
     */
    public function failPending(?int $enquiryId, Throwable $reason, NotificationChannelEnum $channel = NotificationChannelEnum::mail, string $notificationType = 'enquiry_created'): void
    {
        try {
            ChatbotNotificationLog::query()
                ->where('enquiry_id', $enquiryId)
                ->where('channel', $channel->value)
                ->where('notification_type', $notificationType)
                ->whereIn('status', [
                    NotificationStatusEnum::pending->value,
                    NotificationStatusEnum::retrying->value,
                ])
                ->update([
                    'status'        => NotificationStatusEnum::failed->value,
                    'failed_at'     => now(),
                    'error_message' => mb_substr($reason->getMessage(), 0, 2000),
                    'updated_at'    => now(),
                ]);
        } catch (Throwable $exception) {
            $this->reportLoggingFailure($exception, $enquiryId, null);
        }
    }

    private function reportLoggingFailure(Throwable $exception, ?int $enquiryId, ?string $recipient): void
    {
        Log::warning('AI Bot Enquiry notification log could not be written.', [
            'enquiry_id' => $enquiryId,
            'recipient'  => $recipient,
            'message'    => $exception->getMessage(),
        ]);
    }
}
