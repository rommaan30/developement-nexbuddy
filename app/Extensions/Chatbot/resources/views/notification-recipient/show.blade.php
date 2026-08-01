@extends('panel.layout.settings', ['disable_tblr' => true])
@section('title', $title)
@section('titlebar_subtitle', $description)
@section('titlebar_actions', '')

@section('settings')
    <div class="mt-4 space-y-6">
        <dl class="divide-y divide-input-border">
            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Name') }}
                </dt>
                <dd class="m-0 font-medium">
                    {{ $item->name }}
                </dd>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Email') }}
                </dt>
                <dd class="m-0 font-medium">
                    {{ $item->email }}
                </dd>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Notification Type') }}
                </dt>
                <dd class="m-0 font-medium">
                    {{ $item->notification_type->label() }}
                </dd>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Status') }}
                </dt>
                <dd class="m-0">
                    <x-badge variant="{{ $item->is_active ? 'success' : 'secondary' }}">
                        {{ $item->is_active ? __('Active') : __('Inactive') }}
                    </x-badge>
                </dd>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Created') }}
                </dt>
                <dd class="m-0 font-medium">
                    {{ $item->created_at?->format('j.n.Y H:i') }}
                </dd>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="opacity-70">
                    {{ __('Last Updated') }}
                </dt>
                <dd class="m-0 font-medium">
                    {{ $item->updated_at?->format('j.n.Y H:i') }}
                </dd>
            </div>
        </dl>

        <div class="flex flex-wrap gap-3">
            <x-button
                size="lg"
                href="{{ route('dashboard.admin.notification-management.recipient.edit', $item->getKey()) }}"
            >
                {{ __('Edit') }}
            </x-button>

            <x-button
                size="lg"
                variant="outline"
                href="{{ route('dashboard.admin.notification-management.recipient.index') }}"
            >
                {{ __('Back to Recipients') }}
            </x-button>
        </div>
    </div>
@endsection

@push('script')
@endpush
