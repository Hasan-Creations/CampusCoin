<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Campus Coin' }} — Smart Spending, Student Style</title>

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
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <!-- Skip to Main Content Link for Keyboard & Screen Reader Users -->
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <div class="min-h-full flex flex-col justify-between">
        <!-- Top Minimal Header -->
        <header class="w-full border-b hairline-border bg-[var(--bg-surface)] px-6 py-3.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group" aria-label="Campus Coin Home">
                    <div class="w-9 h-9 rounded-[8px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-sm tracking-tight shadow-tactile-sm transition-transform group-hover:scale-105" aria-hidden="true">
                        CC
                    </div>
                    <span class="font-heading font-bold text-lg text-[var(--text-primary)] tracking-tight">Campus<span class="text-[var(--accent-primary)]">Coin</span></span>
                </a>

                <div class="flex items-center gap-4 text-xs font-mono text-[var(--text-muted)]">
                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]" aria-label="System status operational">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" aria-hidden="true"></span>
                        <span class="text-[10px] font-semibold text-[var(--text-primary)] tracking-wide">SYSTEM OPERATIONAL</span>
                    </span>

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
        <footer class="w-full border-t hairline-border py-4 px-6 text-center text-xs text-[var(--text-muted)] bg-[var(--bg-surface)]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>Campus Coin &copy; {{ date('Y') }} &bull; Precision Collegiate Financial Architecture</div>
                <div class="flex items-center gap-4 font-mono text-[11px]">
                    <a href="{{ url('/#sitemap') }}" class="hover:text-[var(--text-primary)] transition-colors">Sitemap</a>
                    <span class="opacity-40">&bull;</span>
                    <a href="{{ url('/admin/login') }}" class="hover:text-[var(--gold)] transition-colors">Staff Portal</a>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
