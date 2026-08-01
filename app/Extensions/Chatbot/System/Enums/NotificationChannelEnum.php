<?php

namespace App\Extensions\Chatbot\System\Enums;

use App\Enums\Traits\EnumTo;

enum NotificationChannelEnum: string
{
    use EnumTo;

    case mail = 'mail';
    case slack = 'slack';
    case whatsapp = 'whatsapp';
    case sms = 'sms';
    case teams = 'teams';
}
