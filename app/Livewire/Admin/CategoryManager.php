<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CategoryManager extends Component
{
    public string $filterScope = 'global';

    public string $filterType = 'all';

    public string $filterStatus = 'all';

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

        $cat = Category::find($id);
        if (! $cat) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->type = $cat->type;
        $this->icon = $cat->icon;
        $this->color = $cat->color;
        $this->is_active = $cat->is_active;
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
            $cat = Category::find($this->editingId);

            if (! $cat) {
                $this->errorMessage = 'Category not found.';
                $this->showModal = false;

                return;
            }

            $cat->update([
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
                'is_active' => $this->is_active,
            ]);

            $this->feedbackMessage = "Category '{$cat->name}' updated.";
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

            $this->feedbackMessage = "Global category '{$this->name}' created.";
        }

        $this->showModal = false;
    }

    public function toggleCategoryStatus(int $id): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $cat = Category::find($id);
        if (! $cat) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $cat->update([
            'is_active' => ! $cat->is_active,
        ]);

        if ($cat->is_active) {
            $this->feedbackMessage = "Category '{$cat->name}' has been activated and is now selectable for student transactions.";
        } else {
            $this->feedbackMessage = "Category '{$cat->name}' has been deactivated. Historical records remain intact, but students cannot select it for new entries.";
        }
    }

    public function deleteCategory(int $id): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $cat = Category::find($id);
        if (! $cat) {
            $this->errorMessage = 'Category not found.';

            return;
        }

        $txCount = $cat->transactions()->count();
        $budgetCount = $cat->budgets()->count();
        $tipCount = $cat->savingTips()->count();
        $learnCount = $cat->categoryLearnings()->count();

        if ($txCount > 0 || $budgetCount > 0 || $tipCount > 0 || $learnCount > 0) {
            $details = [];
            if ($txCount > 0) {
                $details[] = "{$txCount} transaction(s)";
            }
            if ($budgetCount > 0) {
                $details[] = "{$budgetCount} budget(s)";
            }
            if ($tipCount > 0) {
                $details[] = "{$tipCount} tip(s)";
            }
            if ($learnCount > 0) {
                $details[] = "{$learnCount} AI learned mapping(s)";
            }

            $detailStr = implode(', ', $details);
            $this->errorMessage = "Cannot hard delete '{$cat->name}'. It is currently referenced by {$detailStr}. Deactivate this category instead to preserve historical financial integrity.";

            return;
        }

        $name = $cat->name;
        $cat->delete();

        $this->feedbackMessage = "Category '{$name}' was safely and permanently deleted.";
    }

    public function render()
    {
        $query = Category::with('user')
            ->withCount(['transactions', 'budgets']);

        if ($this->filterScope === 'global') {
            $query->where(function (Builder $q) {
                $q->where('is_default', true)->orWhereNull('user_id');
            });
        } elseif ($this->filterScope === 'personal') {
            $query->where('is_default', false)->whereNotNull('user_id');
        }

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        $cats = $query->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $globalCount = Category::where('is_default', true)->orWhereNull('user_id')->count();
        $personalCount = Category::where('is_default', false)->whereNotNull('user_id')->count();
        $activeCount = Category::where('is_active', true)->count();
        $inactiveCount = Category::where('is_active', false)->count();

        return view('livewire.admin.category-manager', [
            'categories' => $cats,
            'globalCount' => $globalCount,
            'personalCount' => $personalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
        ])->layout('components.layouts.admin', ['title' => 'Global Category Governance']);
    }
}
