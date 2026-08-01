<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Events;

use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnquiryCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ChatbotEnquiry $enquiry,
    ) {}
}
