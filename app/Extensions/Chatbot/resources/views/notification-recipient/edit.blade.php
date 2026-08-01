@extends('panel.layout.settings', ['disable_tblr' => true])
@section('title', $title)
@section('titlebar_subtitle', $description)
@section('titlebar_actions', '')

@section('settings')
    <form
        class="flex flex-col gap-10"
        action="{{ $action }}"
        method="post"
    >
        @csrf
        @method($method)

        <div class="mt-4 space-y-6">
            <div class="flex flex-col gap-2">
                <x-forms.input
                    id="name"
                    size="lg"
                    name="name"
                    label="{{ __('Name') }}"
                    placeholder="{{ __('Recipient name') }}"
                    value="{{ old('name', $item->name) }}"
                    required
                />
                @error('name')
                    <p class="text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <x-forms.input
                    id="email"
                    size="lg"
                    type="email"
                    name="email"
                    label="{{ __('Email') }}"
                    placeholder="{{ __('name@company.com') }}"
                    value="{{ old('email', $item->email) }}"
                    required
                />
                @error('email')
                    <p class="text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <x-forms.input
                    id="notification_type"
                    size="lg"
                    type="select"
                    name="notification_type"
                    label="{{ __('Notification Type') }}"
                >
                    @foreach ($types as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(old('notification_type', $item->notification_type?->value) === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </x-forms.input>
                @error('notification_type')
                    <p class="text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <x-forms.input
                id="is_active"
                type="checkbox"
                name="is_active"
                value="1"
                label="{{ __('Active') }}"
                tooltip="{{ __('Only active recipients receive notifications.') }}"
                :checked="(bool) old('is_active', $item->is_active)"
                switcher
            />

            @if ($app_is_demo)
                <x-button
                    class="w-full"
                    size="lg"
                    onclick="return toastr.info('This feature is disabled in Demo version.')"
                >
                    {{ __('Save') }}
                </x-button>
            @else
                <x-button
                    class="w-full"
                    size="lg"
                    type="submit"
                >
                    {{ __('Save') }}
                </x-button>
            @endif

            <x-button
                class="w-full"
                size="lg"
                variant="outline"
                href="{{ route('dashboard.admin.notification-management.recipient.index') }}"
            >
                {{ __('Cancel') }}
            </x-button>
        </div>
    </form>
@endsection

@push('script')
@endpush
