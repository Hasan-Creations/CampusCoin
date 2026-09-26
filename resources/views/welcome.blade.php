<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Coin — Student Cashbook & Financial Ledger</title>

    <!-- Google Fonts per §2 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

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
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-[var(--ink)] bg-[var(--paper)]">
    <!-- Skip to Main Content Link for Keyboard & Screen Reader Users -->
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <!-- Top Masthead Bar -->
    <header class="w-full border-b hairline-border bg-[var(--panel)] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-3.5 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Campus Coin Home">
                <div class="w-8 h-8 bg-[var(--accent)] flex items-center justify-center text-[var(--paper)] font-display font-medium text-sm" aria-hidden="true">
                    CC
                </div>
                <span class="font-display font-medium text-lg text-[var(--ink)] tracking-tight">CampusCoin</span>
            </a>

            <div class="flex items-center gap-4 text-xs font-sans">
                <nav class="hidden sm:flex items-center gap-4 font-caps text-[var(--muted)]">
                    <a href="#features" class="hover:text-[var(--ink)] transition-colors">Features</a>
                    <a href="#architecture" class="hover:text-[var(--ink)] transition-colors">Architecture</a>
                    <a href="#sitemap" class="hover:text-[var(--ink)] transition-colors">Sitemap</a>
                </nav>

                <!-- Theme Toggle -->
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
                        <x-button variant="secondary" href="{{ route('admin.dashboard') }}">Admin Console</x-button>
                    @else
                        <x-button variant="accent" href="{{ route('dashboard') }}">My Ledger</x-button>
                    @endif
                @else
                    <x-button variant="secondary" href="{{ route('login') }}">Sign In</x-button>
                    <x-button variant="accent" href="{{ route('register') }}">Get Started</x-button>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="focus:outline-none">
        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-6 py-16 sm:py-24 border-b hairline-border">
            <div class="max-w-3xl space-y-6">
                <div>
                    <span class="text-xs font-caps text-[var(--muted)] tracking-wider">
                        Collegiate Expense Ledger & Budget Suite
                    </span>
                </div>

                <h1 class="font-display text-4xl sm:text-6xl font-normal tracking-tight text-[var(--ink)] leading-[1.1] headline-rule">
                    Smart Spending, <br>
                    Student Style.
                </h1>

                <p class="text-base sm:text-lg text-[var(--muted)] leading-relaxed max-w-2xl pt-2">
                    A physical student cashbook reimagined for collegiate finance. Reconcile monthly allowances, enforce category spending limits, and track savings goals with deterministic precision.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-4">
                    @auth
                        <x-button variant="accent" href="{{ route('dashboard') }}" class="py-3 px-6 text-sm">
                            <span>Open Dashboard</span>
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </x-button>
                    @else
                        <x-button variant="accent" href="{{ route('register') }}" class="py-3 px-6 text-sm">
                            <span>Setup Student Profile</span>
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </x-button>
                        <x-button variant="secondary" href="{{ route('login') }}" class="py-3 px-6 text-sm">
                            <span>Student Login</span>
                        </x-button>
                        <x-button variant="secondary" href="{{ route('admin.login') }}" class="py-3 px-4 text-sm font-mono text-[var(--secondary)]">
                            <span>Staff Console</span>
                        </x-button>
                    @endauth
                </div>
            </div>
        </section>

        <!-- System Pillars (§5: Clean hairline separation, no card border nesting) -->
        <section id="features" class="max-w-7xl mx-auto px-6 py-16 border-b hairline-border">
            <div class="mb-10">
                <h2 class="font-display text-2xl font-medium text-[var(--ink)] headline-rule">System Pillars</h2>
                <p class="text-xs text-[var(--muted)] mt-1">Built to the rigorous standards of the Campus Coin SRS & design system</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 border hairline-border bg-[var(--panel)] divide-y md:divide-y-0 md:divide-x divide-[var(--hairline)]">
                <div class="p-8 space-y-3">
                    <div class="w-8 h-8 bg-[var(--paper)] border hairline-border flex items-center justify-center text-[var(--accent)]">
                        <x-icon name="target" class="w-4 h-4" />
                    </div>
                    <h3 class="font-display text-lg font-medium text-[var(--ink)]">Deterministic Budgeting</h3>
                    <p class="text-xs text-[var(--muted)] leading-relaxed">
                        Set monthly caps across academic, food, housing, and personal categories with strict 75% warning and 100% danger alerts.
                    </p>
                </div>

                <div class="p-8 space-y-3">
                    <div class="w-8 h-8 bg-[var(--paper)] border hairline-border flex items-center justify-center text-[var(--ink)]">
                        <x-icon name="wallet" class="w-4 h-4" />
                    </div>
                    <h3 class="font-display text-lg font-medium text-[var(--ink)]">Precision Financial Ledger</h3>
                    <p class="text-xs text-[var(--muted)] leading-relaxed">
                        Zero floating-point arithmetic. Stored as exact decimals for dependable month-over-month cashbook reconciliation.
                    </p>
                </div>

                <div class="p-8 space-y-3">
                    <div class="w-8 h-8 bg-[var(--paper)] border hairline-border flex items-center justify-center text-[var(--secondary)]">
                        <x-icon name="lightbulb" class="w-4 h-4" />
                    </div>
                    <h3 class="font-display text-lg font-medium text-[var(--ink)]">Advisory AI Intelligence</h3>
                    <p class="text-xs text-[var(--muted)] leading-relaxed">
                        Smart category classification suggestions that stay advisory. You always maintain authoritative override control.
                    </p>
                </div>
            </div>
        </section>

        <!-- Sitemap Section (§5.3: Single hairline divided grid) -->
        <section id="sitemap" class="max-w-7xl mx-auto px-6 py-16">
            <div class="mb-8">
                <div class="text-xs font-caps text-[var(--muted)] mb-1">Architecture Reference</div>
                <h2 class="font-display text-2xl font-medium text-[var(--ink)] headline-rule">Application Sitemap</h2>
                <p class="text-xs text-[var(--muted)] mt-1">Live routing hierarchy reflecting the system's operational architecture</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 border hairline-border bg-[var(--panel)] divide-y md:divide-y-0 md:divide-x divide-[var(--hairline)]">
                <!-- 1. Public & Onboarding -->
                <div class="p-6 space-y-3">
                    <div class="text-xs font-caps text-[var(--accent)] font-medium">
                        1. Public & Access
                    </div>
                    <ul class="text-xs space-y-2 text-[var(--muted)]">
                        <li><a href="{{ url('/') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/ — Home & Sitemap</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/login — Student Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/register — Profile Setup</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/admin/login — Staff Access</a></li>
                    </ul>
                </div>

                <!-- 2. Student Financial Management -->
                <div class="p-6 space-y-3">
                    <div class="text-xs font-caps text-[var(--accent)] font-medium">
                        2. Student Ledger
                    </div>
                    <ul class="text-xs space-y-2 text-[var(--muted)]">
                        <li><a href="{{ url('/dashboard') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/dashboard — Main Cashbook</a></li>
                        <li><a href="{{ url('/transactions') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/transactions — Ledger Entries</a></li>
                        <li><a href="{{ url('/budgets') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/budgets — Spending Caps</a></li>
                        <li><a href="{{ url('/categories') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/categories — Tag Taxonomy</a></li>
                    </ul>
                </div>

                <!-- 3. Intelligence & Reporting -->
                <div class="p-6 space-y-3">
                    <div class="text-xs font-caps text-[var(--accent)] font-medium">
                        3. Analysis & Intelligence
                    </div>
                    <ul class="text-xs space-y-2 text-[var(--muted)]">
                        <li><a href="{{ url('/reports') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/reports — Financial Reports</a></li>
                        <li><a href="{{ url('/tips') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/tips — Rule-based Tips</a></li>
                    </ul>
                </div>

                <!-- 4. Operational Admin -->
                <div class="p-6 space-y-3">
                    <div class="text-xs font-caps text-[var(--secondary)] font-medium">
                        4. Staff Operations
                    </div>
                    <ul class="text-xs space-y-2 text-[var(--muted)]">
                        <li><a href="{{ url('/admin/dashboard') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/admin/dashboard — Console</a></li>
                        <li><a href="{{ url('/admin/users') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/admin/users — Student Accounts</a></li>
                        <li><a href="{{ url('/admin/categories') }}" class="hover:text-[var(--ink)] font-mono transition-colors">/admin/categories — System Tags</a></li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t hairline-border py-8 px-6 text-xs text-[var(--muted)] bg-[var(--panel)]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>CampusCoin &copy; {{ date('Y') }} &bull; Student Cashbook & Ledger System.</div>
            <div class="flex items-center gap-6 font-mono text-[11px]">
                <span>PHP 8.4</span>
                <span>Laravel 12</span>
                <span>Livewire 3</span>
            </div>
        </div>
    </footer>
</body>
</html>
