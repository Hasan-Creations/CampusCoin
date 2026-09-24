<div>
    <x-slot:header>
        Transactions
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Summary Banner & Quick-Add -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)]">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] bg-[var(--accent-tint)] text-[var(--accent-primary)] text-[11px] font-mono font-semibold uppercase tracking-wider mb-1">
                    <x-icon name="wallet" class="w-3.5 h-3.5" />
                    Double-Entry Verified Ledger
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Transaction History
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Deterministic income and expenditure records with category classification.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="openImportModal" 
                        title="Upload CSV spreadsheet for AI batch categorization"
                        class="btn-secondary py-2 px-3 text-xs">
                    <x-icon name="upload" class="w-4 h-4" />
                    <span class="hidden sm:inline">Import CSV</span>
                </button>
                <button wire:click="exportCsv" 
                        title="Download transactions as CSV spreadsheet"
                        class="btn-secondary py-2 px-3 text-xs">
                    <x-icon name="download" class="w-4 h-4" />
                    <span class="hidden sm:inline">Export CSV</span>
                </button>
                <button wire:click="openCreateModal" class="btn-primary py-2 px-4 text-xs">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Add Transaction</span>
                </button>
            </div>
        </div>

        <!-- Feedback & Alerts -->
        @if ($feedbackMessage)
            <div class="p-4 rounded-[6px] border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/40 dark:border-emerald-900 text-xs text-emerald-800 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div class="p-4 rounded-[6px] border border-rose-200 bg-rose-50 dark:bg-rose-950/40 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        <!-- Filter Controls Toolbar -->
        <div class="card-campus border hairline-border p-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search input -->
                <div class="lg:col-span-4 relative">
                    <span class="absolute left-3 top-2.5 text-[var(--text-muted)]">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input wire:model.live.debounce.250ms="search" 
                           type="text" 
                           placeholder="Search merchant, notes..." 
                           class="input-campus w-full pl-9 text-xs">
                </div>

                <!-- Type Selector -->
                <div class="lg:col-span-3">
                    <div class="inline-flex w-full rounded-[4px] border hairline-border p-0.5 bg-[var(--bg-subtle)] text-xs font-medium">
                        <button wire:click="$set('typeFilter', 'all')" 
                                class="flex-1 py-1.5 text-center rounded-[3px] transition-colors {{ $typeFilter === 'all' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)]' }}">
                            All
                        </button>
                        <button wire:click="$set('typeFilter', 'expense')" 
                                class="flex-1 py-1.5 text-center rounded-[3px] transition-colors {{ $typeFilter === 'expense' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)]' }}">
                            Expense
                        </button>
                        <button wire:click="$set('typeFilter', 'income')" 
                                class="flex-1 py-1.5 text-center rounded-[3px] transition-colors {{ $typeFilter === 'income' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)]' }}">
                            Income
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="lg:col-span-3">
                    <select wire:model.live="categoryFilter" class="input-campus w-full text-xs bg-[var(--bg-surface)]">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="lg:col-span-2">
                    <select wire:model.live="methodFilter" class="input-campus w-full text-xs bg-[var(--bg-surface)]">
                        <option value="">All Methods</option>
                        <option value="card">Card</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="digital_wallet">Digital Wallet</option>
                        <option value="upi">UPI</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Ledger Table / Empty State -->
        @if ($transactions->isEmpty())
            <div class="card-campus border hairline-border p-12 text-center space-y-3">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                    <x-icon name="wallet" class="w-6 h-6" />
                </div>
                @if ($totalCount === 0)
                    <!-- SRS Exact Empty State: NO TRANSACTIONS RECORDED YET -->
                    <h2 class="font-heading text-base font-bold text-[var(--text-primary)] uppercase tracking-wide">
                        NO TRANSACTIONS RECORDED YET
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                        Start tracking your campus expenses to unlock insights.
                    </p>
                    <button wire:click="openCreateModal" class="btn-primary py-2 px-4 text-xs mt-2">
                        Add First Transaction
                    </button>
                @else
                    <h2 class="font-heading text-base font-bold text-[var(--text-primary)] uppercase tracking-wide">
                        NO TRANSACTIONS MATCHING QUERY
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                        Clear active filters or modify search terms to view recorded entries.
                    </p>
                    <button wire:click="$set('search', ''); $set('typeFilter', 'all'); $set('categoryFilter', ''); $set('methodFilter', '');" 
                            class="btn-secondary py-1.5 px-3 text-xs mt-2">
                        Clear All Filters
                    </button>
                @endif
            </div>
        @else
            <div class="card-campus border hairline-border overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="border-b hairline-border font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)]">
                            <tr>
                                <th class="p-3.5 cursor-pointer hover:text-[var(--text-primary)]" wire:click="sortByColumn('transaction_date')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Date</span>
                                        @if ($sortBy === 'transaction_date')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="p-3.5 cursor-pointer hover:text-[var(--text-primary)]" wire:click="sortByColumn('merchant')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Merchant / Description</span>
                                        @if ($sortBy === 'merchant')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="p-3.5">Category</th>
                                <th class="p-3.5">Method</th>
                                <th class="p-3.5 cursor-pointer hover:text-[var(--text-primary)]" wire:click="sortByColumn('type')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Type</span>
                                        @if ($sortBy === 'type')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="p-3.5 text-right cursor-pointer hover:text-[var(--text-primary)]" wire:click="sortByColumn('amount')">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span>Amount</span>
                                        @if ($sortBy === 'amount')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="p-3.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y hairline-border">
                            @foreach ($transactions as $t)
                                <tr class="hover:bg-[var(--bg-subtle)]/60 transition-colors">
                                    <!-- Date -->
                                    <td class="p-3.5 font-mono tabular-nums text-[var(--text-muted)] whitespace-nowrap">
                                        {{ $t->transaction_date->format('M d, Y') }}
                                    </td>

                                    <!-- Merchant / Description -->
                                    <td class="p-3.5">
                                        <div class="font-semibold text-[var(--text-primary)]">
                                            {{ $t->merchant }}
                                            @if ($t->is_recurring)
                                                <span class="inline-block ml-1 text-[10px] font-mono text-purple-600 dark:text-purple-400 font-normal">
                                                    [Recurring]
                                                </span>
                                            @endif
                                        </div>
                                        @if ($t->description)
                                            <div class="text-[11px] text-[var(--text-muted)] truncate max-w-xs mt-0.5">
                                                {{ $t->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Category -->
                                    <td class="p-3.5 whitespace-nowrap">
                                        @if ($t->category)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium"
                                                  style="color: {{ $t->category->color }};">
                                                <x-icon :name="$t->category->icon" class="w-3.5 h-3.5" />
                                                <span>{{ $t->category->name }}</span>
                                            </span>
                                            @if ($t->ai_suggested)
                                                <span title="Categorized with AI Assistant ({{ $t->ai_confidence ? round($t->ai_confidence * 100).'%' : 'Advisory' }})"
                                                      class="inline-flex items-center gap-0.5 ml-1.5 px-1.5 py-0.5 rounded-[4px] bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-[10px] font-mono text-indigo-700 dark:text-indigo-300">
                                                    ✨ AI
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-[var(--text-muted)] italic">Uncategorized</span>
                                        @endif
                                    </td>

                                    <!-- Method -->
                                    <td class="p-3.5 font-mono uppercase text-[11px] text-[var(--text-muted)] whitespace-nowrap">
                                        {{ str_replace('_', ' ', $t->payment_method) }}
                                    </td>

                                    <!-- Type -->
                                    <td class="p-3.5 whitespace-nowrap">
                                        <span class="badge-campus {{ $t->isIncome() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400' }}">
                                            {{ $t->type }}
                                        </span>
                                    </td>

                                    <!-- Amount -->
                                    <td class="p-3.5 text-right font-mono font-semibold tabular-nums whitespace-nowrap {{ $t->isIncome() ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--text-primary)]' }}">
                                        {{ $t->formattedAmount() }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1">
                                            <button wire:click="openEditModal({{ $t->id }})" 
                                                    title="Edit Transaction"
                                                    class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)]">
                                                <x-icon name="sliders" class="w-3.5 h-3.5" />
                                            </button>
                                            <button wire:click="deleteTransaction({{ $t->id }})" 
                                                    wire:confirm="Remove this transaction permanently from your ledger?"
                                                    title="Delete Transaction"
                                                    class="p-1.5 rounded-[4px] border hairline-border hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[var(--text-muted)] hover:text-[var(--danger)]">
                                                <x-icon name="shield-alert" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="p-3 border-t hairline-border bg-[var(--bg-subtle)]/30">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Quick-Add / Edit Transaction Modal (~500px Desktop / Bottom Sheet Mobile) -->
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60"
                 x-data="{ 
                     openDatePicker: false,
                     init() {
                         this.$nextTick(() => {
                             const input = this.$refs.amountInput;
                             if (input) {
                                 input.focus();
                                 input.select();
                             }
                         });
                     }
                 }"
                 @keydown.escape.window="$wire.closeModal()"
                 @keydown.ctrl.enter.window="$wire.saveTransaction()"
                 @keydown.meta.enter.window="$wire.saveTransaction()">
                <div class="card-campus border hairline-border w-full sm:max-w-[500px] p-5 sm:p-6 bg-[var(--bg-surface)] shadow-2xl relative rounded-t-[16px] sm:rounded-[10px] rounded-b-none sm:rounded-b-[10px] max-h-[92vh] overflow-y-auto space-y-4"
                     @click.away="$wire.closeModal()">
                    
                    <!-- Clean Borderless Header -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-heading font-bold text-lg text-[var(--text-primary)] tracking-tight">
                                {{ $editingId ? 'Edit Ledger Entry' : 'Quick Add Transaction' }}
                            </h2>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-1 text-[11px] text-[var(--text-muted)] font-mono">
                                    <kbd class="px-1.5 py-0.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[10px] font-semibold text-[var(--text-primary)] shadow-2xs">Ctrl</kbd>
                                    <span>+</span>
                                    <kbd class="px-1.5 py-0.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[10px] font-semibold text-[var(--text-primary)] shadow-2xs">Enter</kbd>
                                    <span class="ml-0.5">to save</span>
                                </span>
                                <span class="text-[var(--text-muted)] text-[10px]">&bull;</span>
                                <span class="inline-flex items-center gap-1 text-[11px] text-[var(--text-muted)] font-mono">
                                    <kbd class="px-1.5 py-0.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[10px] font-semibold text-[var(--text-primary)] shadow-2xs">Esc</kbd>
                                    <span class="ml-0.5">to dismiss</span>
                                </span>
                            </div>
                        </div>

                        <button type="button" 
                                wire:click="closeModal" 
                                class="p-1.5 rounded-[6px] text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] transition-colors focus:outline-none"
                                aria-label="Close modal">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveTransaction" 
                          @keydown.ctrl.enter.prevent="$wire.saveTransaction()"
                          @keydown.meta.enter.prevent="$wire.saveTransaction()"
                          class="space-y-4">
                        
                        <!-- Quick Controls: Nature Switcher & Compact Date Badge -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <!-- Segmented Nature Switcher -->
                            <div class="inline-flex p-1 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border flex-1 max-w-[240px]">
                                <button type="button" 
                                        wire:click="$set('type', 'expense')"
                                        tabindex="7"
                                        class="flex-1 py-1 px-2.5 rounded-[4px] text-xs font-semibold transition-all duration-150 flex items-center justify-center gap-1.5 {{ $type === 'expense' ? 'bg-[var(--bg-surface)] text-rose-600 dark:text-rose-400 shadow-xs font-bold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                                    <span class="w-2 h-2 rounded-full {{ $type === 'expense' ? 'bg-rose-500' : 'bg-transparent border border-current' }}"></span>
                                    <span>Expense</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('type', 'income')"
                                        tabindex="7"
                                        class="flex-1 py-1 px-2.5 rounded-[4px] text-xs font-semibold transition-all duration-150 flex items-center justify-center gap-1.5 {{ $type === 'income' ? 'bg-[var(--bg-surface)] text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                                    <span class="w-2 h-2 rounded-full {{ $type === 'income' ? 'bg-emerald-500' : 'bg-transparent border border-current' }}"></span>
                                    <span>Income</span>
                                </button>
                            </div>

                            <!-- Date Badge Button with Popover -->
                            <div class="relative" x-data="{ openDatePicker: false }">
                                <button type="button" 
                                        @click="openDatePicker = !openDatePicker" 
                                        tabindex="8"
                                        title="Click to change date"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-subtle)] hover:bg-[var(--bg-surface)] text-[var(--text-primary)] transition-colors focus:outline-none focus:ring-1 focus:ring-[var(--accent-primary)] shadow-2xs">
                                    <span class="text-sm">📅</span>
                                    <span class="font-mono text-xs font-semibold">
                                        @if ($transaction_date === date('Y-m-d'))
                                            Today
                                        @else
                                            {{ !empty($transaction_date) ? date('M d', strtotime($transaction_date)) : 'Today' }}
                                        @endif
                                    </span>
                                    <x-icon name="filter" class="w-2.5 h-2.5 text-[var(--text-muted)] rotate-90 opacity-60 ml-0.5" />
                                </button>

                                <div x-show="openDatePicker" 
                                     @click.away="openDatePicker = false" 
                                     x-cloak 
                                     class="absolute right-0 mt-1 z-30 p-3 rounded-[8px] shadow-xl border hairline-border bg-[var(--bg-surface)] w-60 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)] font-semibold">Select Date</span>
                                        @if ($transaction_date !== date('Y-m-d'))
                                            <button type="button" 
                                                    wire:click="$set('transaction_date', '{{ date('Y-m-d') }}')" 
                                                    @click="openDatePicker = false"
                                                    class="text-[10px] font-mono text-[var(--accent-primary)] hover:underline">
                                                Reset to Today
                                            </button>
                                        @endif
                                    </div>
                                    <input wire:model.live="transaction_date" 
                                           type="date" 
                                           class="input-campus w-full text-xs font-mono py-1.5 px-2"
                                           @change="openDatePicker = false">
                                </div>
                            </div>
                        </div>

                        <!-- HERO Amount Field (PRIMARY FOCUS) -->
                        <div>
                            <div class="rounded-[8px] p-3.5 sm:p-4 bg-[var(--bg-subtle)]/70 border hairline-border focus-within:border-[var(--accent-primary)] focus-within:ring-2 focus-within:ring-[var(--accent-primary)]/20 transition-all">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="quick-add-amount" class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)] font-semibold">
                                        Amount <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[10px] font-mono text-[var(--text-muted)]">USD ($)</span>
                                </div>
                                <div class="relative flex items-center">
                                    <span class="text-3xl sm:text-4xl font-mono font-bold text-[var(--text-muted)] select-none mr-2">
                                        $
                                    </span>
                                    <input wire:model="amount" 
                                           id="quick-add-amount" 
                                           x-ref="amountInput"
                                           autofocus
                                           tabindex="1"
                                           type="number" 
                                           step="0.01" 
                                           min="0.01"
                                           max="999999.99"
                                           placeholder="0.00" 
                                           class="w-full bg-transparent text-3xl sm:text-4xl font-mono font-bold text-[var(--text-primary)] tabular-nums tracking-tight placeholder-[var(--text-muted)]/30 focus:outline-none border-none p-0">
                                </div>
                            </div>
                            @error('amount') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category Chips Selection (Horizontal, Clickable, No pre-fill) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)]">
                                    Category <span class="text-rose-500">*</span>
                                </label>
                                @if (!$category_id)
                                    <span class="text-[10px] font-mono text-[var(--danger)]">Select one</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Category Selection">
                                @forelse ($formCategories as $cat)
                                    <button type="button"
                                            wire:key="cat-chip-{{ $cat->id }}"
                                            wire:click="selectCategory({{ $cat->id }})"
                                            tabindex="2"
                                            class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs font-medium border transition-all duration-150 select-none focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[var(--accent-primary)] {{ $category_id == $cat->id ? 'shadow-xs font-semibold' : 'hover:border-slate-300 dark:hover:border-zinc-700 hover:bg-[var(--bg-surface)]' }}"
                                            style="{{ $category_id == $cat->id 
                                                ? 'background-color: ' . ($cat->color ?? 'var(--accent-primary)') . '; border-color: ' . ($cat->color ?? 'var(--accent-primary)') . '; color: #ffffff;' 
                                                : 'background-color: var(--bg-subtle); border-color: var(--border-hairline); color: var(--text-muted);' }}">
                                        <x-icon :name="$cat->icon" class="w-3.5 h-3.5 {{ $category_id == $cat->id ? 'text-white' : '' }}" />
                                        <span>{{ $cat->name }}</span>
                                    </button>
                                @empty
                                    <div class="text-xs text-[var(--text-muted)] italic py-1">
                                        No {{ $type }} categories found.
                                    </div>
                                @endforelse
                            </div>

                            @if ($activeSuggestion && (! $category_id || ! $manualCategorySelected))
                                <div class="mt-2.5 p-3 rounded-[6px] bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-xs flex items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-2 text-indigo-950 dark:text-indigo-200 min-w-0">
                                        <x-icon name="sparkles" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                                        <div class="truncate">
                                            <span class="font-medium">Suggested category: <strong class="text-indigo-700 dark:text-indigo-300">{{ $activeSuggestion['categoryName'] }}</strong></span>
                                            <span class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 ml-1">({{ round($activeSuggestion['confidence'] * 100) }}% confidence)</span>
                                            <div class="text-[10px] text-indigo-600/80 dark:text-indigo-400/80 truncate">{{ $activeSuggestion['explanation'] }}</div>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="acceptSuggestion" 
                                            class="px-2.5 py-1 text-xs font-semibold rounded-[4px] bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors shrink-0 flex items-center gap-1">
                                        <span>Accept</span>
                                        <x-icon name="check" class="w-3 h-3" />
                                    </button>
                                </div>
                            @endif

                            @error('category_id') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Smart Dynamic Merchant / Source Field -->
                        <div>
                            <label for="merchant" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                {{ $type === 'income' ? 'Where is this from?' : 'Where did you spend it?' }} <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model.live.debounce.300ms="merchant" 
                                   id="merchant" 
                                   tabindex="3"
                                   type="text" 
                                   placeholder="{{ $type === 'income' ? 'e.g. Monthly Allowance, Freelance' : 'e.g. Canteen, Bookstore' }}" 
                                   class="input-campus w-full text-sm py-2">
                            @error('merchant') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Collapsible + More options Details Accordion -->
                        <details class="group border hairline-border rounded-[6px] overflow-hidden"
                                 tabindex="6"
                                 {{ ($editingId && ($description || $is_recurring || ($payment_method && $payment_method !== 'card'))) ? 'open' : '' }}>
                            <summary class="flex items-center justify-between px-3.5 py-2.5 bg-[var(--bg-subtle)]/50 hover:bg-[var(--bg-subtle)] text-xs font-medium text-[var(--text-muted)] cursor-pointer select-none transition-colors">
                                <span class="font-mono text-xs">+ More options</span>
                                <x-icon name="sliders" class="w-3.5 h-3.5 text-[var(--text-muted)] group-open:rotate-180 transition-transform" />
                            </summary>

                            <div class="p-3.5 space-y-3.5 bg-[var(--bg-surface)] border-t hairline-border">
                                <!-- Payment Method / Channel -->
                                <div>
                                    <label for="payment_method" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                        Payment Method
                                    </label>
                                    <select wire:model="payment_method" 
                                            id="payment_method" 
                                            class="input-campus w-full text-xs bg-[var(--bg-surface)]">
                                        <option value="card">Card (Debit/Credit)</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="digital_wallet">Digital Wallet</option>
                                        <option value="upi">UPI</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('payment_method') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Notes / Description -->
                                <div>
                                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                        Notes / Description <span class="text-[10px] font-normal lowercase text-[var(--text-muted)]">(optional)</span>
                                    </label>
                                    <textarea wire:model.live.debounce.300ms="description" 
                                              id="description" 
                                              rows="2" 
                                              placeholder="Add context or notes for this transaction..." 
                                              class="input-campus w-full text-xs"></textarea>
                                    @error('description') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Recurring Entry -->
                                <div class="pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer text-xs text-[var(--text-muted)]">
                                        <input wire:model="is_recurring" 
                                               type="checkbox" 
                                               class="rounded-[4px] border-[var(--border-hairline)] text-[var(--accent-primary)] focus:ring-0">
                                        <span>Recurring entry (Repeats monthly, e.g. allowance or subscription)</span>
                                    </label>
                                </div>
                            </div>
                        </details>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" 
                                    wire:click="closeModal" 
                                    tabindex="5"
                                    class="btn-secondary py-2 px-3 text-xs">
                                Cancel
                            </button>
                            <button type="submit" 
                                    tabindex="4"
                                    class="btn-primary py-2 px-4 text-xs font-semibold shadow-xs">
                                <x-icon name="check-circle-2" class="w-4 h-4" />
                                <span>{{ $editingId ? 'Update Record' : 'Record Transaction' }}</span>
                                <kbd class="ml-1 px-1 py-0.5 rounded-[3px] bg-white/20 text-[10px] font-mono font-normal">↵</kbd>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- CSV Batch Categorization & Import Modal -->
        @if ($showImportModal)
            <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 dark:bg-black/70 backdrop-blur-xs transition-opacity duration-150"
                 x-data
                 @keydown.escape.window="$wire.closeImportModal()">
                <div class="card-campus border hairline-border w-full {{ $importStepReview ? 'sm:max-w-4xl' : 'sm:max-w-xl' }} p-5 sm:p-6 bg-[var(--bg-surface)] shadow-2xl relative rounded-t-[16px] sm:rounded-[10px] rounded-b-none sm:rounded-b-[10px] max-h-[92vh] flex flex-col space-y-4"
                     @click.away="$wire.closeImportModal()">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b hairline-border pb-3 shrink-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded-[6px] bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400">
                                    <x-icon name="sparkles" class="w-4 h-4" />
                                </span>
                                <h2 class="font-heading font-bold text-lg text-[var(--text-primary)] tracking-tight">
                                    {{ $importStepReview ? 'Review AI Batch Categorization' : 'Import Transactions via CSV' }}
                                </h2>
                            </div>
                            <p class="text-xs text-[var(--text-muted)] mt-1">
                                {{ $importStepReview 
                                    ? 'AI suggestions have been generated. Review and adjust categories before importing.' 
                                    : 'Upload a bank or ledger CSV file to automatically categorize transactions using AI.' }}
                            </p>
                        </div>
                        <button type="button" 
                                wire:click="closeImportModal" 
                                class="p-1.5 rounded-[6px] text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-subtle)] transition-colors focus:outline-none"
                                aria-label="Close modal">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Error Alert -->
                    @if ($importError)
                        <div class="p-3 rounded-[6px] bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2 shrink-0">
                            <x-icon name="alert-triangle" class="w-4 h-4 shrink-0" />
                            <span>{{ $importError }}</span>
                        </div>
                    @endif

                    @if (! $importStepReview)
                        <!-- STEP 1: UPLOAD FILE -->
                        <div class="space-y-4 py-2 overflow-y-auto">
                            <div class="border-2 border-dashed hairline-border rounded-[8px] p-6 text-center hover:bg-[var(--bg-subtle)]/50 transition-colors">
                                <div class="w-12 h-12 rounded-[8px] bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center mx-auto mb-3 text-indigo-600 dark:text-indigo-400">
                                    <x-icon name="upload" class="w-6 h-6" />
                                </div>
                                <label for="csv-file-upload" class="cursor-pointer">
                                    <span class="text-xs font-semibold text-[var(--accent-primary)] hover:underline">Choose CSV File</span>
                                    <span class="text-xs text-[var(--text-muted)]"> or drag and drop</span>
                                    <input id="csv-file-upload" 
                                           wire:model="csvFile" 
                                           type="file" 
                                           accept=".csv,text/csv" 
                                           class="sr-only">
                                </label>
                                <p class="text-[11px] text-[var(--text-muted)] mt-1">UTF-8 CSV format, up to 2MB (max 50 rows per batch)</p>

                                @if ($csvFile)
                                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] bg-[var(--bg-surface)] border hairline-border text-xs font-mono text-[var(--text-primary)]">
                                        <x-icon name="file-text" class="w-4 h-4 text-emerald-500" />
                                        <span>{{ $csvFile->getClientOriginalName() }}</span>
                                    </div>
                                @endif

                                <div wire:loading wire:target="csvFile" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 font-mono">
                                    Uploading file...
                                </div>
                            </div>

                            @error('csvFile') 
                                <span class="text-rose-600 dark:text-rose-400 text-xs block font-medium">{{ $message }}</span> 
                            @enderror

                            <!-- Expected Columns Hint -->
                            <div class="p-3.5 rounded-[8px] bg-[var(--bg-subtle)]/70 border hairline-border space-y-1.5 text-xs text-[var(--text-muted)]">
                                <div class="font-semibold text-[var(--text-primary)] flex items-center gap-1.5">
                                    <x-icon name="info" class="w-3.5 h-3.5 text-indigo-500" />
                                    <span>Supported CSV Columns</span>
                                </div>
                                <p class="text-[11px] leading-relaxed">
                                    Header matching is flexible. We look for: 
                                    <code class="font-mono text-[10px] px-1 py-0.5 rounded bg-[var(--bg-surface)] border hairline-border">Date</code>, 
                                    <code class="font-mono text-[10px] px-1 py-0.5 rounded bg-[var(--bg-surface)] border hairline-border">Description / Merchant</code>, 
                                    <code class="font-mono text-[10px] px-1 py-0.5 rounded bg-[var(--bg-surface)] border hairline-border">Amount</code>, and optionally 
                                    <code class="font-mono text-[10px] px-1 py-0.5 rounded bg-[var(--bg-surface)] border hairline-border">Type</code>.
                                </p>
                            </div>
                        </div>

                        <!-- Step 1 Actions -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t hairline-border shrink-0">
                            <button type="button" 
                                    wire:click="closeImportModal" 
                                    class="btn-secondary py-2 px-3 text-xs">
                                Cancel
                            </button>
                            <button type="button" 
                                    wire:click="processCsvUpload"
                                    wire:loading.attr="disabled"
                                    class="btn-primary py-2 px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5 {{ ! $csvFile ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <x-icon name="sparkles" class="w-4 h-4" />
                                <span wire:loading.remove wire:target="processCsvUpload">Categorize with AI</span>
                                <span wire:loading wire:target="processCsvUpload">Analyzing Batch...</span>
                            </button>
                        </div>
                    @else
                        <!-- STEP 2: REVIEW BATCH SUGGESTIONS -->
                        <div class="space-y-3 overflow-y-auto flex-1 max-h-[60vh] pr-1">
                            <div class="flex items-center justify-between text-xs text-[var(--text-muted)]">
                                <span>Parsed <strong>{{ count($importRows) }}</strong> transactions. AI suggestions are pre-filled below:</span>
                                <span class="font-mono text-[11px]">Valid rows: <strong class="text-emerald-600 dark:text-emerald-400">{{ count(array_filter($importRows, fn($r) => !empty($r['is_valid']))) }}</strong></span>
                            </div>

                            <div class="border hairline-border rounded-[8px] overflow-hidden bg-[var(--bg-surface)]">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead>
                                            <tr class="border-b hairline-border bg-[var(--bg-subtle)] text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                                                <th class="py-2.5 px-3">Date</th>
                                                <th class="py-2.5 px-3">Merchant / Description</th>
                                                <th class="py-2.5 px-3">Type</th>
                                                <th class="py-2.5 px-3 text-right">Amount</th>
                                                <th class="py-2.5 px-3">AI Suggestion</th>
                                                <th class="py-2.5 px-3 min-w-[180px]">Assigned Category</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y hairline-border">
                                            @foreach ($importRows as $idx => $row)
                                                <tr class="hover:bg-[var(--bg-subtle)]/40 transition-colors {{ ! $row['is_valid'] ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }}">
                                                    <td class="py-2.5 px-3 font-mono text-[11px] whitespace-nowrap text-[var(--text-muted)]">
                                                        {{ $row['date'] }}
                                                    </td>
                                                    <td class="py-2.5 px-3 font-medium text-[var(--text-primary)] max-w-[200px] truncate" title="{{ $row['description'] }}">
                                                        {{ $row['description'] }}
                                                        @if (! $row['is_valid'])
                                                            <div class="text-[10px] text-rose-500 font-sans mt-0.5">{{ $row['error'] }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-[4px] text-[10px] font-mono font-semibold {{ $row['type'] === 'income' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                                            {{ strtoupper($row['type']) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right font-mono font-semibold whitespace-nowrap text-[var(--text-primary)]">
                                                        ${{ $row['amount'] }}
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if ($row['suggested_category_id'])
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-[11px]">
                                                                <x-icon name="sparkles" class="w-3 h-3 text-indigo-500 shrink-0" />
                                                                <span class="font-medium truncate max-w-[100px]">{{ $row['suggested_category_name'] }}</span>
                                                                <span class="text-[10px] font-mono text-indigo-500 ml-0.5">({{ round(($row['confidence'] ?? 0) * 100) }}%)</span>
                                                            </div>
                                                        @else
                                                            <span class="text-[var(--text-muted)] text-[11px] italic">No match</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3">
                                                        <select wire:model="importRows.{{ $idx }}.selected_category_id"
                                                                class="input-campus w-full py-1 px-2 text-xs bg-[var(--bg-surface)]">
                                                            <option value="">-- Choose Category --</option>
                                                            @foreach ($categories->where('type', $row['type']) as $c)
                                                                <option value="{{ $c->id }}">
                                                                    {{ $c->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 Actions -->
                        <div class="flex items-center justify-between gap-2 pt-2 border-t hairline-border shrink-0">
                            <button type="button" 
                                    wire:click="$set('importStepReview', false)" 
                                    class="btn-secondary py-2 px-3 text-xs">
                                Back to Upload
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        wire:click="closeImportModal" 
                                        class="btn-secondary py-2 px-3 text-xs">
                                    Cancel
                                </button>
                                <button type="button" 
                                        wire:click="confirmImport"
                                        wire:loading.attr="disabled"
                                        class="btn-primary py-2 px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                                    <x-icon name="check-circle-2" class="w-4 h-4" />
                                    <span wire:loading.remove wire:target="confirmImport">Confirm & Import Transactions</span>
                                    <span wire:loading wire:target="confirmImport">Importing...</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
