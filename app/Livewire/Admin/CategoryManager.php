<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CategoryManager extends Component
{
    public string $filterScope = 'global'; // global, personal, all

    public string $filterType = 'all'; // all, expense, income

    public string $filterStatus = 'all'; // all, active, inactive

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'expense';

    public string $icon = 'tag';

    public string $color = '#059669';

    public bool $is_active = true;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    /**
     * Icon palette matching the visual line icon system.
     */
    public array $availableIcons = [
        'tag' => 'Tag',
        'wallet' => 'Wallet',
        'pie-chart' => 'Food/Dining',
        'activity' => 'Work/Activity',
        'graduation-cap' => 'Academics',
        'calendar' => 'Subscriptions',
        'sliders' => 'Entertainment',
        'target' => 'Goals',
        'lightbulb' => 'Tips',
        'credit-card' => 'Payment/Card',
        'plus' => 'Other',
    ];

    /**
     * Color palette adhering to Campus Coin color system.
     */
    public array $availableColors = [
        '#059669' => 'Primary Emerald',
        '#10B981' => 'Light Accent',
        '#E11D48' => 'Rose Expense',
        '#D97706' => 'Amber Gold',
        '#6366F1' => 'Indigo Slate',
        '#8B5CF6' => 'Violet',
        '#EC4899' => 'Pink Outings',
        '#0284C7' => 'Sky Blue',
        '#64748B' => 'Muted Gray',
    ];

    public function boot(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator privileges required.');
        }
    }

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('categories', 'name')
            ->where(function ($query) {
                return $query->where(function ($q) {
                    $q->where('is_default', true)->orWhereNull('user_id');
                })->where('type', $this->type);
            });

        if ($this->editingId) {
            $uniqueRule->ignore($this->editingId);
        }

        return [
            'name' => ['required', 'string', 'min:2', 'max:100', $uniqueRule],
            'type' => ['required', 'in:income,expense'],
            'icon' => ['required', 'string', 'in:'.implode(',', array_keys($this->availableIcons))],
            'color' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'A global category with this name and cash-flow type already exists.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingId = null;
        $this->name = '';
        $this->type = 'expense';
        $this->icon = 'tag';
        $this->color = '#059669';
        $this->is_active = true;
        $this->feedbackMessage = null;
        $this->errorMessage = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $category = Category::find($id);

        if (! $category) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->icon = $category->icon;
        $this->color = $category->color;
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function saveCategory(): void
    {
        $this->validate();

        if ($this->editingId) {
            $category = Category::find($this->editingId);

            if (! $category) {
                $this->errorMessage = 'Category not found.';
                $this->showModal = false;

                return;
            }

            $category->update([
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
                'is_active' => $this->is_active,
            ]);

            $this->feedbackMessage = "Category '{$category->name}' successfully updated.";
        } else {
            Category::create([
                'user_id' => null,
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
                'is_default' => true,
                'is_active' => true,
            ]);

            $this->feedbackMessage = "Global system category '{$this->name}' successfully created.";
        }

        $this->showModal = false;
    }

    public function toggleCategoryStatus(int $id): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $category = Category::find($id);

        if (! $category) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        if ($category->is_active) {
            $this->feedbackMessage = "Category '{$category->name}' has been activated and is now selectable for student transactions.";
        } else {
            $this->feedbackMessage = "Category '{$category->name}' has been deactivated. Historical records remain intact, but students cannot select it for new entries.";
        }
    }

    public function deleteCategory(int $id): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $category = Category::find($id);

        if (! $category) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $transactionsCount = $category->transactions()->count();
        $budgetsCount = $category->budgets()->count();
        $tipsCount = $category->savingTips()->count();
        $learningsCount = $category->categoryLearnings()->count();

        if ($transactionsCount > 0 || $budgetsCount > 0 || $tipsCount > 0 || $learningsCount > 0) {
            $details = [];
            if ($transactionsCount > 0) {
                $details[] = "{$transactionsCount} transaction(s)";
            }
            if ($budgetsCount > 0) {
                $details[] = "{$budgetsCount} budget(s)";
            }
            if ($tipsCount > 0) {
                $details[] = "{$tipsCount} tip(s)";
            }
            if ($learningsCount > 0) {
                $details[] = "{$learningsCount} AI learned mapping(s)";
            }

            $detailStr = implode(', ', $details);
            $this->errorMessage = "Cannot hard delete '{$category->name}'. It is currently referenced by {$detailStr}. Deactivate this category instead to preserve historical financial integrity.";

            return;
        }

        $name = $category->name;
        $category->delete();

        $this->feedbackMessage = "Category '{$name}' was safely and permanently deleted.";
    }

    public function render()
    {
        $query = Category::with('user')
            ->withCount(['transactions', 'budgets']);

        // Scope filter: Global Default vs Personal vs All
        if ($this->filterScope === 'global') {
            $query->where(function (Builder $q) {
                $q->where('is_default', true)->orWhereNull('user_id');
            });
        } elseif ($this->filterScope === 'personal') {
            $query->where('is_default', false)->whereNotNull('user_id');
        }

        // Type filter
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        // Status filter
        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        // Search filter
        if (filled($this->search)) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        $categories = $query->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $globalCount = Category::where('is_default', true)->orWhereNull('user_id')->count();
        $personalCount = Category::where('is_default', false)->whereNotNull('user_id')->count();
        $activeCount = Category::where('is_active', true)->count();
        $inactiveCount = Category::where('is_active', false)->count();

        return view('livewire.admin.category-manager', [
            'categories' => $categories,
            'globalCount' => $globalCount,
            'personalCount' => $personalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
        ])->layout('components.layouts.admin', ['title' => 'Global Category Governance']);
    }
}
