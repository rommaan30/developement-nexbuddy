<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use Illuminate\Support\Collection;

class EnquiryDetectorService
{
    private const MINIMUM_ENQUIRY_SCORE = 50;

    public function __construct(
        private readonly EmailDetector $emailDetector = new EmailDetector,
        private readonly PhoneDetector $phoneDetector = new PhoneDetector,
        private readonly CompanyDetector $companyDetector = new CompanyDetector,
        private readonly InterestDetector $interestDetector = new InterestDetector,
        private readonly LeadScorer $leadScorer = new LeadScorer,
    ) {}

    /**
     * Detect enquiry data from a chatbot conversation or history collection.
     *
     * @param  ChatbotConversation|Collection<int, ChatbotHistory>  $source
     * @return array{
     *     email: string,
     *     phone: string,
     *     company: string,
     *     interest: string,
     *     matched_keyword: string,
     *     lead_score: int,
     *     is_enquiry: bool
     * }
     */
    public function detect(ChatbotConversation|Collection $source): array
    {
        $text = $this->buildUserMessageText($this->historiesFromSource($source));

        $email = $this->emailDetector->detect($text);
        $phone = $this->phoneDetector->detect($text);
        $company = $this->companyDetector->detect($text);
        $interest = $this->interestDetector->detect($text);
        $score = $this->leadScorer->score(
            email: $email,
            phone: $phone,
            company: $company,
            interest: $interest,
            interestKeywordCount: $this->interestDetector->countMatches($text),
        );

        return [
            'email'           => $email['email'] ?? '',
            'phone'           => $phone['phone'] ?? '',
            'company'         => $company['company'] ?? '',
            'interest'        => $interest['interest'] ?? '',
            'matched_keyword' => $interest['matched_keyword'] ?? '',
            'lead_score'      => $score['lead_score'],
            'is_enquiry'      => $score['lead_score'] >= self::MINIMUM_ENQUIRY_SCORE,
        ];
    }

    /**
     * @return Collection<int, ChatbotHistory>
     */
    private function historiesFromSource(ChatbotConversation|Collection $source): Collection
    {
        if ($source instanceof Collection) {
            return $source;
        }

        if ($source->relationLoaded('histories')) {
            return $source->getRelation('histories');
        }

        return $source->histories()->get();
    }

    /**
     * @param  Collection<int, ChatbotHistory>  $histories
     */
    private function buildUserMessageText(Collection $histories): string
    {
        return $histories
            ->filter(static fn (ChatbotHistory $history) => $history->role === 'user')
            ->pluck('message')
            ->filter()
            ->implode("\n");
    }
}
