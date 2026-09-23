<?php

namespace App\Livewire\Student;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CategoryManager extends Component
{
    public string $filterType = 'all'; // all, expense, income

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'expense';

    public string $icon = 'tag';

    public string $color = '#059669';

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
        '#64748B' => 'Muted Gray',
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'type' => ['required', 'in:income,expense'],
            'icon' => ['required', 'string', 'in:'.implode(',', array_keys($this->availableIcons))],
            'color' => ['required', 'string'],
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
        $this->feedbackMessage = null;
        $this->errorMessage = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $category) {
            $this->errorMessage = 'Access denied. You cannot modify global or unauthorized categories.';

            return;
        }

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->icon = $category->icon;
        $this->color = $category->color;
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

        $userId = Auth::id();

        if ($this->editingId) {
            $category = Category::where('id', $this->editingId)
                ->where('user_id', $userId)
                ->first();

            if (! $category) {
                $this->errorMessage = 'Category not found or unauthorized.';
                $this->showModal = false;

                return;
            }

            $category->update([
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
            ]);

            $this->feedbackMessage = "Category '{$category->name}' successfully updated.";
        } else {
            Category::create([
                'user_id' => $userId,
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
                'is_default' => false,
            ]);

            $this->feedbackMessage = "Personal category '{$this->name}' successfully created.";
        }

        $this->showModal = false;
    }

    public function deleteCategory(int $id): void
    {
        $userId = Auth::id();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $category = Category::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $category) {
            $this->errorMessage = 'Cannot delete this category. Global default categories are permanent.';

            return;
        }

        // Safe handling of categories already referenced by transactions
        $transactionsCount = $category->transactions()->where('user_id', $userId)->count();

        if ($transactionsCount > 0) {
            $this->errorMessage = "Cannot delete '{$category->name}'. There are currently {$transactionsCount} transaction(s) assigned to it. Please reassign or delete those transactions first.";

            return;
        }

        $categoryName = $category->name;
        $category->delete();

        $this->feedbackMessage = "Category '{$categoryName}' deleted successfully.";
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Category::forUser($userId)
            ->withCount(['transactions' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        $categories = $query->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.student.category-manager', [
            'categories' => $categories,
            'personalCount' => Category::personal($userId)->count(),
            'defaultCount' => Category::systemDefaults()->count(),
        ])->layout('components.layouts.app', ['title' => 'Category Management']);
    }
}
