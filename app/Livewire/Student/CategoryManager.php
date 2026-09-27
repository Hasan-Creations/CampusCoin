<?php

namespace App\Livewire\Student;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CategoryManager extends Component
{
    public string $filterType = 'all';

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'expense';

    public string $icon = 'tag';

    public string $color = '#059669';

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

        $cat = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $cat) {
            $this->errorMessage = 'Access denied. You cannot modify global or unauthorized categories.';

            return;
        }

        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->type = $cat->type;
        $this->icon = $cat->icon;
        $this->color = $cat->color;
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

        $uid = Auth::id();

        if ($this->editingId) {
            $cat = Category::where('id', $this->editingId)
                ->where('user_id', $uid)
                ->first();

            if (! $cat) {
                $this->errorMessage = 'Category not found or unauthorized.';
                $this->showModal = false;

                return;
            }

            $cat->update([
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
            ]);

            $this->feedbackMessage = "Category '{$cat->name}' updated.";
        } else {
            Category::create([
                'user_id' => $uid,
                'name' => trim($this->name),
                'type' => $this->type,
                'icon' => $this->icon,
                'color' => $this->color,
                'is_default' => false,
            ]);

            $this->feedbackMessage = "Personal category '{$this->name}' created.";
        }

        $this->showModal = false;
    }

    public function deleteCategory(int $id): void
    {
        $uid = Auth::id();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $cat = Category::where('id', $id)
            ->where('user_id', $uid)
            ->first();

        if (! $cat) {
            $this->errorMessage = 'Cannot delete this category. Global default categories are permanent.';

            return;
        }

        $txCount = $cat->transactions()->where('user_id', $uid)->count();
        if ($txCount > 0) {
            $this->errorMessage = "Cannot delete '{$cat->name}'. There are currently {$txCount} transaction(s) assigned to it. Please reassign or delete those transactions first.";

            return;
        }

        $name = $cat->name;
        $cat->delete();

        $this->feedbackMessage = "Category '{$name}' deleted.";
    }

    public function render()
    {
        $uid = Auth::id();

        $query = Category::forUser($uid)
            ->withCount(['transactions' => function ($q) use ($uid) {
                $q->where('user_id', $uid);
            }]);

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        $cats = $query->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.student.category-manager', [
            'categories' => $cats,
            'personalCount' => Category::personal($uid)->count(),
            'defaultCount' => Category::systemDefaults()->count(),
        ])->layout('components.layouts.app', ['title' => 'Category Management']);
    }
}
