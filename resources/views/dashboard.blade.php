<x-layouts.app title="Dashboard">
    <x-slot:header>
        Dashboard
    </x-slot:header>

    <div class="space-y-6">
        <!-- Personalized Greeting & Quick Summary -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)]">
            <div>
                <div class="text-xs font-mono text-[var(--accent-primary)] font-semibold uppercase tracking-wider mb-1">
                    {{ Auth::user()->academic_year ?? 'Student' }} Cohort &bull; Active Academic Term
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Welcome back, {{ Auth::user()->name }}
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
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
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Balance</span>
                    <x-icon name="wallet" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format(Auth::user()->monthly_allowance, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Initial student baseline
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Monthly Income</span>
                    <x-icon name="trending-up" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    ${{ number_format(Auth::user()->monthly_allowance, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Active allowance allotment
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Monthly Spent</span>
                    <x-icon name="trending-down" class="w-4 h-4 text-[var(--text-muted)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    $0.00
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Ledger ready for Phase 1 entries
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Savings Goal</span>
                    <x-icon name="target" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--gold)] tabular-nums">
                    ${{ number_format(Auth::user()->savings_goal, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Monthly accumulation target
                </div>
            </div>
        </div>

        <!-- Empty state placeholder per Section 33 -->
        <div class="card-campus border hairline-border p-12 text-center space-y-3">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                <x-icon name="wallet" class="w-6 h-6" />
            </div>
            <h2 class="font-heading text-lg font-bold text-[var(--text-primary)] uppercase tracking-wide">
                No Transactions Recorded Yet
            </h2>
            <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
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
