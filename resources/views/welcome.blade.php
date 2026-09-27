<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Campus Coin — Student Cashbook & Financial Ledger</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .landing-page {
            --landing-max: 1180px;
            background: var(--paper);
            overflow-x: hidden;
        }

        .landing-page * {
            border-radius: 0 !important;
        }

        .landing-header {
            position: fixed;
            inset: 0 0 auto;
            z-index: 40;
            padding: 24px 48px;
            background: color-mix(in srgb, var(--paper) 92%, transparent);
            border-bottom: 1px solid transparent;
            transition: padding 180ms ease, background-color 180ms ease, border-color 180ms ease;
        }

        .landing-header.scrolled {
            padding-block: 14px;
            background: color-mix(in srgb, var(--paper) 96%, transparent);
            border-color: var(--hairline);
        }

        .landing-header-inner,
        .landing-section-inner {
            width: min(var(--landing-max), calc(100% - 48px));
            margin-inline: auto;
        }

        .landing-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--ink);
            font-family: 'Fraunces', serif;
            font-size: 19px;
            text-decoration: none;
        }

        .landing-logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 27px;
            height: 27px;
            border: 1px solid var(--accent);
            color: var(--accent);
            font-size: 13px;
        }

        .landing-nav a {
            color: var(--ink);
            font-size: 13px;
            text-decoration: none;
            transition: color 150ms ease;
        }

        .landing-nav a:hover {
            color: var(--accent);
        }

        .landing-hero {
            min-height: 100svh;
            display: flex;
            align-items: center;
            padding: 112px 24px 64px;
            position: relative;
        }

        .landing-hero-inner {
            width: min(var(--landing-max), calc(100% - 48px));
            margin-inline: auto;
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(0, 0.96fr);
            gap: 56px;
            align-items: center;
        }

        .landing-hero-content {
            text-align: left;
        }

        .landing-eyebrow,
        .landing-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
            font-variant: small-caps;
            letter-spacing: 0.6px !important;
        }

        .landing-hero-title {
            max-width: 660px;
            margin: 16px 0 0;
            color: var(--ink);
            font-family: 'Fraunces', serif;
            font-size: clamp(3rem, 5.1vw, 4.85rem);
            font-weight: 400;
            letter-spacing: -1.2px !important;
            line-height: 1.0;
        }

        .landing-hero-title em {
            color: var(--accent);
            font-style: italic;
        }

        .landing-rule {
            width: 52px;
            height: 2px;
            margin: 24px 0;
            background: var(--accent);
        }

        .landing-hero-copy {
            max-width: 500px;
            margin: 0 0 32px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }

        .landing-cta-row {
            display: flex;
            justify-content: flex-start;
            gap: 14px;
            flex-wrap: wrap;
        }

        .landing-btn {
            padding: 13px 26px !important;
            font-size: 13.5px !important;
            letter-spacing: 0.3px !important;
        }

        /* Right column: Hero Visual */
        .landing-hero-visual {
            width: 100%;
            position: relative;
        }

        .landing-hero-frame {
            position: relative;
            background: var(--panel);
            border: 1px solid var(--hairline);
            box-shadow: 0 20px 48px -24px rgba(21, 20, 15, 0.18);
            overflow: hidden;
            transition: border-color 200ms ease, box-shadow 200ms ease;
        }

        .dark .landing-hero-frame {
            box-shadow: 0 24px 56px -20px rgba(0, 0, 0, 0.65);
        }

        .landing-hero-img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: var(--bg-subtle);
        }

        .landing-hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .dark .landing-hero-img {
            filter: brightness(0.92) contrast(1.02);
        }

        .landing-hero-frame-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            border-top: 1px solid var(--hairline);
            background: var(--panel);
            font-size: 11.5px;
            line-height: 1.2;
        }

        .landing-hero-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 9999px !important;
            display: inline-block;
            flex-shrink: 0;
        }

        .landing-hero-frame-label {
            color: var(--muted);
            font-variant: small-caps;
            letter-spacing: 0.5px !important;
            font-size: 11px;
            white-space: nowrap;
        }

        .landing-hero-frame-stat {
            color: var(--accent);
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 500;
            font-size: 12px;
            white-space: nowrap;
        }

        .landing-ledger-track {
            height: 105vh;
            margin-top: 0;
            position: relative;
            pointer-events: none;
        }

        .landing-ledger-sticky {
            position: sticky;
            bottom: 4vh;
            display: flex;
            justify-content: center;
        }

        .landing-ledger {
            width: 14vw;
            min-width: 150px;
            will-change: width;
        }

        .landing-ledger svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .landing-section {
            padding: 64px 0 100px;
        }

        .landing-section-head {
            margin-bottom: 64px;
            text-align: center;
        }

        .landing-section-head h2,
        .landing-proof h2 {
            margin: 0 0 14px;
            color: var(--ink);
            font-family: 'Fraunces', serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 500;
        }

        .landing-ideas {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border: 1px solid var(--hairline);
        }

        .landing-idea {
            min-height: 260px;
            padding: 48px 44px;
            background: var(--panel);
        }

        .landing-idea + .landing-idea {
            border-left: 1px solid var(--hairline);
        }

        .landing-idea-art {
            height: 45px;
            margin-bottom: 22px;
        }

        .landing-idea h3 {
            margin: 0 0 16px;
            color: var(--ink);
            font-family: 'Fraunces', serif;
            font-size: 24px;
            font-weight: 500;
        }

        .landing-idea-copy {
            min-height: 96px;
            color: var(--ink);
            font-size: 14.5px;
            line-height: 1.7;
        }

        .landing-cursor {
            display: inline-block;
            width: 2px;
            height: 1em;
            margin-left: 2px;
            vertical-align: -0.15em;
            background: var(--accent);
            animation: landing-blink 0.9s steps(1) infinite;
        }

        @keyframes landing-blink {
            50% { opacity: 0; }
        }

        .landing-proof {
            padding: 80px 0;
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
            text-align: center;
        }

        .landing-proof-copy {
            margin: 0 0 56px;
            color: var(--muted);
            font-size: 14.5px;
        }

        .landing-stat-row {
            display: flex;
            margin-bottom: 56px;
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
        }

        .landing-stat {
            flex: 1;
            padding: 28px 16px;
        }

        .landing-stat + .landing-stat {
            border-left: 1px solid var(--hairline);
        }

        .landing-stat-number {
            color: var(--accent);
            font-family: 'IBM Plex Mono', monospace;
            font-size: 32px;
            font-weight: 600;
        }

        .landing-stat-label {
            margin-top: 8px;
            color: var(--muted);
            font-size: 12px;
            font-variant: small-caps;
            letter-spacing: 0.5px !important;
        }

        .landing-footer {
            min-height: 65vh;
            display: flex;
            flex-direction: column;
            background: var(--ink);
            color: var(--paper);
        }

        .landing-footer-links {
            display: flex;
            justify-content: center;
            gap: 96px;
            padding: 64px 48px 0;
            flex-wrap: wrap;
        }

        .landing-footer-column h3 {
            margin: 0 0 18px;
            color: var(--secondary);
            font-size: 11px;
            font-weight: 500;
            font-variant: small-caps;
            letter-spacing: 0.5px !important;
        }

        .landing-footer-column a {
            display: block;
            margin-bottom: 12px;
            color: var(--paper);
            font-size: 13.5px;
            opacity: 0.75;
            text-decoration: none;
            transition: opacity 150ms ease, color 150ms ease;
        }

        .landing-footer-column a:hover {
            color: var(--secondary);
            opacity: 1;
        }

        .landing-footer-brand {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            text-align: center;
        }

        .landing-footer-brand h2 {
            margin: 0;
            color: var(--paper);
            font-family: 'Fraunces', serif;
            font-size: clamp(3.5rem, 13vw, 10rem);
            font-weight: 600;
            letter-spacing: -2px !important;
            line-height: 0.9;
        }

        .landing-footer-meta {
            margin-top: 34px;
            color: var(--secondary);
            font-size: 12px;
            font-variant: small-caps;
            letter-spacing: 0.5px !important;
        }

        .landing-footer-bottom {
            margin: 0 48px;
            border-top: 1px solid var(--hairline);
            opacity: 0.3;
        }

        .landing-footer-copyright {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 48px;
            color: var(--paper);
            font-size: 11px;
            opacity: 0.5;
        }

        @media (max-width: 760px) {
            .landing-header {
                padding: 18px 20px;
            }

            .landing-header-inner,
            .landing-section-inner,
            .landing-hero-inner {
                width: min(var(--landing-max), calc(100% - 40px));
            }

            .landing-nav {
                display: none;
            }

            .landing-hero {
                min-height: auto;
                padding-top: 110px;
                padding-bottom: 48px;
                padding-inline: 20px;
            }

            .landing-hero-inner {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .landing-hero-title {
                font-size: clamp(2.5rem, 11vw, 3.75rem);
            }

            .landing-hero-copy {
                font-size: 15px;
            }

            .landing-cta-row {
                flex-direction: column;
                align-items: stretch;
            }

            .landing-cta-row .landing-btn {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .landing-ledger-track {
                height: 78vh;
            }

            .landing-ledger {
                min-width: 260px;
            }

            .landing-section {
                padding: 52px 0 72px;
            }

            .landing-section-head {
                margin-bottom: 40px;
            }

            .landing-ideas {
                grid-template-columns: 1fr;
            }

            .landing-idea {
                min-height: 0;
                padding: 36px 28px;
            }

            .landing-idea + .landing-idea {
                border-top: 1px solid var(--hairline);
                border-left: 0;
            }

            .landing-idea-copy {
                min-height: 0;
            }

            .landing-proof {
                padding: 64px 0;
            }

            .landing-stat-row {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
            }

            .landing-stat + .landing-stat {
                border-left: 0;
            }

            .landing-stat:nth-child(odd) {
                border-right: 1px solid var(--hairline);
            }

            .landing-stat:nth-child(-n + 2) {
                border-bottom: 1px solid var(--hairline);
            }

            .landing-footer-links {
                justify-content: flex-start;
                gap: 42px;
                padding: 48px 24px 0;
            }

            .landing-footer-brand h2 {
                font-size: clamp(3.2rem, 16vw, 6rem);
            }

            .landing-footer-bottom {
                margin-inline: 24px;
            }

            .landing-footer-copyright {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px 24px 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .landing-header,
            .landing-cursor {
                transition: none;
                animation: none;
            }
        }
    </style>
</head>
<body class="landing-page min-h-full font-sans antialiased">
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    <header class="landing-header" data-landing-header>
        <div class="landing-header-inner flex items-center justify-between">
            <a href="{{ url('/') }}" class="landing-logo" aria-label="Campus Coin Home">
                <span class="landing-logo-mark" aria-hidden="true">C</span>
                <span>CampusCoin</span>
            </a>

            <div class="flex items-center gap-4">
                <nav class="landing-nav hidden sm:flex items-center gap-8" aria-label="Landing page navigation">
                    <a href="#features">Features</a>
                    <a href="#architecture">Architecture</a>
                    <a href="#sitemap">Sitemap</a>
                </nav>

                <button type="button"
                        onclick="window.CampusCoin ? window.CampusCoin.toggleTheme() : (document.documentElement.classList.toggle('dark'), localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light'))"
                        aria-label="Toggle dark mode"
                        title="Toggle dark mode"
                        class="btn-icon">
                    <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                    <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                </button>

                @auth
                    <x-button variant="secondary" href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="hidden sm:inline-flex landing-btn">
                        {{ Auth::user()->isAdmin() ? 'Admin Console' : 'My Ledger' }}
                    </x-button>
                @else
                    <x-button variant="secondary" href="{{ route('login') }}" class="hidden sm:inline-flex landing-btn">Sign In</x-button>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="focus:outline-none">
        <section class="landing-hero">
            <div class="landing-hero-inner">
                <div class="landing-hero-content">
                    <div class="landing-eyebrow">Full-stack student expense ledger</div>
                    <h1 class="landing-hero-title">Smart spending,<br><em>student</em> style.</h1>
                    <div class="landing-rule" aria-hidden="true"></div>
                    <p class="landing-hero-copy">A precision ledger for campus life: deterministic budgets, a real cash-flow record, and advisory insight without banking complexity.</p>

                    <div class="landing-cta-row">
                        @auth
                            <x-button variant="accent" href="{{ route('dashboard') }}" class="landing-btn">Open Dashboard</x-button>
                        @else
                            <x-button variant="accent" href="{{ route('register') }}" class="landing-btn">Setup Student Profile</x-button>
                            <x-button variant="primary" href="{{ route('login') }}" class="landing-btn">Student Login</x-button>
                        @endauth
                    </div>
                </div>

                <div class="landing-hero-visual">
                    <div class="landing-hero-frame">
                        <div class="landing-hero-img-wrap">
                            <img src="{{ asset('images/hero-student-finance.jpg') }}"
                                 alt="University student managing finances and budgeting on CampusCoin ledger"
                                 width="1200"
                                 height="900"
                                 class="landing-hero-img"
                                 loading="eager"
                                 fetchpriority="high">
                        </div>
                        <div class="landing-hero-frame-footer">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="landing-hero-dot" aria-hidden="true"></span>
                                <span class="landing-hero-frame-label truncate">Student Cashbook &middot; Live Record</span>
                            </div>
                            <span class="landing-hero-frame-stat">
                                <span class="hidden sm:inline text-[var(--muted)] font-sans text-[11px] font-normal mr-1">SAFE TO SPEND &middot;</span>$1,092.50
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="landing-ledger-track" data-ledger-track aria-hidden="true">
            <div class="landing-ledger-sticky">
                <div class="landing-ledger" data-ledger-art>
                    <svg viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg" role="presentation">
                        <ellipse cx="450" cy="562" rx="360" ry="16" fill="#221F19" opacity="0.06"/>
                        <rect x="60" y="40" width="780" height="510" fill="var(--panel)" stroke="var(--hairline)" stroke-width="1.5"/>
                        <polygon points="800,510 840,510 840,550" fill="var(--paper)" stroke="var(--hairline)" stroke-width="1"/>
                        <line x1="450" y1="40" x2="450" y2="550" stroke="var(--hairline)" stroke-width="1"/>
                        <g stroke="var(--hairline)" stroke-width="1" opacity="0.65">
                            <line x1="96" y1="120" x2="410" y2="120"/>
                            <line x1="96" y1="158" x2="410" y2="158"/>
                            <line x1="96" y1="196" x2="410" y2="196"/>
                            <line x1="96" y1="234" x2="410" y2="234"/>
                            <line x1="96" y1="272" x2="410" y2="272"/>
                        </g>
                        <polyline points="96,470 175,440 254,458 333,400 410,420" fill="none" stroke="var(--accent)" stroke-width="3"/>
                        <circle cx="175" cy="440" r="5" fill="var(--secondary)"/>
                        <circle cx="333" cy="400" r="5" fill="var(--secondary)"/>
                        <circle cx="410" cy="420" r="5" fill="var(--accent)"/>
                        <text x="96" y="88" fill="var(--muted)" font-family="IBM Plex Sans, sans-serif" font-size="15" letter-spacing="2">LEDGER — SEPTEMBER 2026</text>
                        <text x="96" y="530" fill="var(--muted)" font-family="IBM Plex Sans, sans-serif" font-size="12" letter-spacing="1.5">CASH FLOW, 6 MONTHS</text>
                        <text x="500" y="150" fill="var(--muted)" font-family="IBM Plex Sans, sans-serif" font-size="14" letter-spacing="2">SAFE TO SPEND</text>
                        <text x="497" y="228" fill="var(--accent)" font-family="Fraunces, serif" font-size="66" font-weight="500">$1,092.50</text>
                        <line x1="500" y1="250" x2="560" y2="250" stroke="var(--secondary)" stroke-width="2"/>
                        <text x="500" y="290" fill="var(--ink)" font-family="IBM Plex Mono, monospace" font-size="14">of $1,200.00 allowance</text>
                        <g>
                            <circle cx="742" cy="112" r="42" fill="var(--panel)" stroke="var(--secondary)" stroke-width="1.5"/>
                            <circle cx="742" cy="112" r="32" fill="none" stroke="var(--accent)" stroke-width="1.5"/>
                            <text x="742" y="124" fill="var(--accent)" font-family="Fraunces, serif" font-size="30" text-anchor="middle">C</text>
                        </g>
                        <text x="500" y="470" fill="var(--muted)" font-family="IBM Plex Sans, sans-serif" font-size="12" letter-spacing="1.5">ENTRIES LOGGED</text>
                        <text x="500" y="500" fill="var(--ink)" font-family="IBM Plex Mono, monospace" font-size="26">4</text>
                    </svg>
                </div>
            </div>
        </div>

        <section id="features" class="landing-section">
            <div class="landing-section-inner">
                <div class="landing-section-head">
                    <h2>Built on two ideas.</h2>
                </div>

                <div class="landing-ideas">
                    <article class="landing-idea" data-landing-type>
                        <div class="landing-idea-art" aria-hidden="true">
                            <svg viewBox="0 0 96 72" width="60" height="45" xmlns="http://www.w3.org/2000/svg">
                                <line x1="4" y1="16" x2="92" y2="16" stroke="var(--hairline)" stroke-width="1.5" stroke-dasharray="3 3"/>
                                <rect x="12" y="34" width="15" height="30" fill="none" stroke="var(--accent)" stroke-width="1.5"/>
                                <rect x="41" y="20" width="15" height="44" fill="none" stroke="var(--expense)" stroke-width="1.5"/>
                                <rect x="70" y="42" width="15" height="22" fill="none" stroke="var(--accent)" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <span class="landing-label">Deterministic budgeting</span>
                        <h3>Every rupee, accounted for.</h3>
                        <div class="landing-idea-copy" data-full="No floating-point guessing. Category caps, 75% warnings, and 100% danger alerts calculated in exact decimals — the same number every time you check it.">No floating-point guessing. Category caps, 75% warnings, and 100% danger alerts calculated in exact decimals — the same number every time you check it.</div>
                    </article>

                    <article class="landing-idea" data-landing-type>
                        <div class="landing-idea-art" aria-hidden="true">
                            <svg viewBox="0 0 96 72" width="60" height="45" xmlns="http://www.w3.org/2000/svg">
                                <line x1="10" y1="18" x2="70" y2="18" stroke="var(--hairline)" stroke-width="1.5"/>
                                <line x1="10" y1="34" x2="70" y2="34" stroke="var(--hairline)" stroke-width="1.5"/>
                                <line x1="10" y1="50" x2="50" y2="50" stroke="var(--hairline)" stroke-width="1.5"/>
                                <circle cx="58" cy="34" r="15" fill="none" stroke="var(--accent)" stroke-width="1.5"/>
                                <line x1="69" y1="45" x2="84" y2="60" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="square"/>
                            </svg>
                        </div>
                        <span class="landing-label">Advisory intelligence</span>
                        <h3>Insight, not intrusion.</h3>
                        <div class="landing-idea-copy" data-full="Smart categorization and saving suggestions stay advisory. The ledger proposes, you decide — override authority always stays with the student.">Smart categorization and saving suggestions stay advisory. The ledger proposes, you decide — override authority always stays with the student.</div>
                    </article>
                </div>
            </div>
        </section>

        <section id="architecture" class="landing-proof">
            <div class="landing-section-inner">
                <h2>Built like a ledger, not a guess.</h2>
                <p class="landing-proof-copy">Every figure in CampusCoin traces back to something exact: no rounding, no estimates.</p>

                <div class="landing-stat-row">
                    <div class="landing-stat"><div class="landing-stat-number">0</div><div class="landing-stat-label">Floating-point errors</div></div>
                    <div class="landing-stat"><div class="landing-stat-number">75%</div><div class="landing-stat-label">Warning threshold</div></div>
                    <div class="landing-stat"><div class="landing-stat-number">12</div><div class="landing-stat-label">Categories, out of the box</div></div>
                    <div class="landing-stat"><div class="landing-stat-number">24/7</div><div class="landing-stat-label">Ledger access</div></div>
                </div>

                <div class="landing-cta-row">
                    @auth
                        <x-button variant="accent" href="{{ route('dashboard') }}" class="landing-btn">Open Your Ledger</x-button>
                    @else
                        <x-button variant="accent" href="{{ route('register') }}" class="landing-btn">Set Up Your Ledger</x-button>
                        <x-button variant="primary" href="{{ route('login') }}" class="landing-btn">Student Login</x-button>
                    @endauth
                </div>
            </div>
        </section>

        <section id="sitemap" class="landing-section pb-16">
            <div class="landing-section-inner">
                <div class="landing-section-head mb-10">
                    <span class="landing-label">Application routes</span>
                    <h2>Application Sitemap</h2>
                </div>
                <div class="grid grid-cols-1 gap-0 border border-[var(--hairline)] md:grid-cols-4">
                    <div class="bg-[var(--panel)] p-6">
                        <div class="landing-label text-[var(--accent)]">Public & Access</div>
                        <div class="mt-4 space-y-2 text-xs text-[var(--muted)] font-mono">
                            <a class="block hover:text-[var(--ink)]" href="{{ route('login') }}">/login — Student Login</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ route('register') }}">/register — Profile Setup</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ route('admin.login') }}">/admin/login — Staff Console</a>
                        </div>
                    </div>
                    <div class="border-t border-[var(--hairline)] bg-[var(--panel)] p-6 md:border-l md:border-t-0">
                        <div class="landing-label text-[var(--accent)]">Student Ledger</div>
                        <div class="mt-4 space-y-2 text-xs text-[var(--muted)] font-mono">
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/dashboard') }}">/dashboard — Main Cashbook</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/transactions') }}">/transactions — Ledger Entries</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/budgets') }}">/budgets — Spending Caps</a>
                        </div>
                    </div>
                    <div class="border-t border-[var(--hairline)] bg-[var(--panel)] p-6 md:border-l md:border-t-0">
                        <div class="landing-label text-[var(--accent)]">Analysis</div>
                        <div class="mt-4 space-y-2 text-xs text-[var(--muted)] font-mono">
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/reports') }}">Financial Reports</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/tips') }}">Saving Tips</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/categories') }}">Categories</a>
                        </div>
                    </div>
                    <div class="border-t border-[var(--hairline)] bg-[var(--panel)] p-6 md:border-l md:border-t-0">
                        <div class="landing-label text-[var(--secondary)]">Staff Operations</div>
                        <div class="mt-4 space-y-2 text-xs text-[var(--muted)] font-mono">
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/admin/dashboard') }}">Console Overview</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/admin/users') }}">Student Accounts</a>
                            <a class="block hover:text-[var(--ink)]" href="{{ url('/admin/categories') }}">System Categories</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-footer-links">
            <div class="landing-footer-column">
                <h3>Product</h3>
                <a href="#features">Features</a>
                <a href="#architecture">Architecture</a>
                <a href="#sitemap">Sitemap</a>
            </div>
            <div class="landing-footer-column">
                <h3>Access</h3>
                <a href="{{ route('login') }}">Student Login</a>
                <a href="{{ route('register') }}">Register</a>
                <a href="{{ route('admin.login') }}">Staff Console</a>
            </div>
            <div class="landing-footer-column">
                <h3>Ledger</h3>
                <a href="{{ url('/budgets') }}">Budget Goals</a>
                <a href="{{ url('/reports') }}">Reports</a>
                <a href="{{ url('/tips') }}">Saving Tips</a>
            </div>
        </div>

        <div class="landing-footer-brand">
            <h2>CAMPUSCOIN</h2>
            <div class="landing-footer-meta">Student Ledger &amp; Budget Suite</div>
        </div>

        <div class="landing-footer-bottom"></div>
        <div class="landing-footer-copyright">
            <span>CampusCoin &copy; {{ date('Y') }}</span>
            <span class="font-mono">PHP 8.4 &middot; Laravel 12 &middot; Livewire 3</span>
        </div>
    </footer>

    <script>
        (function () {
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const header = document.querySelector('[data-landing-header]');
            const track = document.querySelector('[data-ledger-track]');
            const ledger = document.querySelector('[data-ledger-art]');

            const updateLandingScroll = () => {
                if (header) {
                    header.classList.toggle('scrolled', window.scrollY > 24);
                }

                if (!track || !ledger) {
                    return;
                }

                const rect = track.getBoundingClientRect();
                const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (rect.height * 0.85)));
                ledger.style.width = (reducedMotion ? 95 : 14 + progress * 81) + 'vw';
            };

            document.addEventListener('scroll', () => requestAnimationFrame(updateLandingScroll), { passive: true });
            updateLandingScroll();

            const typeInto = (element, text) => {
                if (reducedMotion) {
                    element.textContent = text;
                    return;
                }

                let index = 0;
                const cursor = document.createElement('span');
                cursor.className = 'landing-cursor';

                const step = () => {
                    element.textContent = text.slice(0, index);
                    element.appendChild(cursor);
                    index += 1;

                    if (index <= text.length) {
                        window.setTimeout(step, 16);
                    } else {
                        cursor.remove();
                    }
                };

                step();
            };

            const typeTargets = document.querySelectorAll('[data-landing-type] .landing-idea-copy');
            if ('IntersectionObserver' in window && !reducedMotion) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting || entry.target.dataset.typed) {
                            return;
                        }

                        entry.target.dataset.typed = '1';
                        typeInto(entry.target, entry.target.dataset.full);
                        observer.unobserve(entry.target);
                    });
                }, { threshold: 0.35 });

                typeTargets.forEach((target) => observer.observe(target));
            } else {
                typeTargets.forEach((target) => typeInto(target, target.dataset.full));
            }
        })();
    </script>
</body>
</html>
