<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

class LeadScorer
{
    private const MAX_SCORE = 100;

    /**
     * Calculate a lead score from detector results.
     *
     * @param  array{found: bool}  $email
     * @param  array{found: bool}  $phone
     * @param  array{found: bool}  $company
     * @param  array{found: bool}  $interest
     * @return array{lead_score: int}
     */
    public function score(array $email, array $phone, array $company, array $interest, int $interestKeywordCount = 0): array
    {
        $score = 0;

        $score += $email['found'] ? 30 : 0;
        $score += $phone['found'] ? 30 : 0;
        $score += $company['found'] ? 10 : 0;
        $score += $interest['found'] ? 20 : 0;
        $score += $interestKeywordCount > 1 ? 10 : 0;

        return [
            'lead_score' => min($score, self::MAX_SCORE),
        ];
    }
}
