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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[var(--text-primary)] bg-[var(--bg-canvas)]">
    <div class="min-h-full flex">
        <!-- Sidebar for Desktop -->
        <aside class="hidden md:flex md:w-64 md:flex-col border-r hairline-border bg-[var(--bg-surface)]">
            <div class="p-6 border-b hairline-border flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[4px] bg-[var(--accent-primary)] flex items-center justify-center text-white font-heading font-bold text-sm tracking-tight shadow-sm">
                        CC
                    </div>
                    <div>
                        <div class="font-heading font-bold text-base tracking-tight leading-none text-[var(--text-primary)]">Campus<span class="text-[var(--accent-primary)]">Coin</span></div>
                        <div class="text-[10px] font-mono text-[var(--text-muted)] tracking-wider uppercase mt-1">Student Suite</div>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-1">
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
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--danger)] transition-colors">
                        <x-icon name="log-out" class="w-3.5 h-3.5" />
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top App Bar -->
            <header class="border-b hairline-border bg-[var(--bg-surface)] px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" class="md:hidden p-2 rounded-[6px] border hairline-border text-[var(--text-muted)]">
                        <x-icon name="sliders" class="w-4 h-4" />
                    </button>
                    <!-- Breadcrumbs -->
                    <nav class="flex items-center gap-2 text-xs font-mono text-[var(--text-muted)]">
                        <span class="text-[var(--text-primary)] font-semibold">Campus Coin</span>
                        <span>/</span>
                        <span>{{ $header ?? 'Overview' }}</span>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Allowance Pill Display -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-xs font-mono">
                        <span class="text-[var(--text-muted)] uppercase tracking-wider text-[10px]">Allowance:</span>
                        <span class="font-semibold tabular-nums text-[var(--text-primary)]">${{ number_format(Auth::user()->monthly_allowance ?? 0, 2) }}</span>
                    </div>

                    <!-- Theme Toggle -->
                    <button type="button" 
                            onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                            class="p-2 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                        <x-icon name="sun" class="w-4 h-4 dark:hidden" />
                        <x-icon name="moon" class="w-4 h-4 hidden dark:block" />
                    </button>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
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
