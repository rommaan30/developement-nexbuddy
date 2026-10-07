@php
    $languages = array_values(array_filter(explode(',', (string) ($settings_two->languages ?? ''))));
    $accountUrl = auth()->check() ? route('dashboard.index') : route('register');
@endphp

<header class="nexbuddy-header">
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a href="{{ route('index') }}" class="navbar-brand brand-wrapper">
                <img src="{{ custom_theme_url('assets/img/nexbuddy/nexgeno-logo.png') }}" alt="Nexgeno Technology Private Limited" class="brand-logo-img">
            </a>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto nav-links">
                    <li class="nav-item"><a class="nav-link" href="{{ route('index') }}#features">{{ __('Features') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('index') }}#ai-tools">{{ __('AI Tools') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('index') }}#how-it-works">{{ __('How it works') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'is-active' : '' }}" href="{{ route('pricing') }}">{{ __('Pricing') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">{{ __('Contact') }}</a></li>
                </ul>
            </div>
            <div class="navbar-buttons">
                @if (count($languages) > 1)
                    <details class="lang-menu">
                        <summary class="signin-btn">{{ strtoupper(app()->getLocale()) }}</summary>
                        <div class="lang-list">
                            @foreach (\App\Helpers\Classes\Localization::getSupportedLocales() as $localeCode => $properties)
                                @if (in_array($localeCode, $languages))
                                    <a href="{{ route('language.change', $localeCode) }}" rel="alternate" hreflang="{{ $localeCode }}">
                                        {{ $properties['native'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endif

                @auth
                    <a href="{{ route('dashboard.index') }}" class="start-btn">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="signin-btn">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="start-btn">{{ __('Start Free') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    @includeWhen($fSetting->floating_button_active, 'landing-page.header.floating-button')
</header>

@includeWhen($app_is_demo, 'landing-page.header.envato-link')

@includeWhen(in_array($settings_two->chatbot_status, ['frontend', 'both']) &&
        ($settings_two->chatbot_login_require == false || ($settings_two->chatbot_login_require == true && auth()->check())),
    'panel.chatbot.widget',
    ['page' => 'landing-page']
)
