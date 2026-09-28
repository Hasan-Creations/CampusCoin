<div>
    <x-slot:header>
        Categories
    </x-slot:header>

    <div class="space-y-6">
        <div class="page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-caps text-[var(--muted)]">Classification System</span>
                <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] mt-1 headline-rule">
                    Category Management
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1.5">
                    Manage global standard categories and customized personal spending classifications.
                </p>
            </div>
            <x-button variant="accent" wire:click="openCreateModal">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Create Custom Category</span>
            </x-button>
        </div>

        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-xs text-[var(--accent)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="check-circle-2" class="w-4 h-4" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-xs text-[var(--expense)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="shield-alert" class="w-4 h-4" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon w-6 h-6 border-none">&times;</button>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Filter Tabs Segmented Bar --}}
            <div class="segmented-bar" role="group" aria-label="Category classification filter">
                <button wire:click="$set('filterType', 'all')"
                        aria-label="Show all categories"
                        class="segmented-item {{ $filterType === 'all' ? 'active' : '' }}">
                    All Categories ({{ $categories->count() }})
                </button>
                <button wire:click="$set('filterType', 'expense')"
                        aria-label="Show expense categories only"
                        class="segmented-item {{ $filterType === 'expense' ? 'active' : '' }}">
                    Expense
                </button>
                <button wire:click="$set('filterType', 'income')"
                        aria-label="Show income categories only"
                        class="segmented-item {{ $filterType === 'income' ? 'active' : '' }}">
                    Income
                </button>
            </div>

            {{-- Search input --}}
            <div class="relative w-full sm:w-64">
                <span class="absolute left-3.5 top-3 text-[var(--muted)]">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input wire:model.live.debounce.250ms="search"
                       type="text"
                       aria-label="Filter categories by name"
                       placeholder="Filter categories..."
                       class="field w-full pl-9 text-xs">
            </div>
        </div>

        @if ($categories->isEmpty())
            <div class="empty-state space-y-3">
                <x-icon name="tag" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                <h2 class="font-display text-base font-medium text-[var(--ink)] uppercase tracking-wide">
                    No Categories Matching Filter
                </h2>
                <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">
                    Try adjusting your search criteria or create a new personal category.
                </p>
                <x-button variant="secondary" wire:click="openCreateModal">
                    Create Category
                </x-button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($categories as $cat)
                    <div class="card-campus p-5 space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 flex items-center justify-center text-white flex-shrink-0"
                                     style="background-color: {{ $cat->color }};">
                                    <x-icon :name="$cat->icon" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-display font-medium text-sm text-[var(--ink)]">
                                        {{ $cat->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="badge {{ $cat->type === 'income' ? 'badge-income' : 'badge-expense' }}">
                                            {{ $cat->type }}
                                        </span>
                                        @if ($cat->isDefault())
                                            <span class="badge">Standard</span>
                                        @else
                                            <span class="badge badge-income">Custom</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions for Personal Categories --}}
                            @if (! $cat->isDefault())
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="openEditModal({{ $cat->id }})"
                                            title="Edit Category"
                                            class="btn-icon w-8 h-8">
                                        <x-icon name="sliders" class="w-3.5 h-3.5" />
                                    </button>
                                    <button wire:click="deleteCategory({{ $cat->id }})"
                                            wire:confirm="Are you sure you want to delete this personal category?"
                                            title="Delete Category"
                                            class="btn-icon w-8 h-8 hover:text-[var(--expense)] hover:border-[var(--expense)]">
                                        <x-icon name="shield-alert" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Card Metrics: Transaction Count & Allocation --}}
                        <div class="pt-3 border-t hairline-border grid grid-cols-2 gap-2 text-xs font-mono">
                            <div>
                                <div class="text-[10px] text-[var(--muted)] uppercase tracking-wider">Entries</div>
                                <div class="font-medium text-[var(--ink)] tabular-nums mt-0.5">
                                    {{ $cat->transactions_count }} logged
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] text-[var(--muted)] uppercase tracking-wider">Scope</div>
                                <div class="text-[var(--muted)] mt-0.5">
                                    {{ $cat->isDefault() ? 'Campus Global' : 'Student Private' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($showModal)
            <x-modal :show="true"
                     :title="$editingId ? 'Edit Personal Category' : 'Create Custom Category'"
                     titleId="student-category-modal-title"
                     maxWidth="md"
                     onClose="$wire.closeModal()">
                <div class="space-y-4">
                    <form wire:submit.prevent="saveCategory" class="space-y-4">
                        {{-- Category Type --}}
                        <div>
                            <label class="block text-xs font-caps text-[var(--muted)] mb-1.5">
                                Category Type
                            </label>
                            <div class="segmented-bar w-full">
                                <button type="button"
                                        wire:click="$set('type', 'expense')"
                                        class="segmented-item flex-1 text-center {{ $type === 'expense' ? 'active' : '' }}">
                                    <span>Expense</span>
                                </button>
                                <button type="button"
                                        wire:click="$set('type', 'income')"
                                        class="segmented-item flex-1 text-center {{ $type === 'income' ? 'active' : '' }}">
                                    <span>Income</span>
                                </button>
                            </div>
                        </div>

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-xs font-caps text-[var(--muted)] mb-1.5">
                                Category Title
                            </label>
                            <x-field wire:model="name"
                                     id="name"
                                     type="text"
                                     placeholder="e.g. Lab Supplies, Laundry, Canteen"
                                     class="text-xs"
                                     :hasError="$errors->has('name')" />
                            @error('name') <span class="text-[var(--expense)] text-[11px] mt-1 block font-mono">{{ $message }}</span> @enderror
                        </div>

                        {{-- Icon Selector --}}
                        <div>
                            <label class="block text-xs font-caps text-[var(--muted)] mb-1.5">
                                Symbol / Icon
                            </label>
                            <div class="grid grid-cols-5 gap-2" role="group" aria-label="Icon selection">
                                @foreach ($availableIcons as $key => $label)
                                    <button type="button"
                                            wire:click="$set('icon', '{{ $key }}')"
                                            aria-label="Select icon {{ $label }}"
                                            title="{{ $label }}"
                                            class="p-2.5 border hairline-border flex items-center justify-center transition-colors {{ $icon === $key ? 'border-[var(--accent)] bg-[var(--paper)] text-[var(--accent)]' : 'text-[var(--muted)] hover:border-[var(--muted)]' }}">
                                        <x-icon :name="$key" class="w-4 h-4" />
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Color Selector --}}
                        <div>
                            <label class="block text-xs font-caps text-[var(--muted)] mb-1.5">
                                Identification Color
                            </label>
                            <div class="flex items-center gap-2 flex-wrap" role="group" aria-label="Color selection">
                                @foreach ($availableColors as $hex => $label)
                                    <button type="button"
                                            wire:click="$set('color', '{{ $hex }}')"
                                            aria-label="Select color {{ $label }}"
                                            title="{{ $label }}"
                                            class="w-7 h-7 border transition-colors {{ $color === $hex ? 'ring-2 ring-offset-1 ring-[var(--ink)]' : 'border-[var(--hairline)]' }}"
                                            style="background-color: {{ $hex }};">
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t hairline-border">
                            <x-button variant="secondary" wire:click="closeModal" type="button">
                                Cancel
                            </x-button>
                            <x-button variant="accent" type="submit">
                                {{ $editingId ? 'Save Changes' : 'Create Category' }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif
    </div>
</div>
