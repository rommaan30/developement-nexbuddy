<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class EmailDetector
{
    private const EMAIL_PATTERN = '/(?<![A-Z0-9._%+\-])([A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,24})(?![A-Z0-9._%+\-])/i';

    public function __construct(
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * Extract the first valid email address from text.
     *
     * @return array{email: string|null, found: bool}
     */
    public function detect(string $text): array
    {
        $text = $this->normalizer->normalize($text);

        if (preg_match(self::EMAIL_PATTERN, $text, $matches) !== 1) {
            return [
                'email' => null,
                'found' => false,
            ];
        }

        $email = strtolower(trim($matches[1]));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => null,
                'found' => false,
            ];
        }

        // Reject obvious placeholders / malformed local parts.
        $local = explode('@', $email)[0] ?? '';
        if ($local === '' || str_starts_with($local, '.') || str_ends_with($local, '.') || str_contains($local, '..')) {
            return [
                'email' => null,
                'found' => false,
            ];
        }

        return [
            'email' => $email,
            'found' => true,
        ];
    }
}
