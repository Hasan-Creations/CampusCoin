<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Campus Coin' }} — Smart Spending, Student Style</title>

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
                const fontSize = localStorage.getItem('font-size') || 'normal';
                document.documentElement.setAttribute('data-font-size', fontSize);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--ink)] bg-[var(--paper)]">
    <!-- Skip to Main Content Link for Keyboard & Screen Reader Users -->
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <div class="min-h-full flex flex-col justify-between">
        <!-- Top Minimal Header -->
        <header class="w-full border-b hairline-border bg-[var(--panel)] px-6 py-3.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Campus Coin Home">
                    <div class="w-8 h-8 bg-[var(--accent)] flex items-center justify-center text-[var(--paper)] font-display font-medium text-sm" aria-hidden="true">
                        CC
                    </div>
                    <span class="font-display font-medium text-lg text-[var(--ink)] tracking-tight">CampusCoin</span>
                </a>

                <div class="flex items-center gap-4 text-xs font-mono text-[var(--muted)]">
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
                             class="absolute right-0 mt-2 w-44 border hairline-border bg-[var(--panel)] shadow-panel p-1.5 z-50 text-xs font-mono">
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('normal')"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between transition-colors"
                                    :class="fontSize === 'normal' ? 'bg-[var(--accent)] text-[var(--paper)] font-medium' : 'text-[var(--ink)] hover:bg-[var(--paper)]'">
                                <span>Normal (100%)</span>
                                <span x-show="fontSize === 'normal'" aria-hidden="true">✓</span>
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('large')"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between transition-colors"
                                    :class="fontSize === 'large' ? 'bg-[var(--accent)] text-[var(--paper)] font-medium' : 'text-[var(--ink)] hover:bg-[var(--paper)]'">
                                <span>Large (112.5%)</span>
                                <span x-show="fontSize === 'large'" aria-hidden="true">✓</span>
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    @click="setFontSize('xlarge')"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between transition-colors"
                                    :class="fontSize === 'xlarge' ? 'bg-[var(--accent)] text-[var(--paper)] font-medium' : 'text-[var(--ink)] hover:bg-[var(--paper)]'">
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
                            class="btn-icon">
                        <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                        <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Auth Content Area -->
        <main id="main-content" tabindex="-1" class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 focus:outline-none">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="w-full border-t hairline-border py-4 px-6 text-center text-xs text-[var(--muted)] bg-[var(--panel)]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>CampusCoin &copy; {{ date('Y') }} &bull; Student Cashbook & Financial Ledger</div>
                <div class="flex items-center gap-4 font-mono text-[11px]">
                    <a href="{{ url('/#sitemap') }}" class="hover:text-[var(--ink)] transition-colors">Sitemap</a>
                    <span class="opacity-40">&bull;</span>
                    <a href="{{ url('/admin/login') }}" class="hover:text-[var(--secondary)] transition-colors">Staff Portal</a>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
