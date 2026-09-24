<div>
    <x-slot:header>
        Categories
    </x-slot:header>

    <div class="space-y-6">
        <!-- Header Banner & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)]">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] bg-[var(--accent-tint)] text-[var(--accent-primary)] text-[11px] font-mono font-semibold uppercase tracking-wider mb-1">
                    <x-icon name="tag" class="w-3.5 h-3.5" />
                    Classification System
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Category Management
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Manage global standard categories and customized personal spending classifications.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="openCreateModal" class="btn-primary py-2 px-4 text-xs">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Create Custom Category</span>
                </button>
            </div>
        </div>

        <!-- Feedback & Error Alerts -->
        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 rounded-[6px] border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/40 dark:border-emerald-900 text-xs text-emerald-800 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div role="alert" aria-live="assertive" class="p-4 rounded-[6px] border border-rose-200 bg-rose-50 dark:bg-rose-950/40 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        <!-- Filter Tabs & Search Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="inline-flex rounded-[6px] border hairline-border p-1 bg-[var(--bg-surface)] text-xs font-medium" role="group" aria-label="Category classification filter">
                <button wire:click="$set('filterType', 'all')" 
                        aria-label="Show all categories"
                        class="px-3 py-1.5 rounded-[4px] transition-colors {{ $filterType === 'all' ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    All Categories ({{ $categories->count() }})
                </button>
                <button wire:click="$set('filterType', 'expense')" 
                        aria-label="Show expense categories only"
                        class="px-3 py-1.5 rounded-[4px] transition-colors {{ $filterType === 'expense' ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Expense
                </button>
                <button wire:click="$set('filterType', 'income')" 
                        aria-label="Show income categories only"
                        class="px-3 py-1.5 rounded-[4px] transition-colors {{ $filterType === 'income' ? 'bg-[var(--bg-subtle)] text-[var(--text-primary)] font-semibold' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Income
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full sm:w-64">
                <span class="absolute left-3 top-2.5 text-[var(--text-muted)]">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input wire:model.live.debounce.250ms="search" 
                       type="text" 
                       aria-label="Filter categories by name"
                       placeholder="Filter categories..." 
                       class="input-campus w-full pl-9 text-xs">
            </div>
        </div>

        <!-- 3-Column Card Grid (Desktop) / Responsive Grid -->
        @if ($categories->isEmpty())
            <div class="card-campus border hairline-border p-12 text-center space-y-3">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                    <x-icon name="tag" class="w-6 h-6" />
                </div>
                <h2 class="font-heading text-base font-bold text-[var(--text-primary)] uppercase tracking-wide">
                    No Categories Matching Filter
                </h2>
                <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                    Try adjusting your search criteria or create a new personal category.
                </p>
                <button wire:click="openCreateModal" class="btn-primary py-2 px-4 text-xs mt-2">
                    Create Category
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($categories as $cat)
                    <div class="card-campus border hairline-border p-5 space-y-4 hover:border-slate-300 dark:hover:border-zinc-700 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[6px] flex items-center justify-center text-white shadow-xs"
                                     style="background-color: {{ $cat->color }};">
                                    <x-icon :name="$cat->icon" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-heading font-semibold text-sm text-[var(--text-primary)]">
                                        {{ $cat->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="badge-campus {{ $cat->type === 'income' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400' }}">
                                            {{ $cat->type }}
                                        </span>
                                        @if ($cat->isDefault())
                                            <span class="badge-campus bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                                                Standard
                                            </span>
                                        @else
                                            <span class="badge-campus bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-400">
                                                Custom
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions for Personal Categories -->
                            @if (! $cat->isDefault())
                                <div class="flex items-center gap-1">
                                    <button wire:click="openEditModal({{ $cat->id }})" 
                                            title="Edit Category"
                                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)]">
                                        <x-icon name="sliders" class="w-3.5 h-3.5" />
                                    </button>
                                    <button wire:click="deleteCategory({{ $cat->id }})" 
                                            wire:confirm="Are you sure you want to delete this personal category?"
                                            title="Delete Category"
                                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[var(--text-muted)] hover:text-[var(--danger)]">
                                        <x-icon name="shield-alert" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Card Metrics: Transaction Count & Allocation -->
                        <div class="pt-3 border-t hairline-border grid grid-cols-2 gap-2 text-xs font-mono">
                            <div>
                                <div class="text-[10px] text-[var(--text-muted)] uppercase tracking-wider">Entries</div>
                                <div class="font-bold text-[var(--text-primary)] tabular-nums mt-0.5">
                                    {{ $cat->transactions_count }} logged
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] text-[var(--text-muted)] uppercase tracking-wider">Scope</div>
                                <div class="font-medium text-[var(--text-muted)] mt-0.5">
                                    {{ $cat->isDefault() ? 'Campus Global' : 'Student Private' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Create / Edit Modal (No blur, clean hairline card) -->
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="student-category-modal-title"
                 x-data
                 @keydown.escape.window="$wire.closeModal()">
                <div class="card-campus border hairline-border w-full max-w-md p-6 bg-[var(--bg-surface)] shadow-xl relative"
                     @click.away="$wire.closeModal()">
                    <div class="flex items-center justify-between pb-4 border-b hairline-border mb-4">
                        <h2 id="student-category-modal-title" class="font-heading font-bold text-base text-[var(--text-primary)]">
                            {{ $editingId ? 'Edit Personal Category' : 'Create Custom Category' }}
                        </h2>
                        <button type="button" wire:click="closeModal" aria-label="Close modal" class="text-[var(--text-muted)] hover:text-[var(--text-primary)] text-lg">&times;</button>
                    </div>

                    <form wire:submit.prevent="saveCategory" class="space-y-4">
                        <!-- Category Type -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                Category Type
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                        wire:click="$set('type', 'expense')"
                                        class="py-2 px-3 text-xs font-medium rounded-[4px] border hairline-border flex items-center justify-center gap-1.5 {{ $type === 'expense' ? 'bg-rose-50 border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 font-semibold' : 'text-[var(--text-muted)] hover:bg-[var(--bg-subtle)]' }}">
                                    <span>Expense</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('type', 'income')"
                                        class="py-2 px-3 text-xs font-medium rounded-[4px] border hairline-border flex items-center justify-center gap-1.5 {{ $type === 'income' ? 'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-900 dark:text-emerald-400 font-semibold' : 'text-[var(--text-muted)] hover:bg-[var(--bg-subtle)]' }}">
                                    <span>Income</span>
                                </button>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                Category Title
                            </label>
                            <input wire:model="name" 
                                   id="name" 
                                   type="text" 
                                   placeholder="e.g. Lab Supplies, Laundry, Canteen" 
                                   class="input-campus w-full text-xs">
                            @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Icon Selector -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                Symbol / Icon
                            </label>
                            <div class="grid grid-cols-5 gap-2" role="group" aria-label="Icon selection">
                                @foreach ($availableIcons as $key => $label)
                                    <button type="button" 
                                            wire:click="$set('icon', '{{ $key }}')"
                                            aria-label="Select icon {{ $label }}"
                                            title="{{ $label }}"
                                            class="p-2 rounded-[4px] border hairline-border flex items-center justify-center {{ $icon === $key ? 'border-[var(--accent-primary)] bg-[var(--accent-tint)] text-[var(--accent-primary)]' : 'text-[var(--text-muted)] hover:bg-[var(--bg-subtle)]' }}">
                                        <x-icon :name="$key" class="w-4 h-4" />
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Color Selector -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                                Identification Color
                            </label>
                            <div class="flex items-center gap-2" role="group" aria-label="Color selection">
                                @foreach ($availableColors as $hex => $label)
                                    <button type="button" 
                                            wire:click="$set('color', '{{ $hex }}')"
                                            aria-label="Select color {{ $label }}"
                                            title="{{ $label }}"
                                            class="w-6 h-6 rounded-[4px] border {{ $color === $hex ? 'ring-2 ring-offset-2 ring-slate-800 dark:ring-slate-200' : 'border-black/10' }}"
                                            style="background-color: {{ $hex }};">
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-2 pt-4 border-t hairline-border">
                            <button type="button" wire:click="closeModal" class="btn-secondary py-1.5 px-3 text-xs">
                                Cancel
                            </button>
                            <button type="submit" class="btn-primary py-1.5 px-4 text-xs">
                                {{ $editingId ? 'Save Changes' : 'Create Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
