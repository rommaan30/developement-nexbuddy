@extends('layout.app')

@section('content')
    <section class="pricing-page" id="pricing">
        <div class="container">
            <div class="pricing-heading">
                <p class="pricing-kicker">{{ __('PRICING') }}</p>
                <h1 class="pricing-title">
                    {{ __('Flexible plans.') }}
                    <span class="gradient-text">{{ __('Credit-based usage.') }}</span>
                </h1>
                <p class="pricing-lead">{{ __('Start free. Scale as you grow. Switch monthly or yearly billing anytime.') }}</p>
            </div>

            <div class="pricing-switch">
                <div class="billing-toggle" role="group" aria-label="{{ __('Billing period') }}">
                    <button type="button" data-billing="monthly">{{ __('Monthly') }}</button>
                    <button type="button" class="is-active" data-billing="yearly">{{ __('Yearly') }} <span class="billing-save">{{ __('Save ~17%') }}</span></button>
                </div>
            </div>

            <div class="plan-grid">
                <article class="plan-card">
                    <h2 class="plan-name">{{ __('Starter') }}</h2>
                    <p class="plan-audience">{{ __('For solo founders & side projects') }}</p>
                    <p class="plan-price"><span class="plan-amount" data-monthly="₹999" data-yearly="₹833">₹833</span><span class="plan-period">/ {{ __('mo') }}</span></p>
                    <p class="plan-billed" data-monthly="" data-yearly="{{ __('Billed ₹9,990 / year') }}">{{ __('Billed ₹9,990 / year') }}</p>
                    <p class="plan-credits">✦ {{ __('5,000 credits / month') }}</p>
                    <ul class="plan-features">
                        @foreach ([__('1 AI Assistant'), __('Limited AI Tools access'), __('Basic AI training (PDFs, URLs)'), __('Website embed'), __('Email support'), __('Community access')] as $feature)
                            <li class="plan-feature"><span class="plan-check"></span>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a class="plan-cta" data-monthly="{{ $starterMonthly }}" data-yearly="{{ $starterYearly }}" href="{{ $starterYearly }}">{{ __('Start Free Trial') }}</a>
                </article>

                <article class="plan-card is-popular">
                    <span class="plan-badge">{{ __('Most Popular') }}</span>
                    <h2 class="plan-name">{{ __('Growth') }}</h2>
                    <p class="plan-audience">{{ __('For growing startups & agencies') }}</p>
                    <p class="plan-price"><span class="plan-amount" data-monthly="₹2,999" data-yearly="₹2,499">₹2,499</span><span class="plan-period">/ {{ __('mo') }}</span></p>
                    <p class="plan-billed" data-monthly="" data-yearly="{{ __('Billed ₹29,990 / year') }}">{{ __('Billed ₹29,990 / year') }}</p>
                    <p class="plan-credits">✦ {{ __('25,000 credits / month') }}</p>
                    <ul class="plan-features">
                        @foreach ([__('3 AI Assistants'), __('Advanced AI Tools access'), __('Full AI training suite'), __('Team collaboration (3 seats)'), __('Limited API access'), __('WhatsApp + CRM integrations'), __('Priority email & chat support')] as $feature)
                            <li class="plan-feature"><span class="plan-check"></span>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a class="plan-cta" data-monthly="{{ $growthMonthly }}" data-yearly="{{ $growthYearly }}" href="{{ $growthYearly }}">{{ __('Choose Growth') }}</a>
                </article>

                <article class="plan-card">
                    <h2 class="plan-name">{{ __('Business') }}</h2>
                    <p class="plan-audience">{{ __('For scaling teams & operations') }}</p>
                    <p class="plan-price"><span class="plan-amount" data-monthly="₹6,999" data-yearly="₹5,833">₹5,833</span><span class="plan-period">/ {{ __('mo') }}</span></p>
                    <p class="plan-billed" data-monthly="" data-yearly="{{ __('Billed ₹69,990 / year') }}">{{ __('Billed ₹69,990 / year') }}</p>
                    <p class="plan-credits">✦ {{ __('100,000 credits / month') }}</p>
                    <ul class="plan-features">
                        @foreach ([__('10 AI Assistants'), __('Premium AI Tools access'), __('Advanced AI training & analytics'), __('Team collaboration (10 seats)'), __('Full API access'), __('Priority support + SLA'), __('Custom branding')] as $feature)
                            <li class="plan-feature"><span class="plan-check"></span>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a class="plan-cta" data-monthly="{{ $businessMonthly }}" data-yearly="{{ $businessYearly }}" href="{{ $businessYearly }}">{{ __('Choose Business') }}</a>
                </article>

                <article class="plan-card">
                    <h2 class="plan-name">{{ __('Enterprise') }}</h2>
                    <p class="plan-audience">{{ __('Custom AI for large organizations') }}</p>
                    <p class="plan-price">{{ __('Custom') }}</p>
                    <p class="plan-billed"></p>
                    <p class="plan-credits">✦ {{ __('Custom credits & SLA') }}</p>
                    <ul class="plan-features">
                        @foreach ([__('Unlimited AI Assistants'), __('Full AI Tools suite'), __('Advanced AI training + fine-tuning'), __('SSO & granular roles'), __('Dedicated account manager'), __('Data residency & DPA'), __('Custom integrations')] as $feature)
                            <li class="plan-feature"><span class="plan-check"></span>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a class="plan-cta" href="{{ $salesUrl }}">{{ __('Talk to Sales') }}</a>
                </article>
            </div>

            <p class="pricing-note">{{ __('All plans include SSL, encrypted storage, GDPR-aligned processing and access to credit top-up packs. GST extra as applicable.') }}</p>

            <div class="compare-block">
                <p class="pricing-kicker">{{ __('COMPARE') }}</p>
                <h2 class="compare-title">{{ __('Find your perfect fit') }}</h2>
                <div class="compare-wrap">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th>{{ __('Features') }}</th>
                                <th>{{ __('Starter') }}</th>
                                <th>{{ __('Growth') }} <span class="compare-popular">{{ __('Popular') }}</span></th>
                                <th>{{ __('Business') }}</th>
                                <th>{{ __('Enterprise') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ([
                                [__('AI Assistants'), '1', '3', '10', __('Unlimited')],
                                [__('AI Tools Access'), __('Limited'), __('Advanced'), __('Premium'), __('Full Suite')],
                                [__('Monthly Credits'), '5K', '25K', '100K', __('Custom')],
                                [__('AI Training'), __('Basic'), __('Advanced'), __('Advanced+'), __('Fine-tuning')],
                                [__('Team Seats'), '1', '3', '10', __('Unlimited')],
                                [__('API Access'), '—', __('Limited'), __('Full'), __('Full + Custom')],
                                [__('Integrations'), __('Web'), __('Web + WhatsApp + CRM'), __('All + Custom'), __('All + Custom')],
                                [__('Support'), __('Email'), __('Priority'), __('Priority + SLA'), __('Dedicated')],
                            ] as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pricing-demo">
                <h2>{{ __('Not sure which plan fits?') }}</h2>
                <p>{{ __('Get a free 20-minute strategy call with our AI experts.') }}</p>
                <a class="plan-cta" href="{{ $salesUrl }}">{{ __('Book a Demo') }}</a>
            </div>
        </div>
    </section>
@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ custom_theme_url('assets/css/frontend/nexbuddy-home.css') }}">
@endpush

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-billing]').forEach(function (button) {
            button.addEventListener('click', function () {
                var cycle = button.getAttribute('data-billing');
                document.querySelectorAll('[data-billing]').forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });
                document.querySelectorAll('[data-monthly][data-yearly]').forEach(function (node) {
                    var value = node.getAttribute('data-' + cycle);
                    if (node.tagName === 'A') {
                        node.setAttribute('href', value);
                        return;
                    }
                    node.textContent = value;
                });
            });
        });
    </script>
@endpush
