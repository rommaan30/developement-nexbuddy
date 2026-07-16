<?php

namespace App\Extensions\Chatbot\System\Http\Controllers;

use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ChatbotEnquiryController extends Controller
{
    private const STATUSES = [
        'new'       => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'closed'    => 'Closed',
    ];

    private const LEAD_SCORE_FILTERS = [
        '0-49'   => '0 - 49',
        '50-79'  => '50 - 79',
        '80-100' => '80 - 100',
    ];

    private const SORT_OPTIONS = [
        'latest'     => 'Latest',
        'oldest'     => 'Oldest',
        'lead_score' => 'Lead Score',
    ];

    public function index(Request $request): View
    {
        $chatbotIds = auth()->user()?->externalChatbots()->pluck('id')->toArray() ?? [];
        $scopedQuery = ChatbotEnquiry::query()->whereIn('chatbot_id', $chatbotIds);
        $totalEnquiries = (clone $scopedQuery)->count();
        $filters = [
            'search'     => trim((string) $request->query('search', '')),
            'status'     => (string) $request->query('status', ''),
            'interest'   => (string) $request->query('interest', ''),
            'lead_score' => (string) $request->query('lead_score', ''),
            'start_date' => (string) $request->query('start_date', ''),
            'end_date'   => (string) $request->query('end_date', ''),
            'sort'       => (string) $request->query('sort', 'latest'),
        ];
        $sort = array_key_exists($filters['sort'], self::SORT_OPTIONS) ? $filters['sort'] : 'latest';

        $items = (clone $scopedQuery)
            ->with([
                'customer:id,name,email,phone',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('interest', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(array_key_exists($filters['status'], self::STATUSES), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['interest'] !== '', function (Builder $query) use ($filters) {
                $query->where('interest', $filters['interest']);
            })
            ->when(array_key_exists($filters['lead_score'], self::LEAD_SCORE_FILTERS), function (Builder $query) use ($filters) {
                [$minimum, $maximum] = array_map('intval', explode('-', $filters['lead_score']));

                $query->whereBetween('lead_score', [$minimum, $maximum]);
            })
            ->when($filters['start_date'] !== '', function (Builder $query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['start_date']);
            })
            ->when($filters['end_date'] !== '', function (Builder $query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['end_date']);
            })
            ->when($sort === 'oldest', function (Builder $query) {
                $query->orderBy('created_at')->orderBy('id');
            })
            ->when($sort === 'lead_score', function (Builder $query) {
                $query->orderByDesc('lead_score')->orderByDesc('created_at')->orderByDesc('id');
            })
            ->when($sort === 'latest', function (Builder $query) {
                $query->orderByDesc('created_at')->orderByDesc('id');
            })
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        $interests = (clone $scopedQuery)
            ->whereNotNull('interest')
            ->where('interest', '!=', '')
            ->distinct()
            ->orderBy('interest')
            ->pluck('interest');

        return view('chatbot::enquiry.index', [
            'filters'          => $filters,
            'hasEnquiries'     => $items->isNotEmpty(),
            'interests'        => $interests,
            'items'            => $items,
            'leadScoreFilters' => self::LEAD_SCORE_FILTERS,
            'sortOptions'      => self::SORT_OPTIONS,
            'statuses'         => self::STATUSES,
            'totalEnquiries'   => $totalEnquiries,
            'title'            => __('AI Bot Enquiries'),
            'description'      => __('View and manage enquiries detected from AI bot conversations.'),
        ]);
    }

    public function update(Request $request, ChatbotEnquiry $chatbotEnquiry): RedirectResponse
    {
        $chatbotIds = auth()->user()?->externalChatbots()->pluck('id')->toArray() ?? [];

        $enquiry = ChatbotEnquiry::query()
            ->whereKey($chatbotEnquiry->getKey())
            ->whereIn('chatbot_id', $chatbotIds)
            ->firstOrFail();

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'notes'  => ['nullable', 'string', 'max:5000'],
        ]);

        $enquiry->update($data);

        return redirect()
            ->route('dashboard.chatbot.enquiries.index', $request->query())
            ->with([
                'type'    => 'success',
                'message' => __('Enquiry updated.'),
            ]);
    }
}
