<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Mail;

use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use App\Extensions\Chatbot\System\Services\Enquiry\MissingFieldsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/**
 * Delivery is asynchronous through SendEnquiryNotification, so the mailable
 * itself sends inline and does not queue a second job.
 */
class NewEnquiryNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ChatbotEnquiry $enquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New AI Bot Enquiry Received'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->markdownView(),
            with: [
                'visitorDetails'    => $this->visitorDetails(),
                'businessDetails'   => $this->businessDetails(),
                'leadInformation'   => $this->leadInformation(),
                'dashboardUrl'      => $this->dashboardUrl(),
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * The extension view namespace is registered lazily, so a queue worker can
     * render this mailable before the hint exists. Re-add it when missing.
     */
    private function markdownView(): string
    {
        $view = 'chatbot::mail.enquiry-created';

        if (! View::exists($view)) {
            View::addNamespace('chatbot', __DIR__ . '/../../resources/views');
        }

        return $view;
    }

    /**
     * @return array<string, string>
     */
    private function visitorDetails(): array
    {
        $customer = $this->enquiry->customer;

        return $this->present([
            __('Name')    => $this->enquiry->visitor_name ?: $customer?->name,
            __('Company') => $this->enquiry->company,
            __('Email')   => $this->enquiry->email ?: $customer?->email,
            __('Phone')   => $this->enquiry->phone ?: $customer?->phone,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function businessDetails(): array
    {
        return $this->present([
            __('Requirement') => $this->businessRequirement(),
            __('Interest')    => $this->enquiry->interest,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function leadInformation(): array
    {
        $status = (string) ($this->enquiry->status ?: '');
        $createdAt = $this->enquiry->created_at;

        return $this->present([
            __('Lead Score')    => $this->enquiry->lead_score === null
                ? null
                : (string) $this->enquiry->lead_score,
            __('Status')        => $status === '' ? null : ucfirst($status),
            __('Date & Time')   => $createdAt?->format('d M Y, H:i'),
        ]);
    }

    /**
     * The business requirement is captured during lead qualification and lives
     * on the conversation payload rather than on the enquiry row.
     */
    private function businessRequirement(): ?string
    {
        $payload = $this->enquiry->conversation?->getAttribute('customer_payload');

        if (! is_array($payload)) {
            return null;
        }

        $value = $payload[MissingFieldsManager::PAYLOAD_KEY]['values']['business_requirement'] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function dashboardUrl(): ?string
    {
        $configured = config('chatbot.enquiry_notifications.dashboard_url');

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        if (! Route::has('dashboard.chatbot.enquiries.index')) {
            return null;
        }

        return route('dashboard.chatbot.enquiries.index') . '#enquiry-' . $this->enquiry->getKey();
    }

    /**
     * Drop empty values so the email only lists what was actually collected.
     *
     * @param  array<string, string|null>  $rows
     * @return array<string, string>
     */
    private function present(array $rows): array
    {
        $presented = [];

        foreach ($rows as $label => $value) {
            if (is_string($value) && trim($value) !== '') {
                $presented[$label] = trim($value);
            }
        }

        return $presented;
    }
}
