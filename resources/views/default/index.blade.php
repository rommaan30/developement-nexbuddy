@extends('layout.app')

@php
    $heroImage = custom_theme_url('assets/img/nexbuddy/hero.jpg');
    $orbImage = custom_theme_url('assets/img/nexbuddy/nexbuddy-orb.png');
    $leadImage = custom_theme_url('assets/img/nexbuddy/lead.jpg');
    $teamImage = custom_theme_url('assets/img/nexbuddy/team.jpg');
    $accountUrl = auth()->check() ? route('dashboard.index') : route('register');
    $trialLabel = auth()->check() ? __('Go to Dashboard') : __('Start Free Trial');
    $toolsLabel = auth()->check() ? __('Open AI Tools') : __('Try All Tools Free');
    $demoUrl = $fSetting->hero_button_url ?? '';
    $demoIsVideo = (int) ($fSetting->hero_button_type ?? 1) !== 1 && filled($demoUrl);
@endphp

@section('content')
    <section class="hero-section">
        <div class="container">
            <div class="row mt-5 align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <div class="hero-badge">
                            <span class="badge-dot"></span>
                            <span>{{ __('New · AI Assistant Platform · Powered by Nexbuddy') }}</span>
                        </div>
                        <h1 class="hero-title">
                            {{ __('Build Your Own') }}
                            <span class="gradient-text">{{ __('AI') }}</span>
                            <span class="gradient-text">{{ __('Assistant Platform') }}</span>
                            {{ __('for Business Growth') }}
                        </h1>
                        <p class="hero-description">
                            {{ __('Train intelligent AI assistants using your own business data, automate customer conversations, generate content, and access powerful AI tools — all from one unified platform.') }}
                        </p>
                        <div class="hero-buttons">
                            <a href="{{ $accountUrl }}" class="hero-start-btn">{{ $trialLabel }} <span class="arrow">→</span></a>
                            @if ($demoIsVideo)
                                <a href="{{ $demoUrl }}" class="hero-demo-btn" data-fslightbox="video-gallery"><span class="play-icon">▶</span> {{ __('Watch Demo') }}</a>
                            @else
                                <a href="#how-it-works" class="hero-demo-btn"><span class="play-icon">▶</span> {{ __('Watch Demo') }}</a>
                            @endif
                        </div>
                        <div class="hero-features">
                            <span>{{ __('No coding required') }}</span>
                            <span class="feature-separator">•</span>
                            <span>{{ __('Secure cloud platform') }}</span>
                            <span class="feature-separator">•</span>
                            <span>{{ __('Credit-based AI usage') }}</span>
                            <span class="feature-separator">•</span>
                            <span>{{ __('Business-ready automation') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center">
                    <div class="hero-image-column"></div>
                    <div class="hero-image-box">
                        <img src="{{ $heroImage }}" alt="Nexbuddy AI Assistant">
                        <img src="{{ $orbImage }}" alt="Nexbuddy orb" class="hero-orb">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="trust-section">
        <div class="container p-0">
            <p class="section-label text-center mb-5">{{ __('Built on enterprise-grade foundations') }}</p>
            <div class="row">
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('SSL Secured') }}</div>
                        <div class="trust-desc">{{ __('End-to-end TLS on every request') }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('Privacy-First AI') }}</div>
                        <div class="trust-desc">{{ __('Your data is never used to train public models') }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('Encrypted Storage') }}</div>
                        <div class="trust-desc">{{ __('AES-256 at rest, isolated per workspace') }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('Business-Grade Infra') }}</div>
                        <div class="trust-desc">{{ __('Globally distributed, autoscaling cloud') }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('Secure Payments') }}</div>
                        <div class="trust-desc">{{ __('PCI-DSS compliant payment partners') }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                    <div class="trust-card text-center">
                        <div class="trust-title">{{ __('GDPR-Ready') }}</div>
                        <div class="trust-desc">{{ __('Data residency and DPA available') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section section-padding" id="stats">
        <div class="container">
            <div class="stats-heading text-center">
                <div class="stats-eyebrow">{{ __('WHY TEAMS CHOOSE US') }}</div>
                <h2 class="stats-title">
                    {{ __('The unified') }} <span class="gradient-text">{{ __('AI workspace') }}</span> {{ __('for') }}<br>
                    {{ __('modern businesses.') }}
                </h2>
                <p class="stats-description">
                    {{ __('From AI assistants to content, image and productivity tools — NEXBUDDY is the') }}<br class="stats-description-break">
                    {{ __('operating system for your AI-first business.') }}
                </p>
            </div>
            <div class="stats-grid">
                <div class="stat-card text-center">
                    <div class="stat-number gradient-text">10,000+</div>
                    <div class="stat-label">{{ __('AI conversations generated') }}</div>
                </div>
                <div class="stat-card text-center">
                    <div class="stat-number gradient-text">500+</div>
                    <div class="stat-label">{{ __('businesses exploring AI automation') }}</div>
                </div>
                <div class="stat-card text-center">
                    <div class="stat-number gradient-text">99.9%</div>
                    <div class="stat-label">{{ __('platform availability') }}</div>
                </div>
                <div class="stat-card text-center">
                    <div class="stat-number gradient-text">20+</div>
                    <div class="stat-label">{{ __('AI tools in one workspace') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section section-padding" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <p class="stats-eyebrow">{{ __('EVERYTHING IN ONE PLATFORM') }}</p>
                <h2 class="section-title">{{ __('More than a chatbot.') }} <span class="gradient-text">{{ __('A full AI') }} <br>{{ __('workspace.') }}</span></h2>
                <p class="section-subtitle">{{ __('Custom assistants, content tools, image generation, automation — built for startups, agencies and enterprises.') }}</p>
            </div>
            <div class="row g-2">
                @foreach ([
                    ['🤖', __('Custom AI Assistants'), __('Build branded assistants trained on your documents, FAQs and workflows.')],
                    ['🧠', __('AI Chatbot Training'), __('Upload PDFs, sitemaps and knowledge bases — your assistant learns your business.')],
                    ['✍️', __('AI Content Studio'), __('Long-form articles, ads, scripts and rewrites that actually convert.')],
                    ['🎨', __('AI Image & Vision'), __('Generate visuals from prompts and analyze images for context.')],
                    ['⚡', __('Productivity Suite'), __('Speech-to-text, voiceover, code, RSS digests and more — all in one workspace.')],
                    ['📣', __('Marketing Automation'), __('SEO articles, brand voice, YouTube hooks — built to ship campaigns faster.')],
                    ['💎', __('Credit-Based Usage'), __('Pay only for what you use. Scale up or down without rebuying plans.')],
                    ['🔌', __('Business AI Automation'), __('Connect to CRM, WhatsApp and APIs to automate end-to-end workflows.')],
                ] as [$icon, $name, $desc])
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">{{ $icon }}</div>
                            <h3 class="feature-name">{{ $name }}</h3>
                            <p class="feature-desc">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="tools-section section-padding" id="ai-tools">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label tools-label-pink">{{ __('Custom · Templates') }}</p>
                <h2 class="section-title">20+ <span class="gradient-text">{{ __('AI Tools') }}</span> {{ __('in one workspace.') }}</h2>
                <p class="section-subtitle">{{ __('Chatbots, writing, image, code, voice — every AI superpower your team needs.') }}</p>
            </div>
            <div class="row g-2">
                @foreach ([
                    ['🤖', __('AI Chat Assistants'), __('Train branded assistants and embed in minutes.')],
                    ['📝', __('AI Article Wizard'), __('SEO-optimised long-form articles in one click.')],
                    ['✒️', __('AI Writer'), __('Blog posts, emails and ads that convert.')],
                    ['🌄', __('AI Image'), __('Generate stunning visuals from a prompt.')],
                    ['💻', __('AI Code'), __('Production-ready code in any language.')],
                    ['👁️', __('AI Vision'), __('Analyse images for context and content.')],
                    ['🔄', __('AI ReWriter'), __('Polish drafts to professional quality.')],
                    ['▶️', __('AI YouTube'), __('Titles, hooks, scripts and hashtags.')],
                    ['📡', __('AI RSS'), __('Fresh content from your favourite feeds.')],
                    ['🎙️', __('AI Voiceover'), __('Voice-ready scripts with pacing.')],
                    ['🗣️', __('Speech to Text'), __('Clean, punctuated transcripts.')],
                    ['🎤', __('Brand Voice'), __('Define and lock in your brand tone.')],
                ] as [$icon, $name, $desc])
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="tool-card">
                            <div class="feature-icon">{{ $icon }}</div>
                            <div class="tool-name">{{ $name }}</div>
                            <div class="tool-desc">{{ $desc }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-5">
                <a href="{{ $accountUrl }}" class="btn btn-tool btn-lg">{{ $toolsLabel }} →</a>
            </div>
        </div>
    </section>

    <section class="px-1 guide-section section-padding">
        <div class="container">
            <div class="guide-intro text-center px-5">
                <div class="guide-label">{{ __('THE GUIDE') }}</div>
                <h2 class="section-title">{{ __('What is an AI Assistant Platform?') }}</h2>
                <p class="guide-text">{{ __('An AI assistant platform is a unified SaaS environment where businesses can design, train, and deploy intelligent virtual assistants — without writing code. Instead of stitching together half a dozen point tools, Nexbuddy brings AI chatbots, content generation, image creation, automation and analytics into a single workspace billed on flexible credit-based usage.') }}</p>
                <p class="guide-text">{{ __('For modern startups, agencies and enterprises, this means faster customer support, more qualified leads, higher conversion rates and a measurable lift in team productivity — all powered by AI assistants that genuinely understand your business.') }}</p>
            </div>
            <div class="row mt-5 px-5">
                <div class="col-12">
                    <h3 class="guide-subtitle">{{ __('How Businesses Use AI Assistants') }}</h3>
                </div>
                @foreach ([
                    [__('Customer Support'), __('Resolve common queries instantly with brand-trained assistants that escalate to humans only when needed.')],
                    [__('Sales Automation'), __('Qualify leads in real time, recommend the right product and book meetings inside the chat.')],
                    [__('Lead Generation'), __('Convert anonymous visitors into qualified leads with conversational forms and intent detection.')],
                    [__('FAQ Handling'), __('Train your assistant on your knowledge base so customers get accurate answers, 24/7.')],
                    [__('Appointment Booking'), __('Let customers self-serve calendar bookings without leaving the conversation.')],
                    [__('Internal Workflows'), __('Automate HR, IT and ops queries with AI assistants for your own teams.')],
                ] as [$title, $text])
                    <div class="col-md-6 col-lg-4">
                        <div class="guide-list-item">
                            <div><strong>{{ $title }}</strong><br>{{ $text }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row mt-5 px-5">
                <div class="col-12">
                    <h3 class="guide-subtitle">{{ __('How AI Training Works') }}</h3>
                    <p class="guide-text">{{ __('Training a high-quality AI assistant is a guided process — not a one-click illusion. Inside Nexbuddy you upload PDFs, paste website URLs, import sitemaps, add curated FAQs, and define a brand voice. Our AI onboarding system processes this content, indexes it for fast retrieval, and tunes the assistant to respond in your tone and within your business rules.') }}</p>
                    <ul class="training-list">
                        <li>{{ __('Upload knowledge sources (PDF, DOCX, URLs, sitemaps).') }}</li>
                        <li>{{ __('Add curated FAQs and product information.') }}</li>
                        <li>{{ __('Define brand voice, tone and persona guardrails.') }}</li>
                        <li>{{ __('Smart AI learning indexes and embeds your content securely.') }}</li>
                        <li>{{ __('Test in a sandbox, refine answers, then deploy with one click.') }}</li>
                    </ul>
                    <p class="guide-text">{{ __('Most assistants reach production-quality within hours to a few days, depending on the volume of training material and the depth of integrations required.') }}</p>
                </div>
            </div>
            <div class="row mt-5 px-5">
                <div class="col-12">
                    <h3 class="guide-subtitle">{{ __('The Credit-Based AI Usage Model') }}</h3>
                    <p class="guide-text">{{ __('Traditional SaaS forces you to choose a fixed tier and overpay or hit hard limits. NEXBUDDY uses a flexible credit-based model: every AI action — a chat reply, a generated article, an image, a transcript — consumes credits proportional to its complexity. This makes scaling predictable and aligned with your real usage.') }}</p>
                    <div class="row g-2 mt-3">
                        @foreach ([
                            __('Credits consumed only per action — no waste.'),
                            __('Add-on credit packs available anytime.'),
                            __('Roll over unused credits on yearly plans.'),
                            __('Real-time usage analytics inside your dashboard.'),
                        ] as $creditPoint)
                            <div class="col-md-6">
                                <div class="guide-list-item">
                                    <div>{{ $creditPoint }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row mt-5 px-5">
                <div class="col-12">
                    <h3 class="guide-subtitle">{{ __('Security & Privacy You Can Trust') }}</h3>
                    <p class="guide-text">{{ __('NEXBUDDY is built with enterprise-grade security from day one. All traffic is encrypted in transit with TLS, content is stored at rest with AES-256, and every workspace is logically isolated. Your business data is never used to train shared AI models. We provide GDPR-aligned processing, signed DPAs, and data residency options for Business and Enterprise customers.') }}</p>
                </div>
            </div>
            <div class="row mt-5 px-5">
                <div class="col-12">
                    <h3 class="guide-subtitle">{{ __('Why Choose Nexbuddy') }}</h3>
                    <p class="guide-text">{{ __('Unlike generic AI tools, NEXBUDDY is purpose-built for businesses that want to operationalize AI — not just experiment with it. You get a unified workspace, real training on your data, no-code deployment, transparent credit-based pricing, and a team backed by NexGen0\'s experience delivering digital products to 200+ brands.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="lead-section section-padding">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 order-lg-2">
                    <div class="lead-eyebrow">{{ __('AI LEAD MANAGEMENT') }}</div>
                    <h2 class="lead-title">{{ __('From') }} <span class="gradient-text">{{ __('\'Hi\'') }}</span> {{ __('to closed deal — in one workspace.') }}</h2>
                    <p class="lead-text">{{ __('Get summarized, categorized and prioritized conversations in real-time. Trigger automated follow-ups across email and WhatsApp while your team focuses on the deals that matter most.') }}</p>
                    <ul class="lead-list list-unstyled">
                        <li><span class="lead-check">✓</span><span>{{ __('AI-summarized conversations') }}</span></li>
                        <li><span class="lead-check">✓</span><span>{{ __('Auto-categorized intent & sentiment') }}</span></li>
                        <li><span class="lead-check">✓</span><span>{{ __('One-click follow-up email & WhatsApp') }}</span></li>
                        <li><span class="lead-check">✓</span><span>{{ __('Live human takeover when needed') }}</span></li>
                    </ul>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="lead-image-box">
                        <img src="{{ $leadImage }}" alt="Nexbuddy Lead Management">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-section section-padding" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label tools-label-pink">{{ __('How it works') }}</p>
                <h2 class="section-title">{{ __('A guided') }} <span class="gradient-text">{{ __('3-step') }}</span> {{ __('AI onboarding.') }}</h2>
                <p class="section-subtitle">{{ __('Create your AI assistant in minutes and customize it with your business knowledge, documents, FAQs, and workflows for smarter automated conversations.') }}</p>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="step-card-lg">
                        <div class="step-lg-number gradient-text">01</div>
                        <h3 class="step-lg-title">{{ __('Create Your Workspace') }}</h3>
                        <p class="step-lg-desc">{{ __('Sign up in minutes, invite your team, and choose a plan that matches your usage — monthly or yearly.') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card-lg">
                        <div class="step-lg-number gradient-text">02</div>
                        <h3 class="step-lg-title">{{ __('Train Your AI Assistant') }}</h3>
                        <p class="step-lg-desc">{{ __('Upload documents, paste website URLs, add FAQs and define your brand voice. Our AI onboarding system guides you step-by-step.') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card-lg">
                        <div class="step-lg-number gradient-text">03</div>
                        <h3 class="step-lg-title">{{ __('Deploy & Automate') }}</h3>
                        <p class="step-lg-desc">{{ __('Embed on your website with a single snippet, connect to WhatsApp or APIs, and let your AI assistant handle conversations 24/7.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="industries-section section-padding">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="industries-image-box">
                        <img src="{{ $teamImage }}" alt="Nexbuddy team">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="industries-content p-0">
                        <div class="industries-eyebrow">{{ __('BUILT FOR STARTUPS, AGENCIES & ENTERPRISES') }}</div>
                        <h2 class="industries-title">{{ __('One platform.') }} <span class="gradient-text">{{ __('Every industry.') }}</span></h2>
                        <div class="row g-2 industry-grid">
                            @foreach ([
                                __('Healthcare & Clinics'),
                                __('Real Estate'),
                                __('D2C & E-commerce'),
                                __('SaaS & B2B'),
                                __('Education & Coaching'),
                                __('BFSI'),
                                __('Travel & Hospitality'),
                                __('Agencies'),
                            ] as $industry)
                                <div class="col-12 col-md-6"><div class="industry-card">{{ $industry }}</div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @includeWhen(($fSectSettings->pricing_active ?? 0) == 1, 'landing-page.pricing.section')

    <section class="faq-section section-padding" id="faq">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label tools-label-pink">{{ __('FAQ') }}</p>
                <h2 class="faq-title">{{ __('Frequently asked questions') }}</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion custom-accordion" id="faqAccordion">
                        @php
                            $faqItems = collect([
                                (object) ['question' => __('How does AI assistant training work?'), 'answer' => __('You upload your business documents, FAQs, product catalogs or website URLs. Our onboarding system processes this content, indexes it, and tunes the assistant to your brand voice. Most assistants are ready to test within minutes and reach production-quality after a short training and review cycle.')],
                                (object) ['question' => __('Can I upload my business documents?'), 'answer' => __('Yes. PDFs, DOCX, TXT, sitemaps, FAQs and direct URLs are all supported. You can also re-train at any time as your knowledge base evolves.')],
                                (object) ['question' => __('What are AI credits?'), 'answer' => __('Credits are a flexible usage unit. Each AI action — a chat message, a generated article, an image — consumes credits based on its complexity. This means you only pay for what you use instead of fixed message caps.')],
                                (object) ['question' => __('Is coding knowledge required?'), 'answer' => __('No. The entire platform is no-code. You can train, configure, and deploy your AI assistant using our visual interface. Developers can optionally use the API for deeper integrations.')],
                                (object) ['question' => __('Can I integrate AI on my website?'), 'answer' => __('Yes. Copy a single embed snippet to add the assistant to any website. WhatsApp Business, CRM and API integrations are available on Growth, Business and Enterprise plans.')],
                                (object) ['question' => __('Is my business data secure?'), 'answer' => __('All data is encrypted in transit (TLS) and at rest (AES-256), isolated per workspace, and never used to train shared models. We are GDPR-aligned and offer DPAs and data residency on Business and Enterprise plans.')],
                                (object) ['question' => __('Can teams collaborate?'), 'answer' => __('Yes. Invite teammates, assign roles, and share assistants, knowledge bases and templates across the workspace.')],
                                (object) ['question' => __('How quickly can I deploy my AI assistant?'), 'answer' => __('You can launch a basic assistant in minutes. A fully trained, production-ready assistant typically goes live within a few days — depending on the volume of training material and the integrations required.')],
                            ]);
                        @endphp
                        @foreach ($faqItems as $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-{{ $loop->iteration }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-{{ $loop->iteration }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="faq-content-{{ $loop->iteration }}">
                                        {{ __($item->question) }}
                                    </button>
                                </h2>
                                <div id="faq-content-{{ $loop->iteration }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="faq-heading-{{ $loop->iteration }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{!! __($item->answer) !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section section-padding">
        <div class="container">
            <div class="cta-box text-center">
                <h2 class="cta-title">{{ __('Launch your AI-first business — today.') }}</h2>
                <p class="cta-subtitle">{{ __('Join the businesses building intelligent assistants and automating workflows with') }} <br>{{ __('Nexbuddy.') }}</p>
                <div class="cta-buttons">
                    <a href="{{ $accountUrl }}" class="btn btn-gradient btn-lg">{{ $trialLabel }}</a>
                    <a href="#pricing" class="btn btn-outline-light btn-lg">{{ __('See Plans') }}</a>
                </div>
            </div>
        </div>
    </section>

    @includeWhen(($fSectSettings->blog_active ?? 0) == 1, 'landing-page.blog.section')
    @includeWhen(($setting->gdpr_status ?? 0) == 1, 'landing-page.gdpr')
@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ custom_theme_url('assets/css/frontend/nexbuddy-home.css') }}">
@endpush

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endpush
