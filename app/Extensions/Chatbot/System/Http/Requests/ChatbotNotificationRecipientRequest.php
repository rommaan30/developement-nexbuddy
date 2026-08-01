<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotNotificationRecipientRequest extends FormRequest
{
    public function rules(): array
    {
        $recipient = $this->route('notification_recipient');

        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                // A person may receive different notification types, but never
                // the same one twice.
                Rule::unique('ext_chatbot_notification_recipients', 'email')
                    ->where('notification_type', $this->input('notification_type'))
                    ->ignore($recipient?->getKey()),
            ],
            'notification_type' => ['required', 'string', Rule::in(array_keys(NotificationTypeEnum::options()))],
            'is_active'         => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => __('This email address is already registered for the selected notification type.'),
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
