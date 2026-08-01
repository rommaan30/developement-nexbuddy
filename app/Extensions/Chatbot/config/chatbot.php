<?php

return [
    'version' => 1.0,
    'avatars' => [
        'avatar-1.png',
        'avatar-2.png',
        'avatar-3.png',
        'avatar-4.png',
        'avatar-5.png',
    ],
    'notification_enabled' => env('CHATBOT_NOTIFICATION_ENABLED', true),

    'enquiry_notifications' => [
        'enabled' => env('CHATBOT_ENQUIRY_NOTIFICATION_ENABLED', true),

        'queue' => env('CHATBOT_ENQUIRY_NOTIFICATION_QUEUE', 'default'),

        // Overrides the "View Enquiry" link when the dashboard is hosted elsewhere.
        'dashboard_url' => env('CHATBOT_ENQUIRY_NOTIFICATION_DASHBOARD_URL'),

        // Bootstrap list only. Recipients are managed by admins in the
        // Notification Management module; this seeds the table on install.
        'recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'CHATBOT_ENQUIRY_NOTIFICATION_RECIPIENTS',
                'shahrukh@nexgeno.in,tamir@nexgeno.in,arif@nexgeno.in'
            ))
        ))),
    ],
];
