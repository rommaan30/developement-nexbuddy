<x-mail::message>
# {{ __('New AI Bot Enquiry Received') }}

{{ __('A new enquiry has been generated from the AI Chatbot.') }}

@if (filled($visitorDetails))
## {{ __('Visitor Details') }}

@foreach ($visitorDetails as $label => $value)
- **{{ $label }}:** {{ $value }}
@endforeach
@endif

@if (filled($businessDetails))
## {{ __('Business Details') }}

@foreach ($businessDetails as $label => $value)
- **{{ $label }}:** {{ $value }}
@endforeach
@endif

@if (filled($leadInformation))
## {{ __('Lead Information') }}

@foreach ($leadInformation as $label => $value)
- **{{ $label }}:** {{ $value }}
@endforeach
@endif

@if ($dashboardUrl)
<x-mail::button :url="$dashboardUrl">
{{ __('View Enquiry') }}
</x-mail::button>
@endif

{{ __('This is an internal notification. The visitor has not received a copy of this email.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
