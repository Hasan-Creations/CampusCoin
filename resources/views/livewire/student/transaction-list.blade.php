<div>
    <x-slot:header>
        Transactions
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Summary Banner & Quick-Add -->
        <div class="page-header flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] headline-rule">
                    Transaction History
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1.5">
                    Deterministic student financial cashbook with category accounting.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <x-button variant="secondary" wire:click="openImportModal" title="Upload CSV spreadsheet">
                    <x-icon name="download" class="w-4 h-4 rotate-180" />
                    <span>Import CSV</span>
                </x-button>
                <x-button variant="secondary" wire:click="exportCsv" title="Download transactions as CSV">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Export CSV</span>
                </x-button>
                <x-button variant="accent" wire:click="openCreateModal">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Add Transaction</span>
                </x-button>
            </div>
        </div>

        <!-- Feedback & Alerts -->
        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-xs text-[var(--accent)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)]" />
                    <span class="font-medium">{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-xs text-[var(--expense)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="shield-alert" class="w-4 h-4 text-[var(--expense)]" />
                    <span class="font-medium">{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon w-6 h-6 border-none">&times;</button>
            </div>
        @endif

        <!-- Filter Controls Toolbar -->
        <div class="bg-[var(--panel)] border hairline-border p-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search input -->
                <div class="lg:col-span-4 relative">
                    <span class="absolute left-3.5 top-3 text-[var(--muted)]">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input wire:model.live.debounce.250ms="search" 
                           type="text" 
                           aria-label="Search transactions by merchant or notes"
                           placeholder="Search merchant, notes..." 
                           class="field w-full pl-10 text-xs">
                </div>

                <!-- Type Selector Segmented Bar -->
                <div class="lg:col-span-3">
                    <div class="segmented-bar w-full" role="group" aria-label="Transaction type filter">
                        <button wire:click="$set('typeFilter', 'all')" 
                                aria-label="Show all transaction types"
                                class="segmented-item flex-1 text-center {{ $typeFilter === 'all' ? 'active' : '' }}">
                            All
                        </button>
                        <button wire:click="$set('typeFilter', 'expense')" 
                                aria-label="Show expenses only"
                                class="segmented-item flex-1 text-center {{ $typeFilter === 'expense' ? 'active' : '' }}">
                            Expense
                        </button>
                        <button wire:click="$set('typeFilter', 'income')" 
                                aria-label="Show income only"
                                class="segmented-item flex-1 text-center {{ $typeFilter === 'income' ? 'active' : '' }}">
                            Income
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="lg:col-span-3">
                    <select wire:model.live="categoryFilter" aria-label="Filter transactions by category" class="field w-full text-xs">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="lg:col-span-2">
                    <select wire:model.live="methodFilter" aria-label="Filter transactions by payment method" class="field w-full text-xs">
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
            <div class="border border-dashed hairline-border p-12 text-center space-y-3 bg-[var(--panel)]">
                @if ($totalCount === 0)
                    <!-- SRS Exact Empty State: NO TRANSACTIONS RECORDED YET -->
                    <h2 class="font-display text-base font-medium text-[var(--ink)] uppercase tracking-wide">
                        NO TRANSACTIONS RECORDED YET
                    </h2>
                    <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">
                        Start tracking your campus expenses to unlock insights.
                    </p>
                    <x-button variant="secondary" wire:click="openCreateModal" class="mt-2">
                        Add First Transaction
                    </x-button>
                @else
                    <h2 class="font-display text-base font-medium text-[var(--ink)] uppercase tracking-wide">
                        NO TRANSACTIONS MATCHING QUERY
                    </h2>
                    <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">
                        Clear active filters or modify search terms to view recorded entries.
                    </p>
                    <x-button variant="secondary" wire:click="$set('search', ''); $set('typeFilter', 'all'); $set('categoryFilter', ''); $set('methodFilter', '');" class="mt-2">
                        Clear All Filters
                    </x-button>
                @endif
            </div>
        @else
            <!-- Mobile view (< 640px) -->
            <div class="sm:hidden space-y-3">
                @foreach ($transactions as $t)
                    <div class="transaction-data bg-[var(--panel)] border hairline-border p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-xs text-[var(--ink)] truncate">
                                    {{ $t->merchant }}
                                    @if ($t->is_recurring)
                                        <span class="inline-block ml-1 text-[10px] font-mono text-[var(--secondary)]">
                                            [Recurring]
                                        </span>
                                    @endif
                                </div>
                                @if ($t->description)
                                    <div class="text-[11px] text-[var(--muted)] truncate mt-0.5">
                                        {{ $t->description }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right font-mono text-xs font-medium tabular-nums whitespace-nowrap {{ $t->isIncome() ? 'text-[var(--accent)]' : 'text-[var(--ink)]' }}">
                                {{ $t->formattedAmount() }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2.5 border-t hairline-border text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[11px] text-[var(--muted)]">
                                    {{ $t->transaction_date->format('M d, Y') }}
                                </span>
                                <span class="text-[var(--muted)]">&bull;</span>
                                @if ($t->category)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-[var(--ink)]">
                                        <x-icon :name="$t->category->icon" class="w-3 h-3 text-[var(--muted)]" />
                                        <span>{{ $t->category->name }}</span>
                                    </span>
                                @else
                                    <span class="text-[var(--muted)] text-[11px] italic">Uncategorized</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <x-badge :variant="$t->isIncome() ? 'income' : 'expense'">
                                    {{ $t->type }}
                                </x-badge>
                                <button wire:click="openEditModal({{ $t->id }})" 
                                        title="Edit Transaction"
                                        class="btn-icon w-6 h-6">
                                    <x-icon name="sliders" class="w-3 h-3" />
                                </button>
                                <button wire:click="deleteTransaction({{ $t->id }})" 
                                        wire:confirm="Remove this transaction permanently from your ledger?"
                                        title="Delete Transaction"
                                        class="btn-icon w-6 h-6 hover:text-[var(--expense)] hover:border-[var(--expense)]">
                                    <x-icon name="trash-2" class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop / Tablet Table (>= 640px) -->
            <div class="hidden sm:block">
                <x-ledger-table class="text-left">
                    <x-slot:head>
                        <tr>
                            <th scope="col" class="cursor-pointer hover:text-[var(--ink)] transition-colors select-none" wire:click="sortByColumn('transaction_date')" aria-sort="{{ $sortBy === 'transaction_date' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <div class="flex items-center gap-1.5">
                                    <span>Date</span>
                                    @if ($sortBy === 'transaction_date')
                                        <span class="text-[10px]" aria-hidden="true">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="cursor-pointer hover:text-[var(--ink)] transition-colors select-none" wire:click="sortByColumn('merchant')" aria-sort="{{ $sortBy === 'merchant' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <div class="flex items-center gap-1.5">
                                    <span>Merchant / Description</span>
                                    @if ($sortBy === 'merchant')
                                        <span class="text-[10px]" aria-hidden="true">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col">Category</th>
                            <th scope="col">Method</th>
                            <th scope="col" class="cursor-pointer hover:text-[var(--ink)] transition-colors select-none" wire:click="sortByColumn('type')" aria-sort="{{ $sortBy === 'type' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <div class="flex items-center gap-1.5">
                                    <span>Type</span>
                                    @if ($sortBy === 'type')
                                        <span class="text-[10px]" aria-hidden="true">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="text-right cursor-pointer hover:text-[var(--ink)] transition-colors select-none" wire:click="sortByColumn('amount')" aria-sort="{{ $sortBy === 'amount' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Amount</span>
                                    @if ($sortBy === 'amount')
                                        <span class="text-[10px]" aria-hidden="true">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($transactions as $t)
                        <tr class="table-row-tactile">
                            <!-- Date -->
                            <td class="font-mono text-xs tabular-nums text-[var(--muted)] whitespace-nowrap">
                                {{ $t->transaction_date->format('M d, Y') }}
                            </td>

                            <!-- Merchant / Description -->
                            <td>
                                <div class="font-medium text-xs text-[var(--ink)]">
                                    {{ $t->merchant }}
                                    @if ($t->is_recurring)
                                        <span class="inline-block ml-1 text-[10px] font-mono text-[var(--secondary)]">
                                            [Recurring]
                                        </span>
                                    @endif
                                </div>
                                @if ($t->description)
                                    <div class="text-[11px] text-[var(--muted)] truncate max-w-xs mt-0.5">
                                        {{ $t->description }}
                                    </div>
                                @endif
                            </td>

                            <!-- Category -->
                            <td class="whitespace-nowrap">
                                @if ($t->category)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-sans text-[var(--ink)]">
                                        <x-icon :name="$t->category->icon" class="w-3.5 h-3.5 text-[var(--muted)]" />
                                        <span>{{ $t->category->name }}</span>
                                    </span>
                                    @if ($t->ai_suggested)
                                        <span title="Categorized with AI Assistant"
                                              class="inline-flex items-center ml-1 px-1 py-0.5 border hairline-border text-[9px] font-caps text-[var(--accent)]">
                                            AI
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[var(--muted)] text-xs italic">Uncategorized</span>
                                @endif
                            </td>

                            <!-- Method -->
                            <td class="font-caps text-xs text-[var(--muted)] whitespace-nowrap">
                                {{ str_replace('_', ' ', $t->payment_method) }}
                            </td>

                            <!-- Type -->
                            <td class="whitespace-nowrap">
                                <x-badge :variant="$t->isIncome() ? 'income' : 'expense'">
                                    {{ $t->type }}
                                </x-badge>
                            </td>

                            <!-- Amount (Right-aligned IBM Plex Mono per §2) -->
                            <td class="text-right font-mono text-xs font-medium tabular-nums whitespace-nowrap {{ $t->isIncome() ? 'text-[var(--accent)]' : 'text-[var(--ink)]' }}">
                                {{ $t->formattedAmount() }}
                            </td>

                            <!-- Actions -->
                            <td class="text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openEditModal({{ $t->id }})" 
                                            title="Edit Transaction"
                                            class="btn-icon w-7 h-7">
                                        <x-icon name="sliders" class="w-3 h-3" />
                                    </button>
                                    <button wire:click="deleteTransaction({{ $t->id }})" 
                                            wire:confirm="Remove this transaction permanently from your ledger?"
                                            title="Delete Transaction"
                                            class="btn-icon w-7 h-7 hover:text-[var(--expense)] hover:border-[var(--expense)]">
                                        <x-icon name="trash-2" class="w-3 h-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ledger-table>
            </div>

            @if ($transactions->hasPages())
                <div class="p-4 border hairline-border bg-[var(--panel)]">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif

        <!-- Quick-Add / Edit Transaction Modal -->
        @if ($showModal)
            <x-modal :show="true" 
                     :title="$editingId ? 'Edit Ledger Entry' : 'New Ledger Entry'" 
                     titleId="modal-transaction-title" 
                     maxWidth="md" 
                     onClose="$wire.closeModal()">
                <div x-data="{ 
                         init() {
                             this.$nextTick(() => {
                                 const input = document.getElementById('quick-add-amount');
                                 if (input) {
                                     input.focus();
                                     input.select();
                                 }
                             });
                         }
                     }"
                     @keydown.ctrl.enter.window="$wire.saveTransaction()"
                     @keydown.meta.enter.window="$wire.saveTransaction()"
                     class="space-y-4">
                    
                    <p class="text-xs text-[var(--muted)] font-mono -mt-2 mb-2">
                        Ctrl+Enter to save &bull; Esc to dismiss
                    </p>

                    <form wire:submit.prevent="saveTransaction" 
                          @keydown.ctrl.enter.prevent="$wire.saveTransaction()"
                          @keydown.meta.enter.prevent="$wire.saveTransaction()"
                          class="space-y-4">
                        
                        <!-- Controls: Type Switcher & Date -->
                        <div class="flex items-center justify-between gap-3">
                            <!-- Type Switcher -->
                            <div class="segmented-bar flex-1 max-w-[200px]" role="group" aria-label="Entry nature">
                                <button type="button" 
                                        wire:click="$set('type', 'expense')"
                                        class="segmented-item flex-1 text-center {{ $type === 'expense' ? 'active' : '' }}">
                                    Expense
                                </button>
                                <button type="button" 
                                        wire:click="$set('type', 'income')"
                                        class="segmented-item flex-1 text-center {{ $type === 'income' ? 'active' : '' }}">
                                    Income
                                </button>
                            </div>

                            <!-- Date Picker -->
                            <div class="w-36">
                                <x-field wire:model.live="transaction_date" 
                                         type="date" 
                                         aria-label="Transaction Date"
                                         class="text-xs font-mono py-2 px-2" />
                            </div>
                        </div>

                        <!-- Amount Field (IBM Plex Mono, right-aligned per §4b) -->
                        <div>
                            <label for="quick-add-amount" class="block text-xs font-caps text-[var(--muted)] mb-1">
                                Amount (USD) <span class="text-[var(--expense)]">*</span>
                            </label>
                            <x-field wire:model="amount" 
                                     id="quick-add-amount" 
                                     type="number" 
                                     numeric
                                     step="0.01" 
                                     min="0.01" 
                                     max="999999.99" 
                                     placeholder="0.00" 
                                     class="text-2xl font-mono py-2 px-3"
                                     :hasError="$errors->has('amount')" />
                            @error('amount') <span class="text-[var(--expense)] text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category Selector -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-caps text-[var(--muted)]">
                                    Category <span class="text-[var(--expense)]">*</span>
                                </label>
                                @if (!$category_id)
                                    <span class="text-[10px] font-mono text-[var(--expense)]">Required</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-1.5" role="radiogroup" aria-label="Category Selection">
                                @forelse ($formCategories as $cat)
                                    <button type="button"
                                            wire:key="cat-chip-{{ $cat->id }}"
                                            wire:click="selectCategory({{ $cat->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-sans border transition-colors {{ $category_id == $cat->id ? 'bg-[var(--ink)] text-[var(--paper)] border-[var(--ink)] font-medium' : 'bg-[var(--panel)] text-[var(--ink)] border-[var(--hairline)] hover:border-[var(--muted)]' }}">
                                        <x-icon :name="$cat->icon" class="w-3.5 h-3.5" />
                                        <span>{{ $cat->name }}</span>
                                    </button>
                                @empty
                                    <div class="text-xs text-[var(--muted)] italic py-1">
                                        No {{ $type }} categories available.
                                    </div>
                                @endforelse
                            </div>

                            @if ($activeSuggestion && (! $category_id || ! $manualCategorySelected))
                                <div class="mt-2.5 p-3 border hairline-border bg-[var(--paper)] text-xs flex items-center justify-between gap-3">
                                    <div class="truncate">
                                        <span class="text-[var(--muted)]">Suggested:</span>
                                        <strong class="text-[var(--accent)] ml-1">{{ $activeSuggestion['categoryName'] }}</strong>
                                        <span class="font-mono text-[11px] text-[var(--muted)] ml-1">({{ round($activeSuggestion['confidence'] * 100) }}%)</span>
                                    </div>
                                    <x-button variant="secondary" type="button" wire:click="acceptSuggestion" class="py-1 px-3 text-xs">
                                        Accept
                                    </x-button>
                                </div>
                            @endif

                            @error('category_id') <span class="text-[var(--expense)] text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Merchant / Source -->
                        <div>
                            <label for="merchant" class="block text-xs font-caps text-[var(--muted)] mb-1">
                                {{ $type === 'income' ? 'Where is this from?' : 'Where did you spend it?' }} <span class="text-[var(--expense)]">*</span>
                            </label>
                            <x-field wire:model.live.debounce.300ms="merchant" 
                                     id="merchant" 
                                     type="text" 
                                     placeholder="{{ $type === 'income' ? 'e.g. Monthly Allowance, Freelance' : 'e.g. Canteen, Bookstore' }}" 
                                     class="text-xs"
                                     :hasError="$errors->has('merchant')" />
                            @error('merchant') <span class="text-[var(--expense)] text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-xs font-caps text-[var(--muted)] mb-1">
                                Payment Method
                            </label>
                            <x-field type="select" 
                                     wire:model="payment_method" 
                                     id="payment_method" 
                                     class="text-xs">
                                <option value="card">Card (Debit / Credit)</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="digital_wallet">Digital Wallet</option>
                                <option value="upi">UPI</option>
                                <option value="other">Other</option>
                            </x-field>
                        </div>

                        <!-- Description Notes -->
                        <div>
                            <label for="description" class="block text-xs font-caps text-[var(--muted)] mb-1">
                                Notes (Optional)
                            </label>
                            <x-field type="textarea" 
                                     wire:model.live.debounce.300ms="description" 
                                     id="description" 
                                     rows="2" 
                                     placeholder="Add transaction memo..." 
                                     class="text-xs"></x-field>
                        </div>

                        <!-- Recurring Checkbox -->
                        <div class="pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-[var(--muted)]">
                                <input wire:model="is_recurring" 
                                       type="checkbox" 
                                       class="border-[var(--hairline)] text-[var(--accent)] focus:ring-0">
                                <span>Recurring monthly ledger entry</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t hairline-border">
                            <x-button variant="secondary" type="button" wire:click="closeModal">
                                Cancel
                            </x-button>
                            <x-button variant="accent" type="submit">
                                {{ $editingId ? 'Update Record' : 'Record Entry' }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif

        <!-- CSV Batch Import Modal -->
        @if ($showImportModal)
            <x-modal :show="true"
                     :title="$importStepReview ? 'Review AI Batch Import' : 'Import CSV Ledger'"
                     titleId="modal-import-title"
                     :maxWidth="$importStepReview ? '4xl' : 'xl'"
                     onClose="$wire.closeImportModal()">
                <div class="space-y-6">
                    <p class="text-xs text-[var(--muted)] -mt-2 mb-4">
                        {{ $importStepReview 
                            ? 'Review and adjust pre-filled AI category classifications before confirming.' 
                            : 'Upload a CSV statement to parse and categorize entries automatically.' }}
                    </p>

                    @if ($importError)
                        <div class="p-3 border border-[var(--expense)] bg-[var(--paper)] text-[var(--expense)] text-xs flex items-center gap-2 shrink-0">
                            <x-icon name="alert-triangle" class="w-4 h-4 shrink-0" />
                            <span>{{ $importError }}</span>
                        </div>
                    @endif

                    @if (! $importStepReview)
                        <!-- STEP 1: UPLOAD FILE -->
                        <div class="space-y-4 py-2">
                            <div class="border border-dashed hairline-border p-8 text-center bg-[var(--paper)]">
                                <label for="csv-file-upload" class="cursor-pointer block space-y-2">
                                    <div class="text-xs font-medium text-[var(--accent)] hover:underline">Choose CSV File</div>
                                    <div class="text-xs text-[var(--muted)]">Standard UTF-8 format up to 2MB (max 50 rows)</div>
                                    <input id="csv-file-upload" 
                                           wire:model="csvFile" 
                                           type="file" 
                                           accept=".csv,text/csv" 
                                           class="sr-only">
                                </label>

                                @if ($csvFile)
                                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 border hairline-border bg-[var(--panel)] text-xs font-mono text-[var(--ink)]">
                                        <span>File: {{ $csvFile->getClientOriginalName() }}</span>
                                    </div>
                                @endif

                                <div wire:loading wire:target="csvFile" class="mt-2 text-xs text-[var(--accent)] font-mono animate-pulse">
                                    Uploading statement...
                                </div>
                            </div>

                            @error('csvFile') 
                                <span class="text-[var(--expense)] text-xs block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Step 1 Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t hairline-border shrink-0">
                            <x-button variant="secondary" type="button" wire:click="closeImportModal">
                                Cancel
                            </x-button>
                            <x-button variant="accent" type="button" wire:click="processCsvUpload" wire:loading.attr="disabled" :disabled="!$csvFile">
                                <span wire:loading.remove wire:target="processCsvUpload">Categorize with AI</span>
                                <span wire:loading wire:target="processCsvUpload">Analyzing Batch...</span>
                            </x-button>
                        </div>
                    @else
                        <!-- STEP 2: REVIEW BATCH SUGGESTIONS -->
                        <div class="space-y-4 overflow-y-auto flex-1 max-h-[60vh]">
                            <div class="ledger-table-frame border hairline-border overflow-x-auto">
                                <table class="ledger-table w-full text-xs text-left">
                                    <thead>
                                        <tr class="bg-[var(--panel)]">
                                            <th>Date</th>
                                            <th>Merchant / Description</th>
                                            <th>Type</th>
                                            <th class="text-right">Amount</th>
                                            <th>AI Suggestion</th>
                                            <th>Assigned Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($importRows as $idx => $row)
                                            <tr class="table-row-tactile">
                                                <td class="font-mono text-xs whitespace-nowrap text-[var(--muted)]">
                                                    {{ $row['date'] }}
                                                </td>
                                                <td class="font-medium text-[var(--ink)] max-w-[200px] truncate">
                                                    {{ $row['description'] }}
                                                    @if (! $row['is_valid'])
                                                        <div class="text-[10px] text-[var(--expense)]">{{ $row['error'] }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <x-badge :variant="$row['type'] === 'income' ? 'income' : 'expense'">
                                                        {{ strtoupper($row['type']) }}
                                                    </x-badge>
                                                </td>
                                                <td class="text-right font-mono text-xs tabular-nums text-[var(--ink)]">
                                                    ${{ $row['amount'] }}
                                                </td>
                                                <td class="whitespace-nowrap text-xs">
                                                    @if ($row['suggested_category_id'])
                                                        <span class="text-[var(--accent)] font-medium">{{ $row['suggested_category_name'] }}</span>
                                                    @else
                                                        <span class="text-[var(--muted)] italic">No match</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <x-field type="select" 
                                                             wire:model="importRows.{{ $idx }}.selected_category_id"
                                                             class="py-1 px-2 text-xs">
                                                        <option value="">-- Choose Category --</option>
                                                        @foreach ($categories->where('type', $row['type']) as $c)
                                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </x-field>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Step 2 Actions -->
                        <div class="flex items-center justify-between gap-3 pt-4 border-t hairline-border shrink-0">
                            <x-button variant="secondary" type="button" wire:click="$set('importStepReview', false)">
                                Back
                            </x-button>
                            <div class="flex items-center gap-3">
                                <x-button variant="secondary" type="button" wire:click="closeImportModal">
                                    Cancel
                                </x-button>
                                <x-button variant="accent" type="button" wire:click="confirmImport" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="confirmImport">Import Transactions</span>
                                    <span wire:loading wire:target="confirmImport">Importing...</span>
                                </x-button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-modal>
        @endif
    </div>
</div>
