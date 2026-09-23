<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Console' }} — Campus Coin Ops</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <div class="min-h-full flex">
        <!-- Admin Ops Sidebar -->
        <aside class="w-64 flex-col border-r hairline-border bg-[var(--bg-surface)] flex">
            <div class="p-6 border-b hairline-border flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[4px] bg-[var(--text-primary)] text-[var(--bg-surface)] flex items-center justify-center font-heading font-bold text-sm tracking-tight shadow-sm">
                        OP
                    </div>
                    <div>
                        <div class="font-heading font-bold text-base tracking-tight leading-none text-[var(--text-primary)]">Campus<span class="text-[var(--gold)]">Admin</span></div>
                        <div class="text-[10px] font-mono text-[var(--text-muted)] tracking-wider uppercase mt-1">Operations Console</div>
                    </div>
                </a>
            </div>

            <!-- Admin Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="activity" class="w-4 h-4" />
                    Overview
                </a>

                <a href="{{ url('/admin/users') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('admin/users*') ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="users" class="w-4 h-4" />
                    User Management
                </a>

                <a href="{{ url('/admin/categories') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('admin/categories*') ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="tag" class="w-4 h-4" />
                    Global Categories
                </a>

                <a href="{{ url('/admin/templates') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('admin/templates*') ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="lightbulb" class="w-4 h-4" />
                    Tip Templates
                </a>

                <a href="{{ url('/admin/system') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-sm font-medium transition-colors {{ request()->is('admin/system*') ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)]' }}">
                    <x-icon name="database" class="w-4 h-4" />
                    System & Metrics
                </a>
            </nav>

            <div class="p-4 border-t hairline-border bg-[var(--bg-subtle)]/50">
                <div class="text-xs font-semibold text-[var(--text-primary)] mb-1">{{ Auth::user()->name }}</div>
                <div class="text-[10px] font-mono text-[var(--gold)] uppercase tracking-wider mb-3">Root Operator</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)] text-[var(--danger)] transition-colors">
                        <x-icon name="log-out" class="w-3.5 h-3.5" />
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="border-b hairline-border bg-[var(--bg-surface)] px-6 py-3.5 flex items-center justify-between">
                <div class="text-xs font-mono text-[var(--text-muted)]">
                    OPERATIONAL CONTROL PANEL &bull; RESTRICTED ACCESS
                </div>
                <button type="button" 
                        onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                        class="p-2 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                    <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                    <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                </button>
            </header>

            <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
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
