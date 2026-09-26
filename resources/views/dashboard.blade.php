<x-layouts.app title="Dashboard">
    <x-slot:header>
        Dashboard
    </x-slot:header>

    <div class="space-y-6">
        <!-- Personalized Greeting & Quick Summary -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 border hairline-border bg-[var(--panel)]">
            <div>
                <div class="text-xs font-mono text-[var(--accent)] font-medium uppercase tracking-wider mb-1">
                    {{ Auth::user()->academic_year ?? 'Student' }} Cohort &bull; Active Academic Term
                </div>
                <h1 class="font-display text-2xl font-medium text-[var(--ink)]">
                    Welcome back, {{ Auth::user()->name }}
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1">
                    Your financial baseline is set to ${{ number_format(Auth::user()->monthly_allowance, 2) }} / month with a ${{ number_format(Auth::user()->savings_goal, 2) }} reserve target.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('/transactions') }}" class="btn-primary py-2 px-4 text-xs">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Quick Add Transaction</span>
                </a>
            </div>
        </div>

        <!-- Four KPI Cards (Phase 0 Foundation preview) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Total Balance</span>
                    <x-icon name="wallet" class="w-4 h-4 text-[var(--accent)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums">
                    ${{ number_format(Auth::user()->monthly_allowance, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Initial student baseline
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Monthly Income</span>
                    <x-icon name="trending-up" class="w-4 h-4 text-[var(--accent)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--accent)] tabular-nums">
                    ${{ number_format(Auth::user()->monthly_allowance, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Active allowance allotment
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Monthly Spent</span>
                    <x-icon name="trending-down" class="w-4 h-4 text-[var(--muted)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums">
                    $0.00
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Ledger ready for Phase 1 entries
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Savings Goal</span>
                    <x-icon name="target" class="w-4 h-4 text-[var(--secondary)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--secondary)] tabular-nums">
                    ${{ number_format(Auth::user()->savings_goal, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Monthly accumulation target
                </div>
            </div>
        </div>

        <!-- Empty state placeholder per Section 33 -->
        <div class="card-campus border hairline-border p-12 text-center space-y-3">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-[var(--paper)] text-[var(--muted)] mx-auto border hairline-border">
                <x-icon name="wallet" class="w-6 h-6" />
            </div>
            <h2 class="font-display text-lg font-medium text-[var(--ink)] uppercase tracking-wide">
                No Transactions Recorded Yet
            </h2>
            <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">
                Start tracking your campus expenses to unlock cash flow trends, category breakdowns, and safe-to-spend intelligence.
            </p>
            <div class="pt-2">
                <button type="button" class="btn-primary py-2 px-4 text-xs opacity-80 cursor-not-allowed">
                    Add First Transaction (Phase 1)
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>
