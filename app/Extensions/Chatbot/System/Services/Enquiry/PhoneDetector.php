<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class PhoneDetector
{
    private const INTERNATIONAL_PHONE_PATTERN = '/(?<!\w)\+(?:\d[\s().-]?){7,15}\d(?!\w)/';

    private const LOCAL_PHONE_PATTERN = '/(?<![\d+])(?:\d[\s().-]?){9}\d(?!\d)/';

    /**
     * Extract the first valid phone number from text.
     *
     * @return array{phone: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        if (preg_match(self::INTERNATIONAL_PHONE_PATTERN, $text, $matches) === 1) {
            return [
                'phone' => $this->normalizePhone($matches[0]),
                'found' => true,
            ];
        }

        if (preg_match(self::LOCAL_PHONE_PATTERN, $text, $matches) === 1) {
            return [
                'phone' => $this->normalizePhone($matches[0]),
                'found' => true,
            ];
        }

        return [
            'phone' => null,
            'found' => false,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $prefix = str_starts_with($phone, '+') ? '+' : '';

        return $prefix . preg_replace('/\D+/', '', $phone);
    }
}
