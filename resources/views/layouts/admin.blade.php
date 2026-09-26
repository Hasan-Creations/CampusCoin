<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Administration' }} — Campus Coin Ops</title>

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

    <div class="min-h-full flex" x-data="{ mobileNavOpen: false }">
        <!-- Admin Ops Sidebar (Desktop — Fixed 240px dark sidebar per §3) -->
        <aside class="sidebar-shell hidden md:sticky md:top-0 md:h-screen md:flex md:w-[240px] md:flex-col border-r hairline-border bg-[var(--ink)] text-[var(--paper)] shrink-0" aria-label="Administrator Sidebar Navigation">
            <div class="p-6 border-b border-[#3A362C] flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-[var(--secondary)] flex items-center justify-center text-[var(--paper)] font-display font-medium text-sm" aria-hidden="true">
                        AD
                    </div>
                    <div>
                        <div class="font-display font-medium text-base tracking-tight text-[var(--paper)]">CampusCoin</div>
                        <div class="text-[10px] font-caps text-[var(--secondary)] tracking-wider">Admin Console</div>
                    </div>
                </a>
            </div>

            <!-- Admin Navigation -->
            <nav class="flex-1 p-3 space-y-1" aria-label="Administrator Main Navigation">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-sans tracking-wide transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-3 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                    <x-icon name="activity" class="w-4 h-4 flex-shrink-0" />
                    <span>Overview & Telemetry</span>
                </a>

                <a href="{{ route('admin.users') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-sans tracking-wide transition-colors {{ request()->routeIs('admin.users*') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-3 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                    <x-icon name="users" class="w-4 h-4 flex-shrink-0" />
                    <span>Student Accounts</span>
                </a>

                <a href="{{ route('admin.categories') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-sans tracking-wide transition-colors {{ request()->routeIs('admin.categories*') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-3 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                    <x-icon name="tag" class="w-4 h-4 flex-shrink-0" />
                    <span>Global Categories</span>
                </a>
            </nav>

            <div class="p-4 border-t border-[#3A362C] bg-[#1A1813]">
                <div class="text-xs font-medium text-[var(--paper)] mb-0.5 truncate">{{ Auth::user()->name }}</div>
                <div class="text-[10px] font-caps text-[var(--secondary)] tracking-wider mb-3">Root Operator</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            aria-label="Sign out of administrator console"
                            class="w-full py-2 px-3 text-xs font-sans text-[#A8A599] hover:text-[var(--expense)] border border-[#3A362C] hover:border-[var(--expense)] transition-colors flex items-center justify-center gap-2 bg-transparent">
                        <x-icon name="log-out" class="w-3.5 h-3.5" />
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Admin Mobile Navigation Drawer -->
        <div x-show="mobileNavOpen" 
             x-cloak
             id="admin-mobile-navigation"
             role="dialog"
             aria-modal="true"
             aria-label="Admin Navigation Menu"
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
                 class="sidebar-shell relative flex-1 flex flex-col max-w-xs w-full bg-[var(--ink)] text-[var(--paper)] border-r hairline-border z-10">
                <div class="p-4 border-b border-[#3A362C] flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-[var(--secondary)] flex items-center justify-center text-[var(--paper)] font-display font-medium text-xs" aria-hidden="true">
                            AD
                        </div>
                        <span class="font-display font-medium text-sm text-[var(--paper)]">CampusAdmin</span>
                    </a>
                    <button type="button" 
                            @click="mobileNavOpen = false"
                            aria-label="Close navigation menu"
                            class="p-2 text-[#A8A599] hover:text-[var(--paper)]">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <nav class="flex-1 p-3 space-y-1 overflow-y-auto" aria-label="Admin Mobile Main Navigation">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs font-sans transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-2.5 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                        <x-icon name="activity" class="w-4 h-4" />
                        <span>Overview & Telemetry</span>
                    </a>

                    <a href="{{ route('admin.users') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs font-sans transition-colors {{ request()->routeIs('admin.users*') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-2.5 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                        <x-icon name="users" class="w-4 h-4" />
                        <span>Student Accounts</span>
                    </a>

                    <a href="{{ route('admin.categories') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs font-sans transition-colors {{ request()->routeIs('admin.categories*') ? 'bg-[#2E2B23] text-[var(--paper)] border-l-2 border-[var(--secondary)] pl-2.5 font-medium' : 'text-[#A8A599] hover:text-[var(--paper)] hover:bg-[#2A2720]' }}">
                        <x-icon name="tag" class="w-4 h-4" />
                        <span>Global Categories</span>
                    </a>
                </nav>

                <div class="p-4 border-t border-[#3A362C] bg-[#1A1813]">
                    <div class="text-xs font-medium text-[var(--paper)] mb-0.5 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] font-caps text-[var(--secondary)] tracking-wider mb-3">Root Operator</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                aria-label="Sign out of administrator console"
                                class="w-full py-2 text-xs font-sans text-[#A8A599] hover:text-[var(--expense)] border border-[#3A362C] hover:border-[var(--expense)] transition-colors flex items-center justify-center gap-2 bg-transparent">
                            <x-icon name="log-out" class="w-3.5 h-3.5" />
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Admin Top Bar -->
            <header class="border-b hairline-border bg-[var(--panel)] px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="mobileNavOpen = true"
                            aria-label="Open admin navigation menu"
                            :aria-expanded="mobileNavOpen"
                            aria-controls="admin-mobile-navigation"
                            class="md:hidden btn-icon">
                        <x-icon name="sliders" class="w-4 h-4" />
                    </button>
                    <nav class="flex items-center gap-2 text-xs font-mono text-[var(--muted)]" aria-label="Breadcrumb">
                        <span class="text-[var(--ink)] font-medium">CampusAdmin</span>
                        <span aria-hidden="true" class="opacity-40">/</span>
                        <span class="text-[var(--secondary)] font-medium">{{ $header ?? 'Overview' }}</span>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Font-Size Scaling Control (SRS §1.6 & §185 — Preserved Functionality) -->
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
                             class="absolute right-0 mt-1 w-44 border hairline-border bg-[var(--panel)] p-1 z-50 text-xs font-mono shadow-panel">
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
            </header>

            <!-- Page Body: 1280px max-width per §3 -->
            <main id="main-content" tabindex="-1" class="flex-1 p-6 sm:p-8 lg:p-10 max-w-[1280px] w-full overflow-y-auto focus:outline-none">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
