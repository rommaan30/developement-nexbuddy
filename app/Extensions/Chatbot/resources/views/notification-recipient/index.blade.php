@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $title)
@section('titlebar_subtitle', $description)
@section('titlebar_actions')
    <x-button
        class="mb-4"
        variant="primary"
        href="{{ route($routePrefix . '.recipient.create') }}"
    >
        <x-tabler-plus class="size-4" />
        {{ __('Add Recipient') }}
    </x-button>
@endsection

@section('content')
    <div class="py-10">
        <x-card class="mb-6" class:body="p-5">
            <div class="mb-5 flex flex-wrap items-center gap-4 text-sm">
                <span class="opacity-70">
                    {{ __('Total Recipients') }}: <strong>{{ $totalCount }}</strong>
                </span>
                <span class="opacity-70">
                    {{ __('Active') }}: <strong>{{ $activeCount }}</strong>
                </span>
            </div>

            <form
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5"
                action="{{ route($routePrefix . '.recipient.index') }}"
                method="GET"
            >
                <x-forms.input
                    class="rounded-full ps-10"
                    name="search"
                    size="sm"
                    type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('Search name or email') }}"
                >
                    <x-slot:icon>
                        <span class="absolute start-3 top-1/2 -translate-y-1/2">
                            <x-tabler-search class="size-4" />
                        </span>
                    </x-slot:icon>
                </x-forms.input>

                <x-forms.input
                    name="chatbot_id"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Chatbots') }}
                    </option>
                    @foreach ($chatbots as $chatbot)
                        <option
                            value="{{ $chatbot->id }}"
                            @selected((string) $filters['chatbot_id'] === (string) $chatbot->id)
                        >
                            {{ $chatbot->title }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    name="notification_type"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Notification Types') }}
                    </option>
                    @foreach ($types as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['notification_type'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    name="status"
                    size="sm"
                    type="select"
                >
                    <option value="">
                        {{ __('All Statuses') }}
                    </option>
                    @foreach ($statusFilters as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['status'] === $value)
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
                        href="{{ route($routePrefix . '.recipient.index') }}"
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
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Chatbot') }}</th>
                        <th>{{ __('Notification Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($items as $entry)
                        <tr id="notification-recipient-{{ $entry->id }}">
                            <td>{{ $entry->name }}</td>
                            <td>{{ $entry->email }}</td>
                            <td>{{ $entry->chatbot?->title ?: __('Unknown') }}</td>
                            <td>{{ $entry->notification_type->label() }}</td>
                            <td>
                                <x-badge variant="{{ $entry->is_active ? 'success' : 'secondary' }}">
                                    {{ $entry->is_active ? __('Active') : __('Inactive') }}
                                </x-badge>
                            </td>
                            <td>
                                <p class="m-0">
                                    {{ $entry->created_at?->format('j.n.Y') }}
                                    <span class="block opacity-60">
                                        {{ $entry->created_at?->format('H:i') }}
                                    </span>
                                </p>
                            </td>
                            <td class="whitespace-nowrap text-end">
                                <x-button
                                    class="size-9"
                                    size="none"
                                    variant="ghost-shadow"
                                    hover-variant="primary"
                                    href="{{ route($routePrefix . '.recipient.show', $entry->id) }}"
                                    title="{{ __('View') }}"
                                >
                                    <x-tabler-eye class="size-4" />
                                </x-button>

                                <x-button
                                    class="size-9"
                                    size="none"
                                    variant="ghost-shadow"
                                    hover-variant="primary"
                                    href="{{ route($routePrefix . '.recipient.edit', $entry->id) }}"
                                    title="{{ __('Edit') }}"
                                >
                                    <x-tabler-pencil class="size-4" />
                                </x-button>

                                <form
                                    method="POST"
                                    action="{{ route($routePrefix . '.recipient.toggle', array_merge(request()->query(), ['notification_recipient' => $entry->id])) }}"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <x-button
                                        class="size-9"
                                        size="none"
                                        variant="ghost-shadow"
                                        hover-variant="primary"
                                        type="submit"
                                        title="{{ $entry->is_active ? __('Disable') : __('Enable') }}"
                                    >
                                        @if ($entry->is_active)
                                            <x-tabler-toggle-right class="size-4" />
                                        @else
                                            <x-tabler-toggle-left class="size-4" />
                                        @endif
                                    </x-button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route($routePrefix . '.recipient.destroy', array_merge(request()->query(), ['notification_recipient' => $entry->id])) }}"
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
                                        onclick="return confirm('{{ __('Are you sure you want to delete this recipient? This action cannot be undone.') }}')"
                                        title="{{ __('Delete') }}"
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
                    icon="tabler-mail-off"
                    title="{{ __('No recipients found') }}"
                    description="{{ $totalCount > 0 ? __('No recipients match the current filters.') : __('Add a recipient to start sending internal notifications.') }}"
                />
            </x-card>
        @endif
    </div>
@endsection

@push('script')
@endpush
