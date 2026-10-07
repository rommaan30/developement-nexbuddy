<footer class="footer-section" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('index') }}" class="footer-brand">
                    <img src="{{ custom_theme_url('assets/img/nexbuddy/nexgeno-logo.png') }}" alt="Nexgeno Technology Private Limited" class="brand-logo-img">
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
                    <li><a href="{{ route('index') }}#features">{{ __('Features') }}</a></li>
                    <li><a href="{{ route('index') }}#ai-tools">{{ __('AI Tools') }}</a></li>
                    <li><a href="{{ route('index') }}#how-it-works">{{ __('How it works') }}</a></li>
                    <li><a href="{{ route('pricing') }}">{{ __('Pricing') }}</a></li>
                    <li><a href="{{ route('index') }}#faq">{{ __('FAQ') }}</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h4 class="footer-heading">{{ __('Company') }}</h4>
                <ul class="footer-links list-unstyled">
                    <li><a href="#contact">{{ __('Contact') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h4 class="footer-heading">{{ __('Contact') }}</h4>
                <ul class="footer-links list-unstyled">
                    <li><a href="tel:+919773375525">+91 97733 75525</a></li>
                    <li><a href="mailto:sales@nexbuddy.in">sales@nexbuddy.in</a></li>
                    <li>Unit No. F-50, First Floor kohinoor City Mall Opp Holly Cross School, Kurla (West) Mumbai, Maharashtra - 400070.</li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="text-center footer-bottom">
            <p class="footer-copy">© {{ date('Y') }} {{ $setting->site_name ?? 'Nexbuddy' }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</footer>
