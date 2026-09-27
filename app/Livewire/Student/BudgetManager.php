<?php

namespace App\Livewire\Student;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BudgetManager extends Component
{
    public string $selectedMonth = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $category_id = null;

    public string $amount = '';

    public string $month_year = '';

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    protected $queryString = [
        'selectedMonth' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (blank($this->selectedMonth)) {
            $this->selectedMonth = date('Y-m');
        }
        $this->month_year = $this->selectedMonth;
    }

    public function updatedSelectedMonth(): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;
        $this->month_year = $this->selectedMonth;
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'category_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($userId) {
                    $category = Category::where('id', $value)
                        ->where(function ($q) use ($userId) {
                            $q->where('user_id', $userId)
                                ->orWhere('is_default', true)
                                ->orWhereNull('user_id');
                        })
                        ->first();

                    if (! $category) {
                        $fail('The selected category does not exist or is unauthorized.');

                        return;
                    }

                    if ($category->type !== 'expense') {
                        $fail('Budget goals can only be set for expense categories.');

                        return;
                    }

                    $existsQuery = Budget::where('user_id', $userId)
                        ->where('category_id', $value)
                        ->where('month_year', $this->month_year);

                    if ($this->editingId) {
                        $existsQuery->where('id', '!=', $this->editingId);
                    }

                    if ($existsQuery->exists()) {
                        $fail('A budget goal already exists for this category in the selected month.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'month_year' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'category_id.required' => 'Please select an expense category.',
            'amount.required' => 'A budget limit amount is required.',
            'amount.numeric' => 'The budget limit must be a valid number.',
            'amount.min' => 'The budget limit must be at least $0.01.',
            'month_year.required' => 'The budget month is required.',
            'month_year.regex' => 'The budget month must be in YYYY-MM format.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;
        $this->editingId = null;
        $this->amount = '';
        $this->month_year = $this->selectedMonth ?: date('Y-m');

        $usedCatIds = Budget::where('user_id', Auth::id())
            ->where('month_year', $this->month_year)
            ->pluck('category_id')
            ->toArray();

        $cat = Category::forUser(Auth::id())
            ->active()
            ->expense()
            ->whereNotIn('id', $usedCatIds)
            ->first();

        $this->category_id = $cat?->id;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $budget = Budget::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $budget) {
            $this->errorMessage = 'Budget not found or unauthorized access.';

            return;
        }

        $this->editingId = $budget->id;
        $this->category_id = $budget->category_id;
        $this->amount = (string) $budget->amount;
        $this->month_year = $budget->month_year;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $userId = Auth::id();

        if ($this->editingId) {
            $budget = Budget::where('id', $this->editingId)
                ->where('user_id', $userId)
                ->first();

            if (! $budget) {
                $this->errorMessage = 'Budget not found or unauthorized access.';
                $this->showModal = false;

                return;
            }

            $budget->update([
                'category_id' => $this->category_id,
                'amount' => $this->amount,
                'month_year' => $this->month_year,
            ]);

            $this->feedbackMessage = 'Budget goal updated successfully.';
        } else {
            Budget::create([
                'user_id' => $userId,
                'category_id' => $this->category_id,
                'amount' => $this->amount,
                'month_year' => $this->month_year,
            ]);

            $this->feedbackMessage = 'Budget goal created successfully.';
        }

        $this->showModal = false;
        $this->editingId = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $budget = Budget::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $budget) {
            $this->errorMessage = 'Budget not found or unauthorized.';

            return;
        }

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $budget = Budget::where('id', $this->deletingId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $budget) {
            $this->errorMessage = 'Budget not found or unauthorized.';
            $this->showDeleteModal = false;
            $this->deletingId = null;

            return;
        }

        $budget->delete();

        $this->feedbackMessage = 'Budget goal deleted successfully.';
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $userId = Auth::id();
        $targetMonth = $this->selectedMonth ?: date('Y-m');

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $targetMonth);
            $startDate = $monthDate->copy()->startOfMonth()->toDateString();
            $endDate = $monthDate->copy()->endOfMonth()->toDateString();
            $monthDisplay = $monthDate->format('F Y');
        } catch (\Throwable) {
            $startDate = $targetMonth.'-01';
            $endDate = $targetMonth.'-31';
            $monthDisplay = $targetMonth;
        }

        $spentByCat = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total_spent')
            ->pluck('total_spent', 'category_id')
            ->map(fn ($val) => number_format((float) $val, 2, '.', ''));

        $budgets = Budget::where('user_id', $userId)
            ->where('month_year', $targetMonth)
            ->with('category')
            ->get();

        $totalBudgeted = '0.00';
        $spentTotal = '0.00';
        $overBudgetCount = 0;
        $nearLimitCount = 0;
        $onTrackCount = 0;

        $decoratedBudgets = $budgets->map(function ($budget) use (
            $spentByCat,
            &$totalBudgeted,
            &$spentTotal,
            &$overBudgetCount,
            &$nearLimitCount,
            &$onTrackCount
        ) {
            $spent = $spentByCat->get($budget->category_id, '0.00');
            $remaining = $budget->getRemainingAmount($spent);
            $percentage = $budget->getPercentageConsumed($spent);
            $status = $budget->getStatus($spent);
            $statusLabel = $budget->getStatusLabel($spent);
            $badgeClass = $budget->getStatusBadgeClass($spent);
            $barColor = $budget->getProgressBarColor($spent);

            $totalBudgeted = bcadd($totalBudgeted, (string) $budget->amount, 2);
            $spentTotal = bcadd($spentTotal, (string) $spent, 2);

            match ($status) {
                'over_budget' => $overBudgetCount++,
                'near_limit' => $nearLimitCount++,
                default => $onTrackCount++,
            };

            return [
                'model' => $budget,
                'spent' => $spent,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'badgeClass' => $badgeClass,
                'barColor' => $barColor,
            ];
        });

        $totalRemaining = bcsub($totalBudgeted, $spentTotal, 2);

        $eligibleCategories = Category::forUser($userId)
            ->active()
            ->expense()
            ->orderBy('name')
            ->get();

        return view('livewire.student.budget-manager', [
            'decoratedBudgets' => $decoratedBudgets,
            'totalBudgeted' => $totalBudgeted,
            'totalSpentOnBudgets' => $spentTotal,
            'totalRemaining' => $totalRemaining,
            'overBudgetCount' => $overBudgetCount,
            'nearLimitCount' => $nearLimitCount,
            'onTrackCount' => $onTrackCount,
            'eligibleCategories' => $eligibleCategories,
            'monthDisplay' => $monthDisplay,
        ])->layout('components.layouts.app', ['title' => 'Budget Goals']);
    }
}
