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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <div class="min-h-full flex flex-col justify-between">
        <!-- Top Minimal Header -->
        <header class="w-full border-b hairline-border bg-[var(--bg-surface)] px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-[4px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-sm tracking-tight shadow-sm">
                        CC
                    </div>
                    <span class="font-heading font-bold text-lg text-[var(--text-primary)] tracking-tight">Campus<span class="text-[var(--accent-primary)]">Coin</span></span>
                </a>

                <div class="flex items-center gap-4 text-xs font-mono text-[var(--text-muted)]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        SYSTEM OPERATIONAL
                    </span>
                    <button type="button" 
                            onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                        <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                        <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Auth Content Area -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="w-full border-t hairline-border py-4 px-6 text-center text-xs text-[var(--text-muted)]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>Campus Coin &copy; {{ date('Y') }} — Precision Student Budgeting</div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/#sitemap') }}" class="hover:text-[var(--text-primary)]">Sitemap</a>
                    <a href="{{ url('/admin/login') }}" class="hover:text-[var(--text-primary)]">Staff Portal</a>
                </div>
            </div>
        </footer>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @livewireScripts
</body>
</html>
