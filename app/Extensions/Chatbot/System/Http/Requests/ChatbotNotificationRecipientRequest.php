<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Models\Chatbot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotNotificationRecipientRequest extends FormRequest
{
    public function rules(): array
    {
        $recipient = $this->route('notification_recipient');
        $ownedChatbotIds = Chatbot::query()
            ->where('user_id', $this->user()?->getKey())
            ->pluck('id')
            ->all();

        return [
            'chatbot_id' => [
                'required',
                'integer',
                Rule::in($ownedChatbotIds),
            ],
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('ext_chatbot_notification_recipients', 'email')
                    ->where('notification_type', $this->input('notification_type'))
                    ->where('chatbot_id', $this->input('chatbot_id'))
                    ->ignore($recipient?->getKey()),
            ],
            'notification_type' => ['required', 'string', Rule::in(array_keys(NotificationTypeEnum::options()))],
            'is_active'         => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'chatbot_id.required' => __('Please select a chatbot for this recipient.'),
            'chatbot_id.in'       => __('You can only add recipients for chatbots you own.'),
            'email.unique'        => __('This email address is already registered for the selected chatbot and notification type.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'     => is_string($this->input('email')) ? trim(mb_strtolower($this->input('email'))) : $this->input('email'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
