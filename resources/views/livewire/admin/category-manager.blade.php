<div class="space-y-6">
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-[var(--accent)] text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)] shrink-0" />
                <span class="font-medium">{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-[var(--expense)] text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-icon name="shield-alert" class="w-4 h-4 text-[var(--expense)] shrink-0" />
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon w-6 h-6 border-none">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[var(--muted)] font-mono">Taxonomy Governance</span>
            </div>
            <h1 class="font-display text-2xl sm:text-3xl font-medium tracking-tight text-[var(--ink)] mt-1 headline-rule">Global Category Administration</h1>
            <p class="text-xs sm:text-sm text-[var(--muted)] mt-1">Configure system-wide default categories, monitor custom student categories, and control activation state</p>
        </div>
        <div class="flex items-center gap-3">
            <x-button variant="accent" wire:click="openCreateModal">
                <x-icon name="plus" class="w-3.5 h-3.5" />
                <span>New Global Category</span>
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">Global System Defaults</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">{{ $globalCount }}</div>
        </div>

        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">Student Personal Categories</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">{{ $personalCount }}</div>
        </div>

        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--accent)]">Active / Selectable</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--accent)] tabular-nums tracking-tight">{{ $activeCount }}</div>
        </div>

        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--expense)]">Deactivated / Archived</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--expense)] tabular-nums tracking-tight">{{ $inactiveCount }}</div>
        </div>
    </div>

    <div class="card-campus p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
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

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterType" aria-label="Filter by cash-flow type" class="field !min-h-[38px] !py-1.5 !px-3 text-xs w-auto">
                    <option value="all">All Types</option>
                    <option value="expense">Expenses Only</option>
                    <option value="income">Income Only</option>
                </select>

                <select wire:model.live="filterStatus" aria-label="Filter by operational status" class="field !min-h-[38px] !py-1.5 !px-3 text-xs w-auto">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Deactivated Only</option>
                </select>

                <div class="relative min-w-[220px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)] pointer-events-none" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           aria-label="Search categories by name"
                           placeholder="Search categories..." 
                           class="field !min-h-[38px] !py-1.5 !pl-9 !pr-3 text-xs w-full" />
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto border hairline-border">
        <table class="ledger-table w-full text-xs text-left">
            <thead>
                <tr>
                    <th scope="col">Category Title</th>
                    <th scope="col">Cash-Flow Type</th>
                    <th scope="col">Scope / Ownership</th>
                    <th scope="col" class="text-center">Transactions</th>
                    <th scope="col" class="text-center">Budgets</th>
                    <th scope="col">Operational Status</th>
                    <th scope="col" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="table-row-tactile {{ ! $category->is_active ? 'opacity-65' : '' }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 flex items-center justify-center shrink-0 border hairline-border" style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                                    <x-icon :name="$category->icon" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-medium text-[var(--ink)] flex items-center gap-1.5 text-sm">
                                        <span>{{ $category->name }}</span>
                                        @if(! $category->is_active)
                                            <span class="px-1.5 py-0.5 text-[10px] font-caps border hairline-border text-[var(--expense)]">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="font-mono text-[10px] text-[var(--muted)] uppercase tracking-wider">Icon: {{ $category->icon }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-badge :variant="$category->type === 'income' ? 'income' : 'expense'">
                                {{ ucfirst($category->type) }}
                            </x-badge>
                        </td>
                        <td>
                            @if($category->isDefault())
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono border hairline-border bg-[var(--paper)] text-[var(--accent)] font-medium">
                                    <x-icon name="shield-check" class="w-3 h-3 text-[var(--accent)]" />
                                    Global Default
                                </span>
                            @else
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono border hairline-border bg-[var(--paper)] text-[var(--ink)] font-medium">
                                        <x-icon name="users" class="w-3 h-3" />
                                        Student Custom
                                    </span>
                                    <div class="text-[10px] text-[var(--muted)] mt-0.5">{{ $category->user?->name ?? 'User #' . $category->user_id }}</div>
                                </div>
                            @endif
                        </td>
                        <td class="font-mono tabular-nums text-center text-[var(--muted)] font-medium">
                            {{ number_format($category->transactions_count) }}
                        </td>
                        <td class="font-mono tabular-nums text-center text-[var(--muted)] font-medium">
                            {{ number_format($category->budgets_count) }}
                        </td>
                        <td>
                            @if($category->is_active)
                                <x-badge variant="income">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
                                    <span>Active</span>
                                </x-badge>
                            @else
                                <x-badge variant="secondary">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--muted)]"></span>
                                    <span>Deactivated</span>
                                </x-badge>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <button wire:click="openEditModal({{ $category->id }})" 
                                        class="btn-icon !w-7 !h-7"
                                        title="Edit Category"
                                        aria-label="Edit category {{ $category->name }}">
                                    <x-icon name="edit" class="w-3.5 h-3.5" />
                                </button>

                                <button wire:click="toggleCategoryStatus({{ $category->id }})" 
                                        class="px-2.5 py-1 text-[11px] font-mono border hairline-border transition-colors {{ $category->is_active ? 'text-[var(--expense)] border-[var(--hairline)] hover:border-[var(--expense)]' : 'text-[var(--accent)] border-[var(--hairline)] hover:border-[var(--accent)]' }}"
                                        title="{{ $category->is_active ? 'Deactivate category (preserve historical records)' : 'Activate category for student use' }}"
                                        aria-label="{{ $category->is_active ? 'Deactivate category ' . $category->name : 'Activate category ' . $category->name }}">
                                    {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                </button>

                                <button wire:click="deleteCategory({{ $category->id }})" 
                                        wire:confirm="Attempt to delete category '{{ $category->name }}'? If it is referenced by existing transactions or budgets, deletion will be safely rejected."
                                        class="btn-icon !w-7 !h-7 text-[var(--expense)] hover:border-[var(--expense)]"
                                        title="Delete (Safely checked)"
                                        aria-label="Delete category {{ $category->name }}">
                                    <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-xs text-[var(--muted)]">
                            No categories match the specified scope, type, or search query.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <x-modal :show="true" :title="$editingId ? 'Edit Category' : 'Create Global Default Category'" titleId="admin-category-modal-title" maxWidth="md" onClose="$wire.closeModal()">
            <form wire:submit="saveCategory" class="space-y-5 text-xs">
                <div class="space-y-1.5">
                    <label class="block text-xs font-caps text-[var(--muted)]">Category Name</label>
                    <x-field type="text" 
                             wire:model="name" 
                             placeholder="e.g. Dining, Textbooks, Grants"
                             :hasError="$errors->has('name')" />
                    @error('name') <span class="text-[var(--expense)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-caps text-[var(--muted)]">Cash-Flow Type</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-2.5 p-3 border hairline-border cursor-pointer transition-colors {{ $type === 'expense' ? 'bg-[var(--paper)] border-[var(--expense)] text-[var(--expense)] font-medium' : 'bg-[var(--panel)] hover:bg-[var(--paper)] text-[var(--ink)]' }}">
                            <input type="radio" wire:model="type" value="expense" class="accent-[var(--expense)]" />
                            <div>
                                <div class="font-medium">Expense</div>
                                <div class="text-[10px] text-[var(--muted)] font-normal">Outflow / Spending</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 border hairline-border cursor-pointer transition-colors {{ $type === 'income' ? 'bg-[var(--paper)] border-[var(--accent)] text-[var(--accent)] font-medium' : 'bg-[var(--panel)] hover:bg-[var(--paper)] text-[var(--ink)]' }}">
                            <input type="radio" wire:model="type" value="income" class="accent-[var(--accent)]" />
                            <div>
                                <div class="font-medium">Income</div>
                                <div class="text-[10px] text-[var(--muted)] font-normal">Inflow / Earnings</div>
                            </div>
                        </label>
                    </div>
                    @error('type') <span class="text-[var(--expense)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-caps text-[var(--muted)]">Icon Representation</label>
                    <div class="grid grid-cols-6 gap-2" role="group" aria-label="Icon representation">
                        @foreach ($availableIcons as $iconKey => $iconLabel)
                            <button type="button" 
                                    wire:click="$set('icon', '{{ $iconKey }}')"
                                    aria-label="Select icon {{ $iconLabel }}"
                                    class="p-2.5 border hairline-border flex items-center justify-center transition-colors {{ $icon === $iconKey ? 'border-[var(--accent)] bg-[var(--paper)] text-[var(--accent)] ring-1 ring-[var(--accent)]' : 'bg-[var(--panel)] text-[var(--muted)] hover:text-[var(--ink)] hover:border-[var(--ink)]' }}"
                                    title="{{ $iconLabel }}">
                                <x-icon :name="$iconKey" class="w-4 h-4" />
                            </button>
                        @endforeach
                    </div>
                    @error('icon') <span class="text-[var(--expense)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-caps text-[var(--muted)]">Category Color Swatch</label>
                    <div class="flex flex-wrap items-center gap-2.5" role="group" aria-label="Category color accent">
                        @foreach ($availableColors as $hex => $label)
                            <button type="button" 
                                    wire:click="$set('color', '{{ $hex }}')"
                                    aria-label="Select color {{ $label }}"
                                    class="w-7 h-7 rounded-full border-2 transition-transform {{ $color === $hex ? 'scale-125 border-[var(--ink)]' : 'border-transparent hover:scale-110' }}"
                                    style="background-color: {{ $hex }}"
                                    title="{{ $label }}">
                            </button>
                        @endforeach
                    </div>
                    @error('color') <span class="text-[var(--expense)] text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                @if ($editingId)
                    <div class="pt-2 border-t hairline-border">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="border hairline-border text-[var(--accent)] accent-[var(--accent)]" />
                            <div>
                                <div class="font-medium text-[var(--ink)]">Category Active & Selectable</div>
                                <div class="text-[10px] text-[var(--muted)]">Unchecking deactivates this category without affecting historical transactions</div>
                            </div>
                        </label>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t hairline-border">
                    <x-button variant="secondary" wire:click="closeModal">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        {{ $editingId ? 'Save Changes' : 'Create Global Category' }}
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
