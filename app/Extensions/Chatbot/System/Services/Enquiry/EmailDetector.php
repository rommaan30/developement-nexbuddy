<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class EmailDetector
{
    private const EMAIL_PATTERN = '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i';

    /**
     * Extract the first valid email address from text.
     *
     * @return array{email: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        if (preg_match(self::EMAIL_PATTERN, $text, $matches) !== 1) {
            return [
                'email' => null,
                'found' => false,
            ];
        }

        return [
            'email' => $matches[0],
            'found' => true,
        ];
    }
}
