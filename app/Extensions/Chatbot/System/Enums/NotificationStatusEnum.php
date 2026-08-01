<?php

namespace App\Extensions\Chatbot\System\Enums;

use App\Enums\Traits\EnumTo;

enum NotificationStatusEnum: string
{
    use EnumTo;

    case pending = 'pending';
    case sent = 'sent';
    case failed = 'failed';
    case retrying = 'retrying';
}
