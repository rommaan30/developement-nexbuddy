<?php

namespace App\Extensions\Chatbot\System\Services\Enquiry;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EnquiryDetectorService
{
    private const MINIMUM_ENQUIRY_SCORE = 50;

    public function __construct(
        private readonly EmailDetector $emailDetector = new EmailDetector,
        private readonly PhoneDetector $phoneDetector = new PhoneDetector,
        private readonly CompanyDetector $companyDetector = new CompanyDetector,
        private readonly LeadScorer $leadScorer = new LeadScorer,
        private readonly TextNormalizer $normalizer = new TextNormalizer,
    ) {}

    /**
     * Detect enquiry data from a chatbot conversation or history collection.
     *
     * Qualification fields only — visitor name is extracted separately when an
     * enquiry row is persisted, so name detection never affects lead scoring.
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
        $histories = $this->historiesFromSource($source);
        $text = $this->normalizer->normalize($this->buildUserMessageText($histories));

        // One dictionary load per detection — keyed by chatbot_id.
        $interestDetector = new InterestDetector($this->keywordGroupsFor($source, $histories));

        $email = $this->emailDetector->detect($text);
        $phone = $this->phoneDetector->detect($text);
        $company = $this->companyDetector->detect($text);
        $interest = $interestDetector->detect($text);

        [$email, $phone, $company, $interest] = $this->crossValidate(
            $email,
            $phone,
            $company,
            $interest,
            $interestDetector,
        );

        $score = $this->leadScorer->score(
            email: $email,
            phone: $phone,
            company: $company,
            interest: $interest,
            interestKeywordCount: $interestDetector->countMatches($text),
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
     * @param  Collection<int, ChatbotHistory>  $histories
     */
    public function buildUserMessageText(Collection $histories): string
    {
        return $histories
            ->filter(static fn (ChatbotHistory $history) => $history->role === 'user')
            ->pluck('message')
            ->filter()
            ->implode("\n");
    }

    public static function forgetCache(int $chatbotId): void
    {
        Cache::forget(self::cacheKey($chatbotId));
    }

    /**
     * @param  ChatbotConversation|Collection<int, ChatbotHistory>  $source
     * @param  Collection<int, ChatbotHistory>  $histories
     * @return array<string, array<int, string>>
     */
    private function keywordGroupsFor(ChatbotConversation|Collection $source, Collection $histories): array
    {
        $chatbotId = $this->resolveChatbotId($source, $histories);

        if ($chatbotId < 1) {
            return [];
        }

        return Cache::remember(self::cacheKey($chatbotId), 3600, static function () use ($chatbotId): array {
            $raw = Chatbot::query()->whereKey($chatbotId)->value('enquiry_interests');

            return ChatbotInterestDictionary::normalize(is_array($raw) ? $raw : []);
        });
    }

    /**
     * @param  ChatbotConversation|Collection<int, ChatbotHistory>  $source
     * @param  Collection<int, ChatbotHistory>  $histories
     */
    private function resolveChatbotId(ChatbotConversation|Collection $source, Collection $histories): int
    {
        if ($source instanceof ChatbotConversation) {
            return (int) $source->getAttribute('chatbot_id');
        }

        $first = $histories->first();

        if (! $first instanceof ChatbotHistory) {
            return 0;
        }

        $chatbotId = (int) $first->getAttribute('chatbot_id');

        if ($chatbotId > 0) {
            return $chatbotId;
        }

        return (int) $first->conversation()?->getAttribute('chatbot_id');
    }

    /**
     * @param  array{email: ?string, found: bool}  $email
     * @param  array{phone: ?string, found: bool}  $phone
     * @param  array{company: ?string, found: bool}  $company
     * @param  array{interest: ?string, matched_keyword: ?string, interests: array<int, string>, found: bool}  $interest
     * @return array{0: array, 1: array, 2: array, 3: array}
     */
    private function crossValidate(
        array $email,
        array $phone,
        array $company,
        array $interest,
        InterestDetector $interestDetector,
    ): array {
        $emailValue = $email['email'] ?? null;
        $phoneValue = $phone['phone'] ?? null;
        $companyValue = $company['company'] ?? null;
        $interestValue = $interest['interest'] ?? null;

        if (is_string($companyValue) && $companyValue !== '') {
            if (
                ($emailValue && str_contains(strtolower($companyValue), strtolower($emailValue)))
                || ($phoneValue && str_contains($companyValue, preg_replace('/\D+/', '', $phoneValue) ?? ''))
                || preg_match('/@|https?:\/\//i', $companyValue) === 1
                || $this->containsRecognisedInterestLabel($companyValue, $interestDetector)
            ) {
                $company = ['company' => null, 'found' => false];
                $companyValue = null;
            }
        }

        if (is_string($interestValue) && $interestValue !== '') {
            $labels = array_values(array_filter(array_map('trim', explode(',', $interestValue))));
            $allowed = $interestDetector->recognisedLabels();
            $labels = array_values(array_filter(
                $labels,
                static fn (string $label): bool => in_array($label, $allowed, true)
            ));

            if ($labels === []) {
                $interest = [
                    'interest'        => null,
                    'matched_keyword' => null,
                    'interests'       => [],
                    'found'           => false,
                ];
            } else {
                $interest['interests'] = $labels;
                $interest['interest'] = implode(', ', $labels);
                $interest['found'] = true;
            }
        }

        return [$email, $phone, $company, $interest];
    }

    private function containsRecognisedInterestLabel(string $value, InterestDetector $interestDetector): bool
    {
        $lower = strtolower($value);

        foreach ($interestDetector->recognisedLabels() as $label) {
            if ($lower === strtolower($label)) {
                return true;
            }
        }

        return false;
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

    private static function cacheKey(int $chatbotId): string
    {
        return "ext_chatbot.{$chatbotId}.enquiry_interests";
    }
}
