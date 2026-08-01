<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Observers;

use App\Extensions\Chatbot\System\Events\EnquiryCreated;
use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotEnquiryObserver
{
    /**
     * Fires only for genuinely new enquiry rows. Enrichment, status changes,
     * notes and dashboard edits are updates and never reach this hook.
     */
    public function created(ChatbotEnquiry $enquiry): void
    {
        try {
            EnquiryCreated::dispatch($enquiry);
        } catch (Throwable $exception) {
            Log::warning('AI Bot Enquiry notification could not be dispatched.', [
                'enquiry_id'      => $enquiry->getKey(),
                'conversation_id' => $enquiry->getAttribute('conversation_id'),
                'message'         => $exception->getMessage(),
            ]);
        }
    }
}
