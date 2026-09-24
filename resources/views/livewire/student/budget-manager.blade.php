<div>
    <x-slot:header>
        Budget Goals
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- HEADER BAR: TITLE, MONTH PICKER, SET BUDGET ACTION     --}}
        {{-- ===================================================== --}}
        <div class="p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="text-xs font-mono text-[var(--accent-primary)] font-semibold uppercase tracking-wider mb-1">
                    Financial Planning &bull; {{ $monthDisplay }}
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Budget Goals
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Set and enforce monthly spending caps across your expense categories.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap flex-shrink-0">
                {{-- Month Selector --}}
                <div class="flex items-center gap-2">
                    <label for="month-picker" class="text-xs font-mono text-[var(--text-muted)] uppercase tracking-wider">
                        Month:
                    </label>
                    <input type="month"
                           id="month-picker"
                           aria-label="Filter budgets by month"
                           wire:model.live="selectedMonth"
                           class="input-campus py-1.5 px-3 text-xs font-mono w-40" />
                </div>

                {{-- Add Budget Button --}}
                <button type="button"
                        wire:click="openCreateModal"
                        class="btn-primary py-2 px-4 text-xs inline-flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Set Budget Goal</span>
                </button>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- FLASH MESSAGES                                         --}}
        {{-- ===================================================== --}}
        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 rounded-[6px] border border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="text-emerald-600 hover:text-emerald-800">
                    &times;
                </button>
            </div>
        @endif

        @if ($errorMessage)
            <div role="alert" aria-live="assertive" class="p-4 rounded-[6px] border border-rose-200 bg-rose-50 dark:border-rose-900/50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="text-rose-600 hover:text-rose-800">
                    &times;
                </button>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- IN-APP ALERT BANNER FOR OVER-BUDGET / NEAR-LIMIT       --}}
        {{-- ===================================================== --}}
        @if ($overBudgetCount > 0)
            <div class="p-4 rounded-[6px] border border-rose-300 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/50 text-rose-900 dark:text-rose-200 flex items-start gap-3">
                <x-icon name="shield-alert" class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" />
                <div class="text-xs space-y-1">
                    <div class="font-bold tracking-tight">
                        Budget Alert: {{ $overBudgetCount }} {{ $overBudgetCount === 1 ? 'category has' : 'categories have' }} exceeded their monthly limit
                    </div>
                    <div class="text-rose-700 dark:text-rose-300">
                        Review your transactions or adjust your budget limits to prevent further overspending this month.
                    </div>
                </div>
            </div>
        @elseif ($nearLimitCount > 0)
            <div class="p-4 rounded-[6px] border border-amber-300 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/50 text-amber-900 dark:text-amber-200 flex items-start gap-3">
                <x-icon name="target" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                <div class="text-xs space-y-1">
                    <div class="font-bold tracking-tight">
                        Budget Notice: {{ $nearLimitCount }} {{ $nearLimitCount === 1 ? 'category is' : 'categories are' }} nearing the monthly limit (&ge;75%)
                    </div>
                    <div class="text-amber-700 dark:text-amber-300">
                        Spending in these categories is approaching your set threshold. Monitor your remaining balance.
                    </div>
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- SUMMARY METRICS ROW                                    --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Budgeted --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Budgeted</span>
                    <x-icon name="target" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format((float) $totalBudgeted, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Across {{ $decoratedBudgets->count() }} {{ $decoratedBudgets->count() === 1 ? 'goal' : 'goals' }} in {{ $monthDisplay }}
                </div>
            </div>

            {{-- Total Spent on Budgeted Categories --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Spent</span>
                    <x-icon name="trending-down" class="w-4 h-4 text-[var(--danger)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format((float) $totalSpentOnBudgets, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Verified from actual ledger
                </div>
            </div>

            {{-- Remaining Budget --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Net Remaining</span>
                    <x-icon name="wallet" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold tabular-nums {{ (float) $totalRemaining >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    ${{ number_format((float) $totalRemaining, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ (float) $totalRemaining >= 0 ? 'Available before caps' : 'Over combined budget limits' }}
                </div>
            </div>

            {{-- Health Breakdown --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Budget Health</span>
                    <x-icon name="activity" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-xl font-bold text-[var(--text-primary)] tabular-nums flex items-baseline gap-2">
                    <span class="text-emerald-600 dark:text-emerald-400">{{ $onTrackCount }}</span>
                    <span class="text-xs text-[var(--text-muted)] font-normal">track</span>
                    <span class="text-[var(--gold)]">&bull; {{ $nearLimitCount }}</span>
                    <span class="text-xs text-[var(--text-muted)] font-normal">warn</span>
                    <span class="text-rose-600 dark:text-rose-400">&bull; {{ $overBudgetCount }}</span>
                    <span class="text-xs text-[var(--text-muted)] font-normal">over</span>
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ $decoratedBudgets->count() > 0 ? round(($onTrackCount / $decoratedBudgets->count()) * 100) . '% compliance' : 'No goals active' }}
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- BUDGET GOALS GRID                                      --}}
        {{-- ===================================================== --}}
        @if ($decoratedBudgets->isEmpty())
            <div class="card-campus border hairline-border p-12 text-center space-y-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[8px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                    <x-icon name="target" class="w-6 h-6" />
                </div>
                <div class="space-y-1">
                    <h2 class="font-heading text-base font-bold text-[var(--text-primary)] uppercase tracking-wide">
                        NO BUDGET GOALS SET FOR {{ strtoupper($monthDisplay) }}
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto">
                        Setting monthly category limits helps prevent impulse spending and guarantees you hit your student savings target.
                    </p>
                </div>
                <button type="button"
                        wire:click="openCreateModal"
                        class="btn-primary py-2 px-4 text-xs inline-flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Set First Budget Goal</span>
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($decoratedBudgets as $item)
                    @php
                        $b = $item['model'];
                        $cat = $b->category;
                        $catColor = $cat?->color ?? '#64748B';
                        $catIcon = $cat?->icon ?? 'tag';
                        $pct = $item['percentage'];
                        $isOver = $item['status'] === 'over_budget';
                    @endphp
                    <div class="card-campus border hairline-border p-5 space-y-4 flex flex-col justify-between hover:border-[var(--text-muted)]/40 transition-colors">
                        {{-- Top line: Category Info + Status Badge --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-[6px] flex items-center justify-center text-white flex-shrink-0"
                                     style="background-color: {{ $catColor }};">
                                    <x-icon :name="$catIcon" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0">
                                    <div class="font-heading font-semibold text-sm text-[var(--text-primary)] truncate">
                                        {{ $cat?->name ?? 'Uncategorized' }}
                                    </div>
                                    <div class="text-[11px] font-mono text-[var(--text-muted)]">
                                        {{ $b->month_year }}
                                    </div>
                                </div>
                            </div>
                            <span class="badge-campus text-[10px] font-mono uppercase tracking-wider {{ $item['badgeClass'] }} flex-shrink-0">
                                {{ $item['statusLabel'] }}
                            </span>
                        </div>

                        {{-- Middle: Monetary Progress & Figures --}}
                        <div class="space-y-2">
                            <div class="flex items-baseline justify-between text-xs">
                                <div>
                                    <span class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums">
                                        ${{ number_format((float) $item['spent'], 2) }}
                                    </span>
                                    <span class="text-[11px] font-mono text-[var(--text-muted)]">
                                        / ${{ number_format((float) $b->amount, 2) }}
                                    </span>
                                </div>
                                <div class="font-mono text-xs font-semibold tabular-nums" style="color: {{ $item['barColor'] }};">
                                    {{ $pct }}%
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full h-2 rounded-[4px] bg-[var(--bg-subtle)] overflow-hidden">
                                <div class="h-full rounded-[4px] transition-all duration-500"
                                     style="width: {{ min(100, $pct) }}%; background-color: {{ $item['barColor'] }};">
                                </div>
                            </div>

                            {{-- Remaining or Over-Budget Details --}}
                            <div class="flex items-center justify-between text-[11px] font-mono pt-1">
                                <span class="text-[var(--text-muted)]">
                                    {{ $isOver ? 'Over budget:' : 'Remaining:' }}
                                </span>
                                @if ($isOver)
                                    <span class="font-bold text-rose-600 dark:text-rose-400 tabular-nums">
                                        -${{ number_format(abs((float) $item['remaining']), 2) }}
                                    </span>
                                @else
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                        ${{ number_format((float) $item['remaining'], 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="pt-3 border-t hairline-border flex items-center justify-between text-xs">
                            <span class="text-[10px] font-mono text-[var(--text-muted)]">
                                {{ $cat?->isDefault() ? 'System default' : 'Personal' }}
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        wire:click="openEditModal({{ $b->id }})"
                                        class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors"
                                        title="Edit limit">
                                    <x-icon name="sliders" class="w-3.5 h-3.5" />
                                </button>
                                <button type="button"
                                        wire:click="confirmDelete({{ $b->id }})"
                                        class="p-1.5 rounded-[4px] border hairline-border hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[var(--text-muted)] hover:text-rose-600 transition-colors"
                                        title="Delete budget">
                                    <x-icon name="shield-alert" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- CREATE / EDIT MODAL                                    --}}
        {{-- ===================================================== --}}
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="modal-budget-title"
                 x-data
                 @keydown.escape.window="$wire.closeModal()">
                <div class="card-campus border hairline-border p-6 max-w-md w-full bg-[var(--bg-surface)] shadow-xl space-y-5"
                     @click.away="$wire.closeModal()">
                    <div class="flex items-center justify-between pb-3 border-b hairline-border">
                        <div>
                            <div class="text-[10px] font-mono text-[var(--accent-primary)] uppercase tracking-wider">
                                {{ $editingId ? 'Edit Configuration' : 'New Spending Cap' }}
                            </div>
                            <h2 id="modal-budget-title" class="font-heading text-lg font-bold text-[var(--text-primary)]">
                                {{ $editingId ? 'Edit Budget Goal' : 'Set Budget Goal' }}
                            </h2>
                        </div>
                        <button type="button" wire:click="closeModal" aria-label="Close modal" class="text-[var(--text-muted)] hover:text-[var(--text-primary)]">
                            &times;
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-4">
                        {{-- Month Selector --}}
                        <div class="space-y-1">
                            <label for="form-month" class="block text-xs font-semibold text-[var(--text-primary)]">
                                Target Month
                            </label>
                            <input type="month"
                                   id="form-month"
                                   wire:model="month_year"
                                   class="input-campus w-full py-2 px-3 text-xs font-mono" />
                            @error('month_year')
                                <div class="text-[11px] font-mono text-rose-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Expense Category Select --}}
                        <div class="space-y-1">
                            <label for="form-category" class="block text-xs font-semibold text-[var(--text-primary)]">
                                Expense Category
                            </label>
                            <select id="form-category"
                                    wire:model="category_id"
                                    class="input-campus w-full py-2 px-3 text-xs">
                                <option value="">-- Select Expense Category --</option>
                                @foreach ($eligibleCategories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->name }} ({{ $cat->isDefault() ? 'System' : 'Personal' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="text-[11px] font-mono text-rose-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Budget Limit Amount --}}
                        <div class="space-y-1">
                            <label for="form-amount" class="block text-xs font-semibold text-[var(--text-primary)]">
                                Monthly Budget Limit ($)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-xs font-mono text-[var(--text-muted)]">$</span>
                                <input type="number"
                                       id="form-amount"
                                       step="0.01"
                                       min="0.01"
                                       max="999999.99"
                                       placeholder="150.00"
                                       wire:model="amount"
                                       class="input-campus w-full py-2 pl-7 pr-3 text-xs font-mono" />
                            </div>
                            <p class="text-[10px] text-[var(--text-muted)]">
                                Maximum planned spending for this category in this month.
                            </p>
                            @error('amount')
                                <div class="text-[11px] font-mono text-rose-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Modal Action Buttons --}}
                        <div class="pt-3 border-t hairline-border flex items-center justify-end gap-3">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="btn-secondary py-2 px-4 text-xs">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn-primary py-2 px-4 text-xs">
                                {{ $editingId ? 'Save Changes' : 'Set Budget' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- DELETE CONFIRMATION MODAL                              --}}
        {{-- ===================================================== --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="modal-delete-budget-title"
                 x-data
                 @keydown.escape.window="$wire.cancelDelete()">
                <div class="card-campus border hairline-border p-6 max-w-sm w-full bg-[var(--bg-surface)] shadow-xl space-y-4"
                     @click.away="$wire.cancelDelete()">
                    <div class="flex items-center gap-3 text-rose-600">
                        <x-icon name="shield-alert" class="w-5 h-5 flex-shrink-0" />
                        <h2 id="modal-delete-budget-title" class="font-heading text-base font-bold text-[var(--text-primary)]">
                            Delete Budget Goal?
                        </h2>
                    </div>
                    <p class="text-xs text-[var(--text-muted)]">
                        Are you sure you want to remove this monthly budget limit? Existing transaction records will not be deleted.
                    </p>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                                wire:click="cancelDelete"
                                class="btn-secondary py-2 px-3 text-xs">
                            Cancel
                        </button>
                        <button type="button"
                                wire:click="delete"
                                class="py-2 px-3 text-xs font-medium rounded-[6px] bg-rose-600 text-white hover:bg-rose-700 transition-colors">
                            Delete Budget
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
