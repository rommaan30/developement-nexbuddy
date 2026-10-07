<footer class="footer-section" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('index') }}" class="footer-brand">
                    <span class="brand-logo">N</span>
                    <span class="brand-name"><span class="brand-white">Nex</span><span class="brand-pink">buddy</span></span>
                </a>
                <p class="footer-desc">{{ __('Empowering businesses with next-generation AI automation and intelligent digital workflows. Build, train and deploy AI assistants and access 20+ AI tools — all from one unified platform.') }}</p>
                <div class="footer-badges">
                    <span class="footer-badge">SSL Secured</span>
                    <span class="footer-badge">GDPR-Ready</span>
                    <span class="footer-badge">AES-256</span>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h4 class="footer-heading">{{ __('Product') }}</h4>
                <ul class="footer-links list-unstyled">
                    <li><a href="#features">{{ __('Features') }}</a></li>
                    <li><a href="#ai-tools">{{ __('AI Tools') }}</a></li>
                    <li><a href="#how-it-works">{{ __('How it works') }}</a></li>
                    <li><a href="#pricing">{{ __('Pricing') }}</a></li>
                    <li><a href="#faq">{{ __('FAQ') }}</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h4 class="footer-heading">{{ __('Company') }}</h4>
                <ul class="footer-links list-unstyled">
                    <li><a href="#contact">{{ __('Contact') }}</a></li>
                    <li><a href="{{ route('pagePrivacy') }}">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="{{ route('pageTerms') }}">{{ __('Terms of Service') }}</a></li>
                    @foreach (\App\Models\Page::where(['status' => 1, 'show_on_footer' => 1])->get() ?? [] as $page)
                        <li>
                            <a href="/page/{{ $page->slug }}">{{ $page->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-4">
                <h4 class="footer-heading">{{ __('Contact') }}</h4>
                <ul class="footer-links list-unstyled">
                    <li><a href="tel:+919773375525">+91 97733 75525</a></li>
                    <li><a href="mailto:sales@nexbuddy.in">sales@nexbuddy.in</a></li>
                    <li>F-50, Kohinoor City Mall, Kurla (W), Mumbai 400070</li>
                </ul>
                @php
                    $socials = \App\Models\SocialMediaAccounts::where('is_active', true)->get();
                @endphp
                @if ($socials->isNotEmpty())
                    <ul class="footer-links list-unstyled footer-socials">
                        @foreach ($socials as $social)
                            <li>
                                <a href="{{ $social['link'] }}">
                                    <span class="footer-social-icon">{!! $social['icon'] !!}</span>
                                    {{ $social['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <hr class="footer-divider">
        <div class="text-center footer-bottom">
            <p class="footer-copy">© {{ date('Y') }} {{ $setting->site_name ?? 'Nexbuddy' }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</footer>
