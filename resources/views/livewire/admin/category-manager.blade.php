<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 rounded-[6px] border hairline-border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 rounded-[6px] border hairline-border bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="alert-triangle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="text-amber-600 hover:text-amber-800 dark:hover:text-amber-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-[var(--text-primary)]">Global Category Administration</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1">Configure system-wide default categories, monitor custom student categories, and control activation state</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" class="px-3.5 py-2 rounded-[6px] text-xs font-medium bg-[var(--accent-primary)] hover:bg-[var(--accent-hover)] text-white transition-colors inline-flex items-center gap-2 shadow-sm">
                <x-icon name="plus" class="w-3.5 h-3.5 text-white" />
                New Global Category
            </button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-surface)]">
            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Global System Defaults</div>
            <div class="font-mono text-xl font-bold text-[var(--text-primary)] tabular-nums mt-0.5">{{ $globalCount }}</div>
        </div>
        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-surface)]">
            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Student Personal Categories</div>
            <div class="font-mono text-xl font-bold text-[var(--text-primary)] tabular-nums mt-0.5">{{ $personalCount }}</div>
        </div>
        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-surface)]">
            <div class="text-[10px] font-mono uppercase text-emerald-600 dark:text-emerald-400">Active / Selectable</div>
            <div class="font-mono text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums mt-0.5">{{ $activeCount }}</div>
        </div>
        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-surface)]">
            <div class="text-[10px] font-mono uppercase text-rose-600 dark:text-rose-400">Deactivated / Archived</div>
            <div class="font-mono text-xl font-bold text-rose-600 dark:text-rose-400 tabular-nums mt-0.5">{{ $inactiveCount }}</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card-campus border hairline-border p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Scope Segmented Tabs -->
            <div class="inline-flex p-1 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border" role="group" aria-label="Category scope filter">
                <button wire:click="$set('filterScope', 'global')" 
                        aria-label="Show global defaults only"
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterScope === 'global' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Global Defaults ({{ $globalCount }})
                </button>
                <button wire:click="$set('filterScope', 'personal')" 
                        aria-label="Show student custom categories only"
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterScope === 'personal' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Student Custom ({{ $personalCount }})
                </button>
                <button wire:click="$set('filterScope', 'all')" 
                        aria-label="Show all categories"
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterScope === 'all' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    All Categories ({{ $globalCount + $personalCount }})
                </button>
            </div>

            <!-- Type, Status, & Search Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterType" aria-label="Filter by cash-flow type" class="px-2.5 py-1.5 rounded-[6px] text-xs border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)]">
                    <option value="all">All Types</option>
                    <option value="expense">Expenses Only</option>
                    <option value="income">Income Only</option>
                </select>

                <select wire:model.live="filterStatus" aria-label="Filter by operational status" class="px-2.5 py-1.5 rounded-[6px] text-xs border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)]">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Deactivated Only</option>
                </select>

                <div class="relative min-w-[200px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           aria-label="Search categories by name"
                           placeholder="Search categories..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-[6px] border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-hidden focus:ring-1 focus:ring-[var(--accent-primary)]" />
                </div>
            </div>
        </div>
    </div>

    <!-- Category Table -->
    <div class="card-campus border hairline-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
                    <tr>
                        <th scope="col" class="p-3">Category Title</th>
                        <th scope="col" class="p-3">Cash-Flow Type</th>
                        <th scope="col" class="p-3">Scope / Ownership</th>
                        <th scope="col" class="p-3 text-center">Transactions</th>
                        <th scope="col" class="p-3 text-center">Budgets</th>
                        <th scope="col" class="p-3">Operational Status</th>
                        <th scope="col" class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y hairline-border">
                    @forelse($categories as $category)
                        <tr class="hover:bg-[var(--bg-subtle)]/50 transition-colors {{ ! $category->is_active ? 'opacity-65' : '' }}">
                            <td class="p-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-[4px] flex items-center justify-center shrink-0" style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                                        <x-icon :name="$category->icon" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-[var(--text-primary)] flex items-center gap-1.5">
                                            <span>{{ $category->name }}</span>
                                            @if(! $category->is_active)
                                                <span class="px-1.5 py-0.2 rounded-[3px] text-[10px] font-mono bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="font-mono text-[10px] text-[var(--text-muted)] uppercase tracking-wider">Icon: {{ $category->icon }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="badge-campus {{ $category->type === 'income' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400' }}">
                                    {{ ucfirst($category->type) }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($category->isDefault())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                        <x-icon name="shield-check" class="w-3 h-3 text-sky-600 dark:text-sky-400" />
                                        Global Default
                                    </span>
                                @else
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                            <x-icon name="users" class="w-3 h-3" />
                                            Student Custom
                                        </span>
                                        <div class="text-[10px] text-[var(--text-muted)] mt-0.5">{{ $category->user?->name ?? 'User #' . $category->user_id }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 font-mono tabular-nums text-center text-[var(--text-muted)]">
                                {{ number_format($category->transactions_count) }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-center text-[var(--text-muted)]">
                                {{ number_format($category->budgets_count) }}
                            </td>
                            <td class="p-3">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Deactivated
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit Button (for Global Categories) -->
                                    <button wire:click="openEditModal({{ $category->id }})" 
                                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors"
                                            title="Edit Category">
                                        <x-icon name="edit" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Status Toggle Button -->
                                    <button wire:click="toggleCategoryStatus({{ $category->id }})" 
                                            class="px-2 py-1 rounded-[4px] text-[11px] font-mono border hairline-border transition-colors {{ $category->is_active ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}"
                                            title="{{ $category->is_active ? 'Deactivate category (preserve historical records)' : 'Activate category for student use' }}">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

                                    <!-- Safe Delete Button -->
                                    <button wire:click="deleteCategory({{ $category->id }})" 
                                            wire:confirm="Attempt to delete category '{{ $category->name }}'? If it is referenced by existing transactions or budgets, deletion will be safely rejected."
                                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[var(--text-muted)] hover:text-rose-600 transition-colors"
                                            title="Delete (Safely checked)">
                                        <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-xs text-[var(--text-muted)]">
                                No categories match the specified scope, type, or search query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Edit Category Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
             role="dialog"
             aria-modal="true"
             aria-labelledby="admin-category-modal-title"
             x-data
             @keydown.escape.window="$wire.closeModal()">
            <div class="w-full max-w-md rounded-[8px] border hairline-border bg-[var(--bg-surface)] p-6 space-y-4 shadow-xl"
                 @click.away="$wire.closeModal()">
                <div class="flex items-center justify-between pb-3 border-b hairline-border">
                    <h3 id="admin-category-modal-title" class="font-heading text-base font-bold text-[var(--text-primary)]">
                        {{ $editingId ? 'Edit Category' : 'Create Global Default Category' }}
                    </h3>
                    <button wire:click="closeModal" aria-label="Close modal" class="p-1 rounded-[4px] hover:bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <form wire:submit="saveCategory" class="space-y-4 text-xs">
                    <!-- Category Name -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1">Category Name</label>
                        <input type="text" 
                               wire:model="name" 
                               placeholder="e.g. Dining, Textbooks, Grants"
                               class="w-full px-3 py-2 rounded-[6px] border hairline-border bg-[var(--bg-canvas)] text-[var(--text-primary)] focus:outline-hidden focus:ring-1 focus:ring-[var(--accent-primary)]" />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Category Type -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1">Cash-Flow Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-[6px] border hairline-border cursor-pointer {{ $type === 'expense' ? 'bg-rose-50/50 border-rose-300 dark:bg-rose-950/20' : 'bg-[var(--bg-canvas)]' }}">
                                <input type="radio" wire:model="type" value="expense" class="text-rose-600" />
                                <div>
                                    <div class="font-medium text-[var(--text-primary)]">Expense</div>
                                    <div class="text-[10px] text-[var(--text-muted)]">Outflow / Spending</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[6px] border hairline-border cursor-pointer {{ $type === 'income' ? 'bg-emerald-50/50 border-emerald-300 dark:bg-emerald-950/20' : 'bg-[var(--bg-canvas)]' }}">
                                <input type="radio" wire:model="type" value="income" class="text-emerald-600" />
                                <div>
                                    <div class="font-medium text-[var(--text-primary)]">Income</div>
                                    <div class="text-[10px] text-[var(--text-muted)]">Inflow / Earnings</div>
                                </div>
                            </label>
                        </div>
                        @error('type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Icon Selector -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1">Icon Representation</label>
                        <div class="grid grid-cols-6 gap-2" role="group" aria-label="Icon representation">
                            @foreach ($availableIcons as $iconKey => $iconLabel)
                                <button type="button" 
                                        wire:click="$set('icon', '{{ $iconKey }}')"
                                        aria-label="Select icon {{ $iconLabel }}"
                                        class="p-2 rounded-[6px] border hairline-border flex items-center justify-center transition-colors {{ $icon === $iconKey ? 'border-[var(--accent-primary)] bg-[var(--accent-primary)]/10 text-[var(--accent-primary)]' : 'bg-[var(--bg-canvas)] text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}"
                                        title="{{ $iconLabel }}">
                                    <x-icon :name="$iconKey" class="w-4 h-4" />
                                </button>
                            @endforeach
                        </div>
                        @error('icon') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1">Category Hex Accent</label>
                        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Category color accent">
                            @foreach ($availableColors as $hex => $label)
                                <button type="button" 
                                        wire:click="$set('color', '{{ $hex }}')"
                                        aria-label="Select color {{ $label }}"
                                        class="w-6 h-6 rounded-full border-2 transition-transform {{ $color === $hex ? 'scale-125 border-[var(--text-primary)]' : 'border-transparent hover:scale-110' }}"
                                        style="background-color: {{ $hex }}"
                                        title="{{ $label }}">
                                </button>
                            @endforeach
                        </div>
                        @error('color') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($editingId)
                        <!-- Active Status Toggle in Edit Modal -->
                        <div class="pt-2 border-t hairline-border">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded-[3px] border hairline-border text-[var(--accent-primary)]" />
                                <div>
                                    <div class="font-medium text-[var(--text-primary)]">Category Active & Selectable</div>
                                    <div class="text-[10px] text-[var(--text-muted)]">Unchecking deactivates this category without affecting historical transactions</div>
                                </div>
                            </label>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-3 border-t hairline-border">
                        <button type="button" wire:click="closeModal" class="px-3.5 py-1.5 rounded-[6px] border hairline-border text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[var(--accent-primary)] hover:bg-[var(--accent-hover)] text-white font-medium transition-colors shadow-sm">
                            {{ $editingId ? 'Save Changes' : 'Create Global Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
