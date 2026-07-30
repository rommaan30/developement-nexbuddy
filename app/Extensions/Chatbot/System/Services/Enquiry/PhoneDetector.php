<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class PhoneDetector
{
    /**
     * International numbers with explicit + prefix.
     */
    private const INTERNATIONAL_PHONE_PATTERN = '/(?<!\w)\+(?:\d[\s().-]?){7,15}\d(?!\w)/';

    /**
     * Optional 91 / 0 prefix followed by a 10-digit Indian mobile (starts 6-9).
     */
    private const INDIAN_PHONE_PATTERN = '/(?<![\d])(?:\+?91[\s\-.]?|0)?([6-9](?:[\s().-]?\d){9})(?!\d)/';

    /**
     * Generic local 10+ digit fallback (non-leading-zero sequences).
     */
    private const LOCAL_PHONE_PATTERN = '/(?<![\d+])(?:\d[\s().-]?){9,14}\d(?!\d)/';

    public function __construct(
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * Extract the first valid phone number from text.
     *
     * @return array{phone: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        $text = $this->normalizer->normalize($text);

        if (preg_match(self::INTERNATIONAL_PHONE_PATTERN, $text, $matches) === 1) {
            $phone = $this->normalizePhone($matches[0]);

            if ($this->isValidPhone($phone)) {
                return [
                    'phone' => $phone,
                    'found' => true,
                ];
            }
        }

        if (preg_match(self::INDIAN_PHONE_PATTERN, $text, $matches) === 1) {
            $digits = preg_replace('/\D+/', '', $matches[1]) ?? '';

            if (strlen($digits) === 10) {
                return [
                    'phone' => '+91' . $digits,
                    'found' => true,
                ];
            }
        }

        if (preg_match(self::LOCAL_PHONE_PATTERN, $text, $matches) === 1) {
            $phone = $this->normalizePhone($matches[0]);

            if ($this->isValidPhone($phone)) {
                return [
                    'phone' => $phone,
                    'found' => true,
                ];
            }
        }

        return [
            'phone' => null,
            'found' => false,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // Normalize bare Indian mobiles / 91-prefixed numbers.
        if (strlen($digits) === 10 && preg_match('/^[6-9]/', $digits) === 1) {
            return '+91' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $local = substr($digits, 1);
            if (preg_match('/^[6-9]\d{9}$/', $local) === 1) {
                return '+91' . $local;
            }
        }

        return ($hasPlus ? '+' : '') . $digits;
    }

    private function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return false;
        }

        // Reject obvious non-phone runs (all same digit, sequential placeholders).
        if (preg_match('/^(\d)\1+$/', $digits) === 1) {
            return false;
        }

        return true;
    }
}
