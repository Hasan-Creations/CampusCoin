<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Coin — Smart Spending, Student Style</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Immediate Theme & Font-Size Boot Script (Prevents FOUC & Text Shifts) -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                const fontSize = localStorage.getItem('font-size') || 'normal';
                document.documentElement.setAttribute('data-font-size', fontSize);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <!-- Skip to Main Content Link for Keyboard & Screen Reader Users -->
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <!-- Navigation Bar -->
    <header class="w-full border-b hairline-border bg-[var(--bg-surface)] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-3.5 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group" aria-label="Campus Coin Home">
                <div class="w-9 h-9 rounded-[8px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-sm tracking-tight shadow-tactile-sm transition-transform group-hover:scale-105" aria-hidden="true">
                    CC
                </div>
                <span class="font-heading font-bold text-lg text-[var(--text-primary)] tracking-tight">Campus<span class="text-[var(--accent-primary)]">Coin</span></span>
            </a>

            <div class="flex items-center gap-3 text-xs font-medium">
                <a href="#features" class="hidden sm:inline-block text-[var(--text-muted)] hover:text-[var(--text-primary)] px-2 py-1 transition-colors">Features</a>
                <a href="#architecture" class="hidden sm:inline-block text-[var(--text-muted)] hover:text-[var(--text-primary)] px-2 py-1 transition-colors">Architecture</a>
                <a href="#sitemap" class="text-[var(--text-muted)] hover:text-[var(--text-primary)] px-2 py-1 transition-colors">Sitemap</a>

                <!-- Font-Size Scaling Control (SRS §1.6 & §185) -->
                <div class="relative" x-data="{
                    open: false,
                    fontSize: localStorage.getItem('font-size') || 'normal',
                    setFontSize(size) {
                        this.fontSize = size;
                        if (window.CampusCoin && window.CampusCoin.setFontSize) {
                            window.CampusCoin.setFontSize(size);
                        } else {
                            localStorage.setItem('font-size', size);
                            document.documentElement.setAttribute('data-font-size', size);
                        }
                        this.open = false;
                    }
                }">
                    <button type="button"
                            @click="open = !open"
                            @keydown.escape="open = false"
                            aria-haspopup="true"
                            :aria-expanded="open"
                            aria-label="Adjust text scaling size"
                            title="Adjust text scaling"
                            class="btn-icon text-xs font-mono gap-1 w-auto px-2.5">
                        <span class="font-bold text-xs" aria-hidden="true">aA</span>
                        <x-icon name="sliders" class="w-3 h-3" />
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-cloak
                         role="menu"
                         aria-label="Text size options"
                         class="absolute right-0 mt-2 w-44 rounded-[16px] border hairline-border bg-[var(--bg-surface)] shadow-modal p-1.5 z-50 text-xs font-mono">
                        <button type="button"
                                role="menuitem"
                                @click="setFontSize('normal')"
                                class="w-full text-left px-3 py-2 rounded-[8px] flex items-center justify-between transition-colors"
                                :class="fontSize === 'normal' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                            <span>Normal (100%)</span>
                            <span x-show="fontSize === 'normal'" aria-hidden="true">✓</span>
                        </button>
                        <button type="button"
                                role="menuitem"
                                @click="setFontSize('large')"
                                class="w-full text-left px-3 py-2 rounded-[8px] flex items-center justify-between transition-colors"
                                :class="fontSize === 'large' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                            <span>Large (112.5%)</span>
                            <span x-show="fontSize === 'large'" aria-hidden="true">✓</span>
                        </button>
                        <button type="button"
                                role="menuitem"
                                @click="setFontSize('xlarge')"
                                class="w-full text-left px-3 py-2 rounded-[8px] flex items-center justify-between transition-colors"
                                :class="fontSize === 'xlarge' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                            <span>X-Large (125%)</span>
                            <span x-show="fontSize === 'xlarge'" aria-hidden="true">✓</span>
                        </button>
                    </div>
                </div>

                <button type="button" 
                        onclick="window.CampusCoin ? window.CampusCoin.toggleTheme() : (document.documentElement.classList.toggle('dark'), localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light'))"
                        aria-label="Toggle dark mode"
                        title="Toggle dark mode"
                        class="btn-icon">
                    <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                    <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                </button>

                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-secondary py-1.5 px-3.5 text-xs">Admin Console</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-primary py-1.5 px-3.5 text-xs">My Ledger</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-secondary py-1.5 px-3.5 text-xs">Sign In</a>
                    <a href="{{ route('register') }}" class="btn-primary py-1.5 px-3.5 text-xs">Get Started</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="focus:outline-none">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 py-16 sm:py-24 border-b hairline-border">
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-[6px] border hairline-border bg-[var(--bg-subtle)] text-xs font-mono font-semibold text-[var(--accent-primary)] shadow-tactile-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent-primary)]"></span>
                FULL-STACK STUDENT EXPENSE LEDGER & BUDGET SUITE
            </div>

            <h1 class="font-heading text-4xl sm:text-6xl font-bold tracking-tight text-[var(--text-primary)] leading-[1.08]">
                Smart Spending, <br>
                <span class="text-[var(--accent-primary)]">Student Style.</span>
            </h1>

            <p class="text-base sm:text-lg text-[var(--text-muted)] leading-relaxed max-w-2xl">
                The authoritative collegiate financial platform. High-density ledger tracking, deterministic budget caps, safe-to-spend intelligence, and advisory insights without banking complexity.
            </p>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary py-3 px-6 text-sm">
                        <span>Open Dashboard</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary py-3 px-6 text-sm">
                        <span>Setup Student Profile</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary py-3 px-6 text-sm">
                        <span>Student Login</span>
                    </a>
                    <a href="{{ route('admin.login') }}" class="btn-secondary py-3 px-4 text-sm font-mono text-[var(--gold)]">
                        <span>Staff Console</span>
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Key Principles & Features Grid -->
    <section id="features" class="max-w-7xl mx-auto px-6 py-16 border-b hairline-border">
        <div class="mb-10">
            <h2 class="font-heading text-2xl font-bold text-[var(--text-primary)]">System Pillars</h2>
            <p class="text-sm text-[var(--text-muted)] mt-1">Built to the rigorous standards of the Campus Coin SRS & UI/UX specifications</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card-campus border hairline-border p-6 space-y-3 hover:-translate-y-1 transition-all duration-200">
                <div class="w-10 h-10 rounded-[8px] bg-[var(--accent-tint)] text-[var(--accent-primary)] flex items-center justify-center shadow-tactile-sm">
                    <x-icon name="target" class="w-5 h-5" />
                </div>
                <h3 class="font-heading text-lg font-bold text-[var(--text-primary)]">Deterministic Budgeting</h3>
                <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                    Set monthly caps across academic, food, hostel, and personal categories with strict 75% warning and 100% danger alerts.
                </p>
            </div>

            <div class="card-campus border hairline-border p-6 space-y-3 hover:-translate-y-1 transition-all duration-200">
                <div class="w-10 h-10 rounded-[8px] bg-[var(--bg-subtle)] text-[var(--text-primary)] flex items-center justify-center shadow-tactile-sm">
                    <x-icon name="wallet" class="w-5 h-5" />
                </div>
                <h3 class="font-heading text-lg font-bold text-[var(--text-primary)]">Precision Financial Ledger</h3>
                <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                    Zero floating-point arithmetic. Stored as exact decimals for dependable month-over-month reconciliation.
                </p>
            </div>

            <div class="card-campus border hairline-border p-6 space-y-3 hover:-translate-y-1 transition-all duration-200">
                <div class="w-10 h-10 rounded-[8px] bg-[var(--accent-tint)] text-[var(--accent-primary)] flex items-center justify-center shadow-tactile-sm">
                    <x-icon name="lightbulb" class="w-5 h-5" />
                </div>
                <h3 class="font-heading text-lg font-bold text-[var(--text-primary)]">Advisory AI Intelligence</h3>
                <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                    Smart categorization suggestions that stay advisory. You always maintain authoritative override authority.
                </p>
            </div>
        </div>
    </section>

    <!-- SRS Deliverable: Comprehensive Site Map Section -->
    <section id="sitemap" class="max-w-7xl mx-auto px-6 py-16">
        <div class="mb-8">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] bg-[var(--bg-subtle)] text-xs font-mono text-[var(--text-muted)] uppercase tracking-wider mb-2 font-semibold">
                SRS Deliverable §5.3
            </div>
            <h2 class="font-heading text-2xl font-bold text-[var(--text-primary)]">Application Sitemap</h2>
            <p class="text-xs text-[var(--text-muted)] mt-1">Live routing hierarchy reflecting the system's operational architecture</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6 rounded-[16px] border hairline-border bg-[var(--bg-surface)] shadow-tactile-sm">
            <!-- 1. Public & Onboarding -->
            <div class="space-y-3">
                <div class="text-xs font-mono uppercase tracking-wider font-semibold text-[var(--accent-primary)]">
                    1. Public & Access
                </div>
                <ul class="text-xs space-y-2 text-[var(--text-muted)]">
                    <li><a href="{{ url('/') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/ — Home & Sitemap</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/login — Student Sign In</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/register — Profile Setup</a></li>
                    <li><a href="{{ route('admin.login') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/admin/login — Staff Access</a></li>
                </ul>
            </div>

            <!-- 2. Student Financial Management -->
            <div class="space-y-3">
                <div class="text-xs font-mono uppercase tracking-wider font-semibold text-[var(--accent-primary)]">
                    2. Student Ledger
                </div>
                <ul class="text-xs space-y-2 text-[var(--text-muted)]">
                    <li><a href="{{ url('/dashboard') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/dashboard — 12-Col Dashboard</a></li>
                    <li><a href="{{ url('/transactions') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/transactions — Cash Flow Ledger</a></li>
                    <li><a href="{{ url('/budgets') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/budgets — Category Caps & Alerts</a></li>
                    <li><a href="{{ url('/categories') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/categories — Personal Tags</a></li>
                </ul>
            </div>

            <!-- 3. Intelligence & Reporting -->
            <div class="space-y-3">
                <div class="text-xs font-mono uppercase tracking-wider font-semibold text-[var(--accent-primary)]">
                    3. Analysis & Intelligence
                </div>
                <ul class="text-xs space-y-2 text-[var(--text-muted)]">
                    <li><a href="{{ url('/reports') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/reports — 6-Month Trends</a></li>
                    <li><a href="{{ url('/tips') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/tips — Rule-based Saving Tips</a></li>
                    <li><a href="{{ url('/bookmarks') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/bookmarks — Saved Insights</a></li>
                    <li><a href="{{ url('/import') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/import — CSV Statement Ingest</a></li>
                </ul>
            </div>

            <!-- 4. Operational Admin -->
            <div class="space-y-3">
                <div class="text-xs font-mono uppercase tracking-wider font-semibold text-[var(--gold)]">
                    4. Staff Operations
                </div>
                <ul class="text-xs space-y-2 text-[var(--text-muted)]">
                    <li><a href="{{ url('/admin/dashboard') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/admin/dashboard — Console</a></li>
                    <li><a href="{{ url('/admin/users') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/admin/users — Student Directory</a></li>
                    <li><a href="{{ url('/admin/categories') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/admin/categories — System Tags</a></li>
                    <li><a href="{{ url('/admin/system') }}" class="hover:text-[var(--text-primary)] font-mono transition-colors">/admin/system — Server Health</a></li>
                </ul>
            </div>
        </div>
    </section>
    </main>

    <!-- Footer -->
    <footer class="border-t hairline-border py-8 px-6 text-xs text-[var(--text-muted)] bg-[var(--bg-surface)]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>Campus Coin &copy; {{ date('Y') }} &bull; Precision Student Budgeting Platform.</div>
            <div class="flex items-center gap-6 font-mono text-[11px]">
                <span>PHP 8.4</span>
                <span>Laravel 12</span>
                <span>Livewire 3</span>
                <span>MySQL</span>
            </div>
        </div>
    </footer>
</body>
</html>
