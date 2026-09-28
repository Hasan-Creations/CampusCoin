<div>
    <x-slot:header>
        Budget Goals
    </x-slot:header>

    <div class="space-y-6">
        <div class="page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-caps text-[var(--muted)]">
                    Financial Planning &bull; {{ $monthDisplay }}
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] mt-1 headline-rule">
                    Budget Goals
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1.5">
                    Set and enforce monthly spending caps across your expense categories.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap flex-shrink-0">
                {{-- Month Selector --}}
                <div class="flex items-center gap-2">
                    <label for="month-picker" class="text-xs font-caps text-[var(--muted)]">Month:</label>
                    <input type="month"
                           id="month-picker"
                           aria-label="Filter budgets by month"
                           wire:model.live="selectedMonth"
                           class="field py-1.5 px-3 text-xs font-mono w-40" />
                </div>

                {{-- Add Budget Button --}}
                <x-button variant="accent" wire:click="openCreateModal">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Set Budget Goal</span>
                </x-button>
            </div>
        </div>

        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-xs text-[var(--accent)] flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="check-circle-2" class="w-4 h-4 flex-shrink-0" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">
                    &times;
                </button>
            </div>
        @endif

        @if ($errorMessage)
            <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-xs text-[var(--expense)] flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="shield-alert" class="w-4 h-4 flex-shrink-0" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon w-6 h-6 border-none">
                    &times;
                </button>
            </div>
        @endif

        @if ($overBudgetCount > 0)
            <div class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-[var(--ink)] flex items-start gap-3" role="alert" aria-live="assertive">
                <x-icon name="shield-alert" class="w-5 h-5 text-[var(--expense)] flex-shrink-0 mt-0.5" />
                <div class="text-xs space-y-1">
                    <div class="font-medium text-[var(--expense)]">
                        Budget Alert: {{ $overBudgetCount }} {{ $overBudgetCount === 1 ? 'category has' : 'categories have' }} exceeded their monthly limit
                    </div>
                    <div class="text-[var(--muted)]">
                        Review your transactions or adjust your budget limits to prevent further overspending this month.
                    </div>
                </div>
            </div>
        @elseif ($nearLimitCount > 0)
            <div class="p-4 border border-[var(--secondary)] bg-[var(--paper)] text-[var(--ink)] flex items-start gap-3" role="status" aria-live="polite">
                <x-icon name="target" class="w-5 h-5 text-[var(--secondary)] flex-shrink-0 mt-0.5" />
                <div class="text-xs space-y-1">
                    <div class="font-medium text-[var(--secondary)]">
                        Budget Notice: {{ $nearLimitCount }} {{ $nearLimitCount === 1 ? 'category is' : 'categories are' }} nearing the monthly limit (&ge;75%)
                    </div>
                    <div class="text-[var(--muted)]">
                        Spending in these categories is approaching your set threshold. Monitor your remaining balance.
                    </div>
                </div>
            </div>
        @endif

        <div class="stat-strip">
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Total Budgeted</div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums mt-1">
                    ${{ number_format((float) $totalBudgeted, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    Across {{ $decoratedBudgets->count() }} {{ $decoratedBudgets->count() === 1 ? 'goal' : 'goals' }} in {{ $monthDisplay }}
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Total Spent</div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums mt-1">
                    ${{ number_format((float) $totalSpentOnBudgets, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">Verified from actual ledger</div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Net Remaining</div>
                <div class="font-mono text-2xl font-medium tabular-nums mt-1 {{ (float) $totalRemaining >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">
                    ${{ number_format((float) $totalRemaining, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    {{ (float) $totalRemaining >= 0 ? 'Available before caps' : 'Over combined budget limits' }}
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Budget Health</div>
                <div class="font-mono text-xl font-medium text-[var(--ink)] tabular-nums flex items-baseline gap-2 mt-1">
                    <span class="text-[var(--accent)]">{{ $onTrackCount }}</span>
                    <span class="text-xs text-[var(--muted)] font-sans font-normal">track</span>
                    <span class="text-[var(--secondary)]">&bull; {{ $nearLimitCount }}</span>
                    <span class="text-xs text-[var(--muted)] font-sans font-normal">warn</span>
                    <span class="text-[var(--expense)]">&bull; {{ $overBudgetCount }}</span>
                    <span class="text-xs text-[var(--muted)] font-sans font-normal">over</span>
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    {{ $decoratedBudgets->count() > 0 ? round(($onTrackCount / $decoratedBudgets->count()) * 100) . '% compliance' : 'No goals active' }}
                </div>
            </div>
        </div>

        @if ($decoratedBudgets->isEmpty())
            <div class="empty-state space-y-3">
                <x-icon name="target" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                <div class="space-y-1">
                    <h2 class="font-display text-base font-medium text-[var(--ink)] uppercase tracking-wide">
                        NO BUDGET GOALS SET for {{ strtoupper($monthDisplay) }}
                    </h2>
                    <p class="text-xs text-[var(--muted)] max-w-md mx-auto">
                        Setting monthly category limits helps prevent impulse spending and guarantees you hit your student savings target.
                    </p>
                </div>
                <x-button variant="secondary" wire:click="openCreateModal">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Set First Budget Goal</span>
                </x-button>
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
                    <div class="card-campus p-5 space-y-4 flex flex-col justify-between">
                        {{-- Top line: Category Info + Status Badge --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 flex items-center justify-center text-white flex-shrink-0"
                                     style="background-color: {{ $catColor }};">
                                    <x-icon :name="$catIcon" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0">
                                    <div class="font-display font-medium text-sm text-[var(--ink)] truncate">
                                        {{ $cat?->name ?? 'Uncategorized' }}
                                    </div>
                                    <div class="text-[11px] font-mono text-[var(--muted)]">
                                        {{ $b->month_year }}
                                    </div>
                                </div>
                            </div>
                            <span class="badge {{ $item['badgeClass'] }} flex-shrink-0">
                                {{ $item['statusLabel'] }}
                            </span>
                        </div>

                        {{-- Middle: Monetary Progress & Figures --}}
                        <div class="space-y-2">
                            <div class="flex items-baseline justify-between text-xs">
                                <div>
                                    <span class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums">
                                        ${{ number_format((float) $item['spent'], 2) }}
                                    </span>
                                    <span class="text-[11px] font-mono text-[var(--muted)]">
                                        / ${{ number_format((float) $b->amount, 2) }}
                                    </span>
                                </div>
                                <div class="font-mono text-xs font-medium tabular-nums" style="color: {{ $item['barColor'] }};">
                                    {{ $pct }}%
                                </div>
                            </div>

                            {{-- Progress Bar (flat, no radius) --}}
                            <div class="w-full h-1.5 bg-[var(--paper)] border hairline-border overflow-hidden">
                                <div class="h-full transition-all duration-500"
                                     style="width: {{ min(100, $pct) }}%; background-color: {{ $item['barColor'] }};"></div>
                            </div>

                            {{-- Remaining or Over-Budget Details --}}
                            <div class="flex items-center justify-between text-[11px] font-mono pt-1">
                                <span class="text-[var(--muted)]">
                                    {{ $isOver ? 'Over budget:' : 'Remaining:' }}
                                </span>
                                @if ($isOver)
                                    <span class="font-medium text-[var(--expense)] tabular-nums">
                                        -${{ number_format(abs((float) $item['remaining']), 2) }}
                                    </span>
                                @else
                                    <span class="font-medium text-[var(--accent)] tabular-nums">
                                        ${{ number_format((float) $item['remaining'], 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="pt-3 border-t hairline-border flex items-center justify-between text-xs">
                            <span class="text-[10px] font-mono text-[var(--muted)]">
                                {{ $cat?->isDefault() ? 'System default' : 'Personal' }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        wire:click="openEditModal({{ $b->id }})"
                                        class="btn-icon w-8 h-8"
                                        title="Edit limit">
                                    <x-icon name="sliders" class="w-3.5 h-3.5" />
                                </button>
                                <button type="button"
                                        wire:click="confirmDelete({{ $b->id }})"
                                        class="btn-icon w-8 h-8 hover:text-[var(--expense)] hover:border-[var(--expense)]"
                                        title="Delete budget">
                                    <x-icon name="shield-alert" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($showModal)
            <x-modal :show="true"
                     :title="$editingId ? 'Edit Budget Goal' : 'Set Budget Goal'"
                     titleId="modal-budget-title"
                     maxWidth="md"
                     onClose="$wire.closeModal()">
                <div class="space-y-4">
                    <p class="text-xs text-[var(--muted)] font-mono -mt-2 mb-3">
                        {{ $editingId ? 'Edit spending limit configuration' : 'Set monthly category cap' }}
                    </p>

                    <form wire:submit.prevent="save" class="space-y-4">
                        {{-- Month Selector --}}
                        <div class="space-y-1">
                            <label for="form-month" class="block text-xs font-caps text-[var(--muted)]">
                                Target Month
                            </label>
                            <x-field type="month"
                                     id="form-month"
                                     wire:model="month_year"
                                     class="text-xs font-mono"
                                     :hasError="$errors->has('month_year')" />
                            @error('month_year')
                                <div class="text-[11px] font-mono text-[var(--expense)] mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Expense Category Select --}}
                        <div class="space-y-1">
                            <label for="form-category" class="block text-xs font-caps text-[var(--muted)]">
                                Expense Category
                            </label>
                            <x-field type="select"
                                     id="form-category"
                                     wire:model="category_id"
                                     class="text-xs"
                                     :hasError="$errors->has('category_id')">
                                <option value="">-- Select Expense Category --</option>
                                @foreach ($eligibleCategories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->name }} ({{ $cat->isDefault() ? 'System' : 'Personal' }})
                                    </option>
                                @endforeach
                            </x-field>
                            @error('category_id')
                                <div class="text-[11px] font-mono text-[var(--expense)] mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Budget Limit Amount --}}
                        <div class="space-y-1">
                            <label for="form-amount" class="block text-xs font-caps text-[var(--muted)]">
                                Monthly Budget Limit ($)
                            </label>
                            <x-field type="number"
                                     numeric
                                     id="form-amount"
                                     step="0.01"
                                     min="0.01"
                                     max="999999.99"
                                     placeholder="150.00"
                                     wire:model="amount"
                                     class="text-xs font-mono"
                                     :hasError="$errors->has('amount')" />
                            <p class="text-[10px] text-[var(--muted)] font-mono">
                                Maximum planned spending for this category in this month.
                            </p>
                            @error('amount')
                                <div class="text-[11px] font-mono text-[var(--expense)] mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Modal Action Buttons --}}
                        <div class="pt-3 border-t hairline-border flex items-center justify-end gap-2.5">
                            <x-button variant="secondary" wire:click="closeModal" type="button">
                                Cancel
                            </x-button>
                            <x-button variant="accent" type="submit">
                                {{ $editingId ? 'Save Changes' : 'Set Budget' }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif

        @if ($showDeleteModal)
            <x-modal :show="true"
                     title="Delete Budget Goal?"
                     titleId="modal-delete-budget-title"
                     maxWidth="sm"
                     onClose="$wire.cancelDelete()">
                <div class="space-y-4">
                    <p class="text-xs text-[var(--muted)]">
                        Are you sure you want to remove this monthly budget limit? Existing transaction records will not be deleted.
                    </p>
                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <x-button variant="secondary" wire:click="cancelDelete" type="button">
                            Cancel
                        </x-button>
                        <x-button variant="destructive" wire:click="delete" type="button">
                            Delete Budget
                        </x-button>
                    </div>
                </div>
            </x-modal>
        @endif
    </div>
</div>
