@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $title)
@section('titlebar_subtitle', $description)
@section('titlebar_actions')

@endsection

@section('content')
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

        <x-card class:body="p-8">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
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

            @unless ($hasEnquiries)
                <div class="rounded-2xl border border-dashed p-10 text-center">
                    <x-tabler-inbox class="mx-auto mb-4 size-10 text-heading-foreground/40" />
                    <h3 class="mb-2 text-lg font-semibold text-heading-foreground">
                        {{ __('No enquiries found') }}
                    </h3>
                    <p class="mx-auto mb-0 max-w-xl text-heading-foreground/60">
                        {{ __('AI bot enquiries will appear here after qualifying conversations are detected.') }}
                    </p>
                </div>
            @endunless
        </x-card>
    </div>
@endsection

@push('script')
@endpush
