<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Campus Coin' }} — Smart Spending, Student Style</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                const fontSize = localStorage.getItem('fontSize');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                if (['small', 'normal', 'large'].includes(fontSize)) {
                    document.documentElement.dataset.fontSize = fontSize;
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full font-sans antialiased text-[var(--ink)] bg-[var(--paper)]">
    <a href="#main-content" class="skip-to-content">
        Skip to main content
    </a>

    <div class="min-h-full flex flex-col justify-between">
        <header class="w-full border-b hairline-border bg-[var(--panel)] px-6 py-3.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Campus Coin Home">
                    <div class="w-8 h-8 bg-[var(--accent)] flex items-center justify-center text-[var(--paper)] font-display font-medium text-sm"
                        aria-hidden="true">
                        CC
                    </div>
                    <span class="font-display font-medium text-lg text-[var(--ink)] tracking-tight">CampusCoin</span>
                </a>

                <div class="flex items-center gap-4 text-xs font-mono text-[var(--muted)]">
                    <label for="text-size-control" class="sr-only">Text size control</label>
                    <select id="text-size-control" aria-label="Text size control"
                        onchange="window.CampusCoin.setFontSize(this.value)" class="field text-xs py-1.5 px-2">
                        <option value="small">Small text</option>
                        <option value="normal">Normal text</option>
                        <option value="large">Large text</option>
                    </select>

                    <button type="button"
                        onclick="window.CampusCoin ? window.CampusCoin.toggleTheme() : (document.documentElement.classList.toggle('dark'), localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light'))"
                        aria-label="Toggle dark mode" title="Toggle dark mode" class="btn-icon">
                        <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                        <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                    </button>
                </div>
            </div>
        </header>

        <main id="main-content" tabindex="-1"
            class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 focus:outline-none">
            {{ $slot }}
        </main>

        <footer
            class="w-full border-t hairline-border py-4 px-6 text-center text-xs text-[var(--muted)] bg-[var(--panel)]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>CampusCoin &copy; {{ date('Y') }} &bull; Student Cashbook & Financial Ledger</div>
                <div class="flex items-center gap-4 font-mono text-[11px]">
                    <a href="{{ url('/#sitemap') }}" class="hover:text-[var(--ink)] transition-colors">Sitemap</a>
                    <span class="opacity-40">&bull;</span>
                    <a href="{{ url('/admin/login') }}" class="hover:text-[var(--secondary)] transition-colors">Staff
                        Portal</a>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>

</html>
