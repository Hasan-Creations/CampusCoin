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
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
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
