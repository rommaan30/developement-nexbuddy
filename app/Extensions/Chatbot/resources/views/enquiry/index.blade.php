@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $title)
@section('titlebar_subtitle', $description)
@section('titlebar_actions')

@endsection

@section('content')
    @php
        $statusBadgeVariants = [
            'new' => 'primary',
            'contacted' => 'info',
            'qualified' => 'success',
            'closed' => 'secondary',
        ];
    @endphp

    <div class="py-10">
        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-heading-foreground/60">
            <a
                class="transition-colors hover:text-heading-foreground"
                href="{{ route('dashboard.chatbot.index') }}"
            >
                {{ __('AI Chat Bots') }}
            </a>
            <span>/</span>
            <span class="text-heading-foreground">
                {{ __('AI Bot Enquiries') }}
            </span>
        </div>

        <x-card
            class="mb-6"
            class:body="p-6"
        >
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="mb-2 text-2xl font-semibold text-heading-foreground">
                        {{ __('AI Bot Enquiries') }}
                    </h2>
                    <p class="mb-0 text-heading-foreground/60">
                        {{ __('Detected enquiries from AI bot conversations will appear here.') }}
                    </p>
                </div>

                <div class="rounded-xl bg-heading-foreground/[3%] px-5 py-4 text-center">
                    <span class="block text-xs font-medium uppercase tracking-wide text-heading-foreground/60">
                        {{ __('Total Enquiries') }}
                    </span>
                    <span class="font-heading text-3xl font-semibold text-heading-foreground">
                        {{ $totalEnquiries }}
                    </span>
                </div>
            </div>

            <form
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
                action="{{ route('dashboard.chatbot.enquiries.index') }}"
                method="GET"
            >
                <x-forms.input
                    class="rounded-full ps-10"
                    name="search"
                    size="sm"
                    type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('Search enquiries') }}"
                >
                    <x-slot:icon>
                        <span class="absolute start-3 top-1/2 -translate-y-1/2">
                            <x-tabler-search class="size-4" />
                        </span>
                    </x-slot:icon>
                </x-forms.input>

                <x-forms.input
                    name="status"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Statuses') }}
                    </option>
                    @foreach ($statuses as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['status'] === $value)
                        >
                            {{ __($label) }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    name="interest"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Interests') }}
                    </option>
                    @foreach ($interests as $interest)
                        <option
                            value="{{ $interest }}"
                            @selected($filters['interest'] === $interest)
                        >
                            {{ $interest }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    name="lead_score"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Lead Scores') }}
                    </option>
                    @foreach ($leadScoreFilters as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['lead_score'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    name="start_date"
                    size="sm"
                    type="date"
                    value="{{ $filters['start_date'] }}"
                    placeholder="{{ __('Start Date') }}"
                />

                <x-forms.input
                    name="end_date"
                    size="sm"
                    type="date"
                    value="{{ $filters['end_date'] }}"
                    placeholder="{{ __('End Date') }}"
                />

                <x-forms.input
                    name="sort"
                    size="sm"
                    type="select"
                >
                    @foreach ($sortOptions as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['sort'] === $value)
                        >
                            {{ __($label) }}
                        </option>
                    @endforeach
                </x-forms.input>

                <div class="flex flex-wrap items-center gap-2">
                    <x-button
                        class="h-9"
                        tag="button"
                        type="submit"
                        variant="primary"
                    >
                        <x-tabler-filter class="size-4" />
                        {{ __('Apply') }}
                    </x-button>

                    <x-button
                        class="h-9"
                        href="{{ route('dashboard.chatbot.enquiries.index') }}"
                        variant="outline"
                    >
                        {{ __('Clear') }}
                    </x-button>
                </div>
            </form>
        </x-card>

        @if ($items->isNotEmpty())
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>
                            {{ __('Visitor Name') }}
                        </th>
                        <th>
                            {{ __('Email') }}
                        </th>
                        <th>
                            {{ __('Phone') }}
                        </th>
                        <th>
                            {{ __('Company') }}
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
                            {{ __('Notes') }}
                        </th>
                        <th>
                            {{ __('Conversation ID') }}
                        </th>
                        <th>
                            {{ __('Created At') }}
                        </th>
                        <th class="text-end">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($items as $entry)
                        @php
                            $status = strtolower((string) ($entry->status ?: 'new'));
                            $leadScore = max(0, min(100, (int) $entry->lead_score));
                            $leadScoreVariant = $leadScore >= 80 ? 'success' : ($leadScore >= 50 ? 'warning' : 'default');
                        @endphp

                        <tr id="enquiry-{{ $entry->id }}">
                            <td>
                                {{ $entry->visitor_name ?: ($entry->customer?->name ?: '-') }}
                            </td>
                            <td>
                                {{ ($entry->email ?: $entry->customer?->email) ?: '-' }}
                            </td>
                            <td>
                                {{ ($entry->phone ?: $entry->customer?->phone) ?: '-' }}
                            </td>
                            <td>
                                {{ $entry->company ?: '-' }}
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
                                    variant="{{ $statusBadgeVariants[$status] ?? 'default' }}"
                                >
                                    {{ __($statuses[$status] ?? ucfirst($status)) }}
                                </x-badge>

                                <x-forms.input
                                    class="mt-2 min-w-32"
                                    form="enquiry-update-{{ $entry->id }}"
                                    name="status"
                                    size="sm"
                                    type="select"
                                >
                                    @foreach ($statuses as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(old('status', $status) === $value)
                                        >
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </x-forms.input>
                            </td>
                            <td class="min-w-64">
                                <x-forms.input
                                    form="enquiry-update-{{ $entry->id }}"
                                    name="notes"
                                    rows="2"
                                    size="none"
                                    type="textarea"
                                    placeholder="{{ __('Add internal notes') }}"
                                >{{ old('notes', $entry->notes) }}</x-forms.input>
                            </td>
                            <td>
                                {{ $entry->conversation_id ?: '-' }}
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
                                <form
                                    id="enquiry-update-{{ $entry->id }}"
                                    method="POST"
                                    action="{{ route('dashboard.chatbot.enquiries.update', array_merge(request()->query(), ['chatbotEnquiry' => $entry->id])) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                </form>

                                <x-button
                                    class="size-9"
                                    form="enquiry-update-{{ $entry->id }}"
                                    size="none"
                                    tag="button"
                                    type="submit"
                                    variant="ghost-shadow"
                                    hover-variant="primary"
                                    title="{{ __('Save') }}"
                                >
                                    <x-tabler-device-floppy class="size-4" />
                                </x-button>
                                <x-button
                                    class="size-9"
                                    size="none"
                                    variant="ghost-shadow"
                                    hover-variant="primary"
                                    href="{{ route('dashboard.chatbot.index', ['conversation_id' => $entry->conversation_id]) }}"
                                    title="{{ __('Open Conversation') }}"
                                >
                                    <x-tabler-eye class="size-4" />
                                </x-button>
                                <form
                                    method="POST"
                                    action="{{ route('dashboard.chatbot.enquiries.destroy', array_merge(request()->query(), ['chatbotEnquiry' => $entry->id])) }}"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-button
                                        class="size-9"
                                        size="none"
                                        variant="ghost-shadow"
                                        hover-variant="danger"
                                        type="submit"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this enquiry? This action cannot be undone.') }}')"
                                        title="{{ __('Delete Enquiry') }}"
                                    >
                                        <x-tabler-trash class="size-4" />
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @else
            <x-card class:body="p-10">
                <x-empty-state
                    icon="tabler-inbox"
                    title="{{ __('No enquiries found') }}"
                    description="{{ $totalEnquiries > 0 ? __('No enquiries match the current filters.') : __('AI bot enquiries will appear here after qualifying conversations are detected.') }}"
                />
            </x-card>
        @endif
    </div>
@endsection

@push('script')
@endpush
