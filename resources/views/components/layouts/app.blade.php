<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — Campus Coin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

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
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <!-- Skip to Main Content Link for Keyboard & Screen Reader Users -->
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <div class="min-h-full flex" x-data="{ mobileNavOpen: false }">
        <!-- Sidebar for Desktop -->
        <aside class="hidden md:flex md:w-64 md:flex-col border-r hairline-border bg-[var(--bg-surface)] shrink-0" aria-label="Student Sidebar Navigation">
            <div class="p-6 border-b hairline-border flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[4px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-sm tracking-tight shadow-sm" aria-hidden="true">
                        CC
                    </div>
                    <div>
                        <div class="font-heading font-bold text-base tracking-tight leading-none text-[var(--text-primary)]">Campus<span class="text-[var(--accent-primary)]">Coin</span></div>
                        <div class="text-[10px] font-mono text-[var(--text-muted)] tracking-wider uppercase mt-1">Student Suite</div>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-1" aria-label="Desktop Main Navigation">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] dark:bg-[var(--accent-tint)] dark:text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="activity" class="w-4 h-4" />
                    Dashboard
                </a>

                <a href="{{ url('/transactions') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('transactions*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="wallet" class="w-4 h-4" />
                    Transactions
                </a>

                <a href="{{ url('/budgets') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('budgets*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="target" class="w-4 h-4" />
                    Budgets
                </a>

                <a href="{{ url('/categories') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('categories*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="tag" class="w-4 h-4" />
                    Categories
                </a>

                <a href="{{ url('/reports') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('reports*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="pie-chart" class="w-4 h-4" />
                    Reports
                </a>

                <a href="{{ route('student.tips') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->routeIs('student.tips*') || request()->is('tips*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="lightbulb" class="w-4 h-4" />
                    Saving Tips
                </a>
            </nav>

            <!-- User Cohort Information & Session Footer -->
            <div class="p-4 border-t hairline-border bg-[var(--bg-subtle)]/50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-xs font-semibold text-[var(--text-primary)]">{{ Auth::user()->name }}</div>
                        <div class="text-[11px] font-mono text-[var(--text-muted)]">{{ Auth::user()->academic_year ?? 'Student' }} &bull; Active</div>
                    </div>
                    <span class="badge-campus bg-[var(--accent-tint)] text-[var(--accent-primary)]">
                        {{ Auth::user()->role }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            aria-label="Sign out of student account"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--danger)] transition-colors">
                        <x-icon name="log-out" class="w-3.5 h-3.5" />
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileNavOpen" 
             x-cloak
             id="mobile-navigation"
             role="dialog"
             aria-modal="true"
             aria-label="Mobile Navigation Menu"
             @keydown.escape.window="mobileNavOpen = false"
             class="fixed inset-0 z-50 md:hidden flex">
            <!-- Backdrop -->
            <div x-show="mobileNavOpen"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileNavOpen = false"
                 class="fixed inset-0 bg-black/60"></div>

            <!-- Drawer Panel -->
            <div x-show="mobileNavOpen"
                 x-transition:enter="transition ease-in-out duration-200 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative flex-1 flex flex-col max-w-xs w-full bg-[var(--bg-surface)] border-r hairline-border shadow-xl">
                <div class="p-4 border-b hairline-border flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-[4px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-xs tracking-tight shadow-sm" aria-hidden="true">
                            CC
                        </div>
                        <span class="font-heading font-bold text-sm tracking-tight text-[var(--text-primary)]">Campus<span class="text-[var(--accent-primary)]">Coin</span></span>
                    </a>
                    <button type="button" 
                            @click="mobileNavOpen = false"
                            aria-label="Close navigation menu"
                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)]">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <nav class="flex-1 p-4 space-y-1 overflow-y-auto" aria-label="Mobile Main Navigation">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="activity" class="w-4 h-4" />
                        Dashboard
                    </a>

                    <a href="{{ url('/transactions') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('transactions*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="wallet" class="w-4 h-4" />
                        Transactions
                    </a>

                    <a href="{{ url('/budgets') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('budgets*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="target" class="w-4 h-4" />
                        Budgets
                    </a>

                    <a href="{{ url('/categories') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('categories*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="tag" class="w-4 h-4" />
                        Categories
                    </a>

                    <a href="{{ url('/reports') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('reports*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="pie-chart" class="w-4 h-4" />
                        Reports
                    </a>

                    <a href="{{ route('student.tips') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->routeIs('student.tips*') || request()->is('tips*') ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                        <x-icon name="lightbulb" class="w-4 h-4" />
                        Saving Tips
                    </a>
                </nav>

                <div class="p-4 border-t hairline-border bg-[var(--bg-subtle)]/50">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-xs font-semibold text-[var(--text-primary)]">{{ Auth::user()->name }}</div>
                            <div class="text-[11px] font-mono text-[var(--text-muted)]">{{ Auth::user()->academic_year ?? 'Student' }} &bull; Active</div>
                        </div>
                        <span class="badge-campus bg-[var(--accent-tint)] text-[var(--accent-primary)]">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                aria-label="Sign out of student account"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--danger)] transition-colors">
                            <x-icon name="log-out" class="w-3.5 h-3.5" />
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top App Bar -->
            <header class="border-b hairline-border bg-[var(--bg-surface)] px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="mobileNavOpen = true"
                            aria-label="Open mobile navigation menu"
                            :aria-expanded="mobileNavOpen"
                            aria-controls="mobile-navigation"
                            class="md:hidden p-2 rounded-[6px] border hairline-border text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]">
                        <x-icon name="sliders" class="w-4 h-4" />
                    </button>
                    <!-- Breadcrumbs -->
                    <nav class="flex items-center gap-2 text-xs font-mono text-[var(--text-muted)]" aria-label="Breadcrumb">
                        <span class="text-[var(--text-primary)] font-semibold">Campus Coin</span>
                        <span aria-hidden="true">/</span>
                        <span class="text-[var(--text-primary)]">{{ $header ?? 'Overview' }}</span>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Allowance Pill Display -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-xs font-mono" aria-label="Monthly allowance baseline">
                        <span class="text-[var(--text-muted)] uppercase tracking-wider text-[10px]">Allowance:</span>
                        <span class="font-semibold tabular-nums text-[var(--text-primary)]">${{ number_format(Auth::user()->monthly_allowance ?? 0, 2) }}</span>
                    </div>

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
                                class="p-2 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors flex items-center gap-1 font-mono text-xs">
                            <span class="font-bold text-xs" aria-hidden="true">aA</span>
                            <x-icon name="sliders" class="w-3 h-3" />
                        </button>
                        <div x-show="open"
                             @click.away="open = false"
                             x-cloak
                             role="menu"
                             aria-label="Text size options"
                             class="absolute right-0 mt-1 w-40 rounded-[6px] border hairline-border bg-[var(--bg-surface)] shadow-lg p-1 z-50 text-xs font-mono">
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('normal')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[4px] flex items-center justify-between transition-colors"
                                    :class="fontSize === 'normal' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                                <span>Normal (100%)</span>
                                <span x-show="fontSize === 'normal'" aria-hidden="true">✓</span>
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('large')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[4px] flex items-center justify-between transition-colors"
                                    :class="fontSize === 'large' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                                <span>Large (112.5%)</span>
                                <span x-show="fontSize === 'large'" aria-hidden="true">✓</span>
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('xlarge')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[4px] flex items-center justify-between transition-colors"
                                    :class="fontSize === 'xlarge' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]'">
                                <span>X-Large (125%)</span>
                                <span x-show="fontSize === 'xlarge'" aria-hidden="true">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button type="button" 
                            onclick="window.CampusCoin ? window.CampusCoin.toggleTheme() : (document.documentElement.classList.toggle('dark'), localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light'))"
                            aria-label="Toggle dark mode"
                            title="Toggle dark mode"
                            class="p-2 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors">
                        <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                        <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                    </button>
                </div>
            </header>

            <!-- Page Body -->
            <main id="main-content" tabindex="-1" class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto focus:outline-none">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
