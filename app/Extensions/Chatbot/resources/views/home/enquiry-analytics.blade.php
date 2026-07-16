@php
    $enquiryStatusBadgeVariants = [
        'new' => 'primary',
        'contacted' => 'info',
        'qualified' => 'success',
        'closed' => 'secondary',
    ];

    $enquiryStatCards = [
        [
            'label' => __('Total Enquiries'),
            'value' => $enquiryAnalytics['total'] ?? 0,
        ],
        [
            'label' => __('New Enquiries'),
            'value' => $enquiryAnalytics['new'] ?? 0,
        ],
        [
            'label' => __('Contacted'),
            'value' => $enquiryAnalytics['contacted'] ?? 0,
        ],
        [
            'label' => __('Qualified'),
            'value' => $enquiryAnalytics['qualified'] ?? 0,
        ],
        [
            'label' => __('Closed'),
            'value' => $enquiryAnalytics['closed'] ?? 0,
        ],
    ];
@endphp

<div class="my-9">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="mb-2 text-2xl font-semibold text-heading-foreground">
                {{ __('AI Bot Enquiries Analytics') }}
            </h3>
            <p class="mb-0 text-heading-foreground/60">
                {{ __('Track extracted enquiries and review the newest leads from your AI bot conversations.') }}
            </p>
        </div>

        <x-button
            href="{{ route('dashboard.chatbot.enquiries.index') }}"
            variant="ghost-shadow"
        >
            {{ __('View All Enquiries') }}
            <x-tabler-chevron-right class="size-4" />
        </x-button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($enquiryStatCards as $card)
            <x-card class:body="px-6 py-5">
                <h4 class="mb-3 text-sm font-medium text-heading-foreground/70">
                    {{ $card['label'] }}
                </h4>
                <p class="mb-0 flex items-center text-[24px] font-medium text-heading-foreground">
                    {{ number_format($card['value']) }}
                </p>
            </x-card>
        @endforeach
    </div>

    <x-card class:body="px-0 pb-0">
        <x-slot:head
            class="flex items-center justify-between border-0 px-6 pt-6"
        >
            <h4 class="m-0 text-sm font-medium">
                {{ __('Latest Enquiries') }}
            </h4>
        </x-slot:head>

        @if ($latestEnquiries->isNotEmpty())
            <div class="overflow-x-auto">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>
                                {{ __('Visitor') }}
                            </th>
                            <th>
                                {{ __('Interest') }}
                            </th>
                            <th>
                                {{ __('Lead Score') }}
                            </th>
                            <th>
                                {{ __('Status') }}
                            </th>
                            <th>
                                {{ __('Date') }}
                            </th>
                            <th class="text-end">
                                {{ __('Quick Actions') }}
                            </th>
                        </tr>
                    </x-slot:head>

                    <x-slot:body>
                        @foreach ($latestEnquiries as $entry)
                            @php
                                $status = strtolower((string) ($entry->status ?: 'new'));
                                $leadScore = max(0, min(100, (int) $entry->lead_score));
                                $leadScoreVariant = $leadScore >= 80 ? 'success' : ($leadScore >= 50 ? 'warning' : 'default');
                            @endphp

                            <tr id="dashboard-enquiry-{{ $entry->id }}">
                                <td>
                                    {{ $entry->customer?->name ?: '-' }}
                                </td>
                                <td>
                                    {{ $entry->interest ?: '-' }}
                                </td>
                                <td>
                                    <x-badge
                                        class="text-2xs"
                                        variant="{{ $leadScoreVariant }}"
                                    >
                                        {{ $leadScore }}
                                    </x-badge>
                                </td>
                                <td>
                                    <x-badge
                                        class="text-2xs"
                                        variant="{{ $enquiryStatusBadgeVariants[$status] ?? 'default' }}"
                                    >
                                        {{ __(ucfirst($status)) }}
                                    </x-badge>
                                </td>
                                <td>
                                    <p class="m-0">
                                        {{ $entry->created_at?->format('j.n.Y') ?? '-' }}
                                        @if ($entry->created_at)
                                            <span class="block opacity-60">
                                                {{ $entry->created_at->format('H:i:s') }}
                                            </span>
                                        @endif
                                    </p>
                                </td>
                                <td class="whitespace-nowrap text-end">
                                    <x-button
                                        class="size-9"
                                        size="none"
                                        variant="ghost-shadow"
                                        hover-variant="primary"
                                        href="{{ route('dashboard.chatbot.index', ['conversation_id' => $entry->conversation_id]) }}"
                                        title="{{ __('View Conversation') }}"
                                    >
                                        <x-tabler-eye class="size-4" />
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-table>
            </div>
        @else
            <x-empty-state
                class="px-6 pb-10"
                icon="tabler-inbox"
                title="{{ __('No enquiries found') }}"
                description="{{ __('Latest AI bot enquiries will appear here after qualifying conversations are detected.') }}"
            />
        @endif
    </x-card>
</div>
