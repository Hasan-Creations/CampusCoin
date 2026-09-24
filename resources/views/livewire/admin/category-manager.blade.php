<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 rounded-[10px] border hairline-border bg-[var(--success-tint)] text-[var(--success)] text-xs flex items-center justify-between shadow-tactile-sm">
            <div class="flex items-center gap-2.5">
                <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--success)] shrink-0" />
                <span class="font-medium">{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon !w-6 !h-6 text-[var(--success)] hover:bg-[var(--success-hover)] hover:text-white transition-colors">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 rounded-[10px] border hairline-border bg-[var(--danger-tint)] text-[var(--danger)] text-xs flex items-center justify-between shadow-tactile-sm">
            <div class="flex items-center gap-2.5">
                <x-icon name="alert-triangle" class="w-4 h-4 text-[var(--danger)] shrink-0" />
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon !w-6 !h-6 text-[var(--danger)] hover:bg-[var(--danger-hover)] hover:text-white transition-colors">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[var(--text-muted)] font-mono">Taxonomy Governance</span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-bold tracking-tight text-[var(--text-primary)] mt-1">Global Category Administration</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">Configure system-wide default categories, monitor custom student categories, and control activation state</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" class="btn-primary !text-xs !min-h-[38px] !py-2 !px-3.5 inline-flex items-center gap-2">
                <x-icon name="plus" class="w-3.5 h-3.5 text-white" />
                New Global Category
            </button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-campus p-4 space-y-1">
            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Global System Defaults</div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">{{ $globalCount }}</div>
        </div>
        <div class="card-campus p-4 space-y-1">
            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Student Personal Categories</div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">{{ $personalCount }}</div>
        </div>
        <div class="card-campus p-4 space-y-1">
            <div class="text-[10px] font-mono uppercase text-[var(--success)]">Active / Selectable</div>
            <div class="font-mono text-2xl font-bold text-[var(--success)] tabular-nums">{{ $activeCount }}</div>
        </div>
        <div class="card-campus p-4 space-y-1">
            <div class="text-[10px] font-mono uppercase text-[var(--danger)]">Deactivated / Archived</div>
            <div class="font-mono text-2xl font-bold text-[var(--danger)] tabular-nums">{{ $inactiveCount }}</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card-campus p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Scope Segmented Tabs -->
            <div class="segmented-bar" role="group" aria-label="Category scope filter">
                <button wire:click="$set('filterScope', 'global')" 
                        aria-label="Show global defaults only"
                        class="segmented-item {{ $filterScope === 'global' ? 'active' : '' }}">
                    Global Defaults ({{ $globalCount }})
                </button>
                <button wire:click="$set('filterScope', 'personal')" 
                        aria-label="Show student custom categories only"
                        class="segmented-item {{ $filterScope === 'personal' ? 'active' : '' }}">
                    Student Custom ({{ $personalCount }})
                </button>
                <button wire:click="$set('filterScope', 'all')" 
                        aria-label="Show all categories"
                        class="segmented-item {{ $filterScope === 'all' ? 'active' : '' }}">
                    All Categories ({{ $globalCount + $personalCount }})
                </button>
            </div>

            <!-- Type, Status, & Search Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterType" aria-label="Filter by cash-flow type" class="input-campus !min-h-[38px] !py-1.5 !px-3 text-xs">
                    <option value="all">All Types</option>
                    <option value="expense">Expenses Only</option>
                    <option value="income">Income Only</option>
                </select>

                <select wire:model.live="filterStatus" aria-label="Filter by operational status" class="input-campus !min-h-[38px] !py-1.5 !px-3 text-xs">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Deactivated Only</option>
                </select>

                <div class="relative min-w-[220px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           aria-label="Search categories by name"
                           placeholder="Search categories..." 
                           class="input-campus !min-h-[38px] !py-1.5 !pl-9 !pr-3 text-xs w-full" />
                </div>
            </div>
        </div>
    </div>

    <!-- Category Table -->
    <div class="card-campus p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
                    <tr>
                        <th scope="col" class="p-3.5">Category Title</th>
                        <th scope="col" class="p-3.5">Cash-Flow Type</th>
                        <th scope="col" class="p-3.5">Scope / Ownership</th>
                        <th scope="col" class="p-3.5 text-center">Transactions</th>
                        <th scope="col" class="p-3.5 text-center">Budgets</th>
                        <th scope="col" class="p-3.5">Operational Status</th>
                        <th scope="col" class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y hairline-border bg-[var(--bg-surface)]">
                    @forelse($categories as $category)
                        <tr class="table-row-tactile {{ ! $category->is_active ? 'opacity-65' : '' }}">
                            <td class="p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[8px] flex items-center justify-center shrink-0 border hairline-border shadow-xs" style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                                        <x-icon :name="$category->icon" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-[var(--text-primary)] flex items-center gap-1.5 text-sm">
                                            <span>{{ $category->name }}</span>
                                            @if(! $category->is_active)
                                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--danger-tint)] text-[var(--danger)] font-medium">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="font-mono text-[10px] text-[var(--text-muted)] uppercase tracking-wider">Icon: {{ $category->icon }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5">
                                <span class="badge-campus {{ $category->type === 'income' ? 'bg-[var(--success-tint)] text-[var(--success)]' : 'bg-[var(--danger-tint)] text-[var(--danger)]' }}">
                                    {{ ucfirst($category->type) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                @if($category->isDefault())
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--bg-subtle)] text-[var(--accent-primary)] font-medium border hairline-border">
                                        <x-icon name="shield-check" class="w-3 h-3 text-[var(--accent-primary)]" />
                                        Global Default
                                    </span>
                                @else
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--bg-subtle)] text-[var(--text-primary)] font-medium border hairline-border">
                                            <x-icon name="users" class="w-3 h-3" />
                                            Student Custom
                                        </span>
                                        <div class="text-[10px] text-[var(--text-muted)] mt-0.5">{{ $category->user?->name ?? 'User #' . $category->user_id }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono tabular-nums text-center text-[var(--text-muted)] font-medium">
                                {{ number_format($category->transactions_count) }}
                            </td>
                            <td class="p-3.5 font-mono tabular-nums text-center text-[var(--text-muted)] font-medium">
                                {{ number_format($category->budgets_count) }}
                            </td>
                            <td class="p-3.5">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--success-tint)] text-[var(--success)] font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--success)]"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--bg-subtle)] text-[var(--text-muted)] font-medium border hairline-border">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--text-muted)]"></span>
                                        Deactivated
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit Button (for Global Categories) -->
                                    <button wire:click="openEditModal({{ $category->id }})" 
                                            class="btn-icon !w-7 !h-7"
                                            title="Edit Category">
                                        <x-icon name="edit" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Status Toggle Button -->
                                    <button wire:click="toggleCategoryStatus({{ $category->id }})" 
                                            class="px-2.5 py-1 rounded-[6px] text-[11px] font-mono border hairline-border transition-colors {{ $category->is_active ? 'text-[var(--danger)] hover:bg-[var(--danger-tint)]' : 'text-[var(--success)] hover:bg-[var(--success-tint)]' }}"
                                            title="{{ $category->is_active ? 'Deactivate category (preserve historical records)' : 'Activate category for student use' }}">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

                                    <!-- Safe Delete Button -->
                                    <button wire:click="deleteCategory({{ $category->id }})" 
                                            wire:confirm="Attempt to delete category '{{ $category->name }}'? If it is referenced by existing transactions or budgets, deletion will be safely rejected."
                                            class="btn-icon !w-7 !h-7 text-[var(--danger)] hover:bg-[var(--danger-tint)]"
                                            title="Delete (Safely checked)">
                                        <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-xs text-[var(--text-muted)]">
                                No categories match the specified scope, type, or search query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Edit Category Modal (Solid Physical Geometry, NO BACKDROP BLUR) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/55"
             role="dialog"
             aria-modal="true"
             aria-labelledby="admin-category-modal-title"
             x-data
             @keydown.escape.window="$wire.closeModal()">
            <div class="modal-dialog-surface w-full max-w-md rounded-[22px] border hairline-border bg-[var(--bg-surface-elevated)] p-6 space-y-5 shadow-modal"
                 @click.away="$wire.closeModal()">
                <div class="flex items-center justify-between pb-3 border-b hairline-border">
                    <h3 id="admin-category-modal-title" class="font-heading text-lg font-bold text-[var(--text-primary)]">
                        {{ $editingId ? 'Edit Category' : 'Create Global Default Category' }}
                    </h3>
                    <button wire:click="closeModal" aria-label="Close modal" class="btn-icon !w-8 !h-8">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <form wire:submit="saveCategory" class="space-y-4 text-xs">
                    <!-- Category Name -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1.5">Category Name</label>
                        <input type="text" 
                               wire:model="name" 
                               placeholder="e.g. Dining, Textbooks, Grants"
                               class="input-campus w-full" />
                        @error('name') <span class="text-[var(--danger)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Category Type -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1.5">Cash-Flow Type</label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label class="flex items-center gap-2.5 p-3 rounded-[10px] border hairline-border cursor-pointer transition-colors {{ $type === 'expense' ? 'bg-[var(--danger-tint)] border-[var(--danger)] font-semibold' : 'bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)]' }}">
                                <input type="radio" wire:model="type" value="expense" class="accent-[var(--danger)]" />
                                <div>
                                    <div class="text-[var(--text-primary)] font-medium">Expense</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-normal">Outflow / Spending</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-[10px] border hairline-border cursor-pointer transition-colors {{ $type === 'income' ? 'bg-[var(--success-tint)] border-[var(--success)] font-semibold' : 'bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)]' }}">
                                <input type="radio" wire:model="type" value="income" class="accent-[var(--success)]" />
                                <div>
                                    <div class="text-[var(--text-primary)] font-medium">Income</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-normal">Inflow / Earnings</div>
                                </div>
                            </label>
                        </div>
                        @error('type') <span class="text-[var(--danger)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Icon Selector -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1.5">Icon Representation</label>
                        <div class="grid grid-cols-6 gap-2" role="group" aria-label="Icon representation">
                            @foreach ($availableIcons as $iconKey => $iconLabel)
                                <button type="button" 
                                        wire:click="$set('icon', '{{ $iconKey }}')"
                                        aria-label="Select icon {{ $iconLabel }}"
                                        class="p-2.5 rounded-[8px] border hairline-border flex items-center justify-center transition-colors {{ $icon === $iconKey ? 'border-[var(--accent-primary)] bg-[var(--accent-tint)] text-[var(--accent-primary)] ring-1 ring-[var(--accent-primary)]' : 'bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] hover:border-[var(--border-strong)]' }}"
                                        title="{{ $iconLabel }}">
                                    <x-icon :name="$iconKey" class="w-4 h-4" />
                                </button>
                            @endforeach
                        </div>
                        @error('icon') <span class="text-[var(--danger)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <label class="block font-medium text-[var(--text-primary)] mb-1.5">Category Color Swatch</label>
                        <div class="flex flex-wrap items-center gap-2.5" role="group" aria-label="Category color accent">
                            @foreach ($availableColors as $hex => $label)
                                <button type="button" 
                                        wire:click="$set('color', '{{ $hex }}')"
                                        aria-label="Select color {{ $label }}"
                                        class="w-7 h-7 rounded-full border-2 transition-transform {{ $color === $hex ? 'scale-125 border-[var(--text-primary)] shadow-tactile-sm' : 'border-transparent hover:scale-110' }}"
                                        style="background-color: {{ $hex }}"
                                        title="{{ $label }}">
                                </button>
                            @endforeach
                        </div>
                        @error('color') <span class="text-[var(--danger)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    @if ($editingId)
                        <!-- Active Status Toggle in Edit Modal -->
                        <div class="pt-2 border-t hairline-border">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded-[4px] border hairline-border text-[var(--accent-primary)] accent-[var(--accent-primary)]" />
                                <div>
                                    <div class="font-medium text-[var(--text-primary)]">Category Active & Selectable</div>
                                    <div class="text-[10px] text-[var(--text-muted)]">Unchecking deactivates this category without affecting historical transactions</div>
                                </div>
                            </label>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t hairline-border">
                        <button type="button" wire:click="closeModal" class="btn-secondary !text-xs !min-h-[38px] !py-2 !px-4">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary !text-xs !min-h-[38px] !py-2 !px-4">
                            {{ $editingId ? 'Save Changes' : 'Create Global Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
