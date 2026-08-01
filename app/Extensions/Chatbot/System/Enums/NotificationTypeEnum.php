<?php

namespace App\Extensions\Chatbot\System\Enums;

use App\Enums\Traits\EnumTo;

enum NotificationTypeEnum: string
{
    use EnumTo;

    case ai_bot_enquiry = 'ai_bot_enquiry';
    case support_ticket = 'support_ticket';
    case contact_form = 'contact_form';
    case system_alert = 'system_alert';

    public function label(): string
    {
        return match ($this) {
            self::ai_bot_enquiry => __('AI Bot Enquiry'),
            self::support_ticket => __('Support Ticket'),
            self::contact_form   => __('Contact Form'),
            self::system_alert   => __('System Alert'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
