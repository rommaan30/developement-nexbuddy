<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Listeners;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Events\EnquiryCreated;
use App\Extensions\Chatbot\System\Mail\NewEnquiryNotificationMail;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationRecipient;
use App\Extensions\Chatbot\System\Services\Notification\NotificationLogger;
use App\Extensions\Chatbot\System\Services\Notification\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEnquiryNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Only dispatch once the enquiry row is committed, so the listener can
     * never observe a rolled back lead.
     */
    public bool $afterCommit = true;

    /**
     * An enquiry deleted from the dashboard before the job runs is not an
     * error; drop the job instead of pushing it into failed_jobs.
     */
    public bool $deleteWhenMissingModels = true;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    public function viaQueue(): string
    {
        return (string) config('chatbot.enquiry_notifications.queue', 'default');
    }

    public function shouldQueue(EnquiryCreated $event): bool
    {
        if (! config('chatbot.enquiry_notifications.enabled', true)) {
            return false;
        }

        if ($this->recipients()->isNotEmpty()) {
            return true;
        }

        $this->reportMissingRecipients($event->enquiry->getKey());

        return false;
    }

    public function handle(EnquiryCreated $event): void
    {
        if (! config('chatbot.enquiry_notifications.enabled', true)) {
            return;
        }

        $enquiry = $event->enquiry;
        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            $this->reportMissingRecipients($enquiry->getKey());

            return;
        }

        Log::info('AI Bot Enquiry notification started.', [
            'enquiry_id' => $enquiry->getKey(),
            'recipients' => $recipients->count(),
        ]);

        $delivered = [];
        $failed = [];
        $lastException = null;
        $logger = app(NotificationLogger::class);
        $jobId = $this->job?->getJobId();

        // Sent per recipient so one rejected address cannot block the others.
        foreach ($recipients as $recipient) {
            $log = $logger->starting(
                enquiryId: (int) $enquiry->getKey(),
                recipient: $recipient->email,
                queueJobId: $jobId === null ? null : (string) $jobId,
                attempt: $this->job?->attempts(),
            );

            try {
                Mail::to($recipient->email, $recipient->name)->send(new NewEnquiryNotificationMail($enquiry));

                $logger->sent($log);
                $delivered[] = $recipient->email;
            } catch (Throwable $exception) {
                $logger->failed($log, $exception);
                $failed[] = $recipient->email;
                $lastException = $exception;
            }
        }

        if ($delivered !== []) {
            Log::info('AI Bot Enquiry notification email sent.', [
                'enquiry_id' => $enquiry->getKey(),
                'delivered'  => $delivered,
            ]);
        }

        if ($failed === []) {
            return;
        }

        Log::error('AI Bot Enquiry notification email failed.', [
            'enquiry_id' => $enquiry->getKey(),
            'failed'     => $failed,
            'message'    => $lastException?->getMessage(),
        ]);

        // Retrying a partial success would duplicate mail for delivered
        // recipients, so only a total failure is handed back to the queue.
        if ($delivered !== [] || $this->job === null || $lastException === null) {
            return;
        }

        // A permanent SMTP rejection (5xx) will fail identically on every
        // retry, so burning attempts only wastes the provider's send quota.
        if ($this->isPermanentFailure($lastException)) {
            Log::error('AI Bot Enquiry notification will not be retried: permanent SMTP rejection.', [
                'enquiry_id' => $enquiry->getKey(),
                'message'    => $lastException->getMessage(),
            ]);

            return;
        }

        throw $lastException;
    }

    public function failed(EnquiryCreated $event, Throwable $exception): void
    {
        app(NotificationLogger::class)->failPending((int) $event->enquiry->getKey(), $exception);

        Log::error('AI Bot Enquiry notification queue job failed.', [
            'enquiry_id' => $event->enquiry->getKey(),
            'message'    => $exception->getMessage(),
        ]);
    }

    /**
     * Recipients are managed by admins in the Notification Management module.
     *
     * @return Collection<int, ChatbotNotificationRecipient>
     */
    private function recipients(): Collection
    {
        try {
            return app(NotificationRecipientResolver::class)
                ->active(NotificationTypeEnum::ai_bot_enquiry);
        } catch (Throwable $exception) {
            Log::error('AI Bot Enquiry notification recipients could not be loaded.', [
                'message' => $exception->getMessage(),
            ]);

            return new Collection;
        }
    }

    /**
     * Transient problems (dropped connection, greylisting, 4xx) are worth
     * retrying. A 5xx response is the server refusing outright.
     */
    private function isPermanentFailure(Throwable $exception): bool
    {
        if (preg_match('/got code "(\d{3})"/', $exception->getMessage(), $matches) !== 1) {
            return false;
        }

        return (int) $matches[1] >= 500;
    }

    private function reportMissingRecipients(mixed $enquiryId): void
    {
        Log::warning('AI Bot Enquiry notification skipped: no active recipients configured.', [
            'enquiry_id' => $enquiryId,
        ]);
    }
}
