@php
    $languages = array_values(array_filter(explode(',', (string) ($settings_two->languages ?? ''))));
    $accountUrl = auth()->check() ? route('dashboard.index') : route('register');
@endphp

<header class="nexbuddy-header">
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a href="{{ route('index') }}" class="navbar-brand brand-wrapper">
                <img src="{{ custom_theme_url('assets/img/nexbuddy/nexgeno-logo.png') }}?v=white" alt="Nexgeno Technology Private Limited" class="brand-logo-img">
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
            <button type="button" class="menu-toggle" aria-label="{{ __('Open menu') }}" aria-expanded="false" aria-controls="mobileDrawer">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
        <div class="mobile-drawer-backdrop"></div>
        <aside class="mobile-drawer-panel" aria-label="{{ __('Menu') }}">
            <button type="button" class="mobile-drawer-close" aria-label="{{ __('Close menu') }}">&times;</button>
            <ul class="mobile-drawer-links">
                <li><a href="{{ route('index') }}#features">{{ __('Features') }}</a></li>
                <li><a href="{{ route('index') }}#ai-tools">{{ __('AI Tools') }}</a></li>
                <li><a href="{{ route('index') }}#how-it-works">{{ __('How it works') }}</a></li>
                <li><a href="{{ route('pricing') }}">{{ __('Pricing') }}</a></li>
                <li><a href="#contact">{{ __('Contact') }}</a></li>
            </ul>
            <div class="mobile-drawer-actions">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="start-btn">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="signin-btn">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="start-btn">{{ __('Start Free') }}</a>
                @endauth
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('.menu-toggle');
            var drawer = document.querySelector('.mobile-drawer');
            if (!toggle || !drawer) return;

            var setOpen = function (open) {
                drawer.classList.toggle('is-open', open);
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.classList.toggle('drawer-open', open);
            };

            toggle.addEventListener('click', function () {
                setOpen(!drawer.classList.contains('is-open'));
            });
            drawer.querySelector('.mobile-drawer-backdrop').addEventListener('click', function () { setOpen(false); });
            drawer.querySelector('.mobile-drawer-close').addEventListener('click', function () { setOpen(false); });
            drawer.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () { setOpen(false); });
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') setOpen(false);
            });
        });
    </script>

    @includeWhen($fSetting->floating_button_active, 'landing-page.header.floating-button')
</header>

@includeWhen($app_is_demo, 'landing-page.header.envato-link')

@includeWhen(in_array($settings_two->chatbot_status, ['frontend', 'both']) &&
        ($settings_two->chatbot_login_require == false || ($settings_two->chatbot_login_require == true && auth()->check())),
    'panel.chatbot.widget',
    ['page' => 'landing-page']
)
