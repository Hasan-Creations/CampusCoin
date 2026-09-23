<?php

namespace App\Livewire\Student;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionList extends Component
{
    use WithPagination;

    // Filters and Search
    public string $search = '';

    public string $typeFilter = 'all'; // all, expense, income

    public string $categoryFilter = '';

    public string $methodFilter = '';

    public string $sortBy = 'transaction_date';

    public string $sortDirection = 'desc';

    // Modal & Form State
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $type = 'expense';

    public string $amount = '';

    public string $merchant = '';

    public ?int $category_id = null;

    public string $transaction_date = '';

    public string $payment_method = 'card';

    public ?string $description = null;

    public bool $is_recurring = false;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'categoryFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->transaction_date = date('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
        // Reset category selection if it does not match new type
        if ($this->category_id) {
            $cat = Category::find($this->category_id);
            if ($cat && $cat->type !== $this->type) {
                $this->category_id = null;
            }
        }
    }

    public function updatedType(): void
    {
        // When changing type in form, clear selected category so user explicitly selects
        $this->category_id = null;
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMethodFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
    }

    protected function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'merchant.required' => $this->type === 'income' ? 'Please specify where this income is from.' : 'Please specify where you spent it.',
            'amount.required' => 'Please enter a transaction amount.',
            'amount.min' => 'Amount must be at least $0.01.',
        ];
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'merchant' => ['required', 'string', 'min:2', 'max:150'],
            'category_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Category::where('id', $value)
                        ->where(function ($q) use ($userId) {
                            $q->where('user_id', $userId)
                                ->orWhere('is_default', true)
                                ->orWhereNull('user_id');
                        })
                        ->where('type', $this->type)
                        ->exists();

                    if (! $exists) {
                        $fail('The selected category is invalid for this transaction type.');
                    }
                },
            ],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,upi,digital_wallet,other'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_recurring' => ['boolean'],
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingId = null;
        $this->type = 'expense';
        $this->amount = '';
        $this->merchant = '';
        $this->category_id = null; // Do NOT pre-fill to force explicit selection
        $this->transaction_date = date('Y-m-d');
        $this->payment_method = 'card';
        $this->description = null;
        $this->is_recurring = false;
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $transaction = Transaction::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $transaction) {
            $this->errorMessage = 'Transaction not found or unauthorized.';

            return;
        }

        $this->editingId = $transaction->id;
        $this->type = $transaction->type;
        $this->amount = (string) $transaction->amount;
        $this->merchant = $transaction->merchant;
        $this->category_id = $transaction->category_id;
        $this->transaction_date = $transaction->transaction_date->format('Y-m-d');
        $this->payment_method = $transaction->payment_method;
        $this->description = $transaction->description;
        $this->is_recurring = (bool) $transaction->is_recurring;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function saveTransaction(): void
    {
        $this->validate();

        $userId = Auth::id();

        if ($this->editingId) {
            $transaction = Transaction::where('id', $this->editingId)
                ->where('user_id', $userId)
                ->first();

            if (! $transaction) {
                $this->errorMessage = 'Unauthorized transaction modification.';
                $this->showModal = false;

                return;
            }

            $transaction->update([
                'type' => $this->type,
                'amount' => $this->amount,
                'merchant' => trim($this->merchant),
                'category_id' => $this->category_id,
                'transaction_date' => $this->transaction_date,
                'payment_method' => $this->payment_method,
                'description' => trim($this->description ?? ''),
                'is_recurring' => $this->is_recurring,
            ]);

            $this->feedbackMessage = "Transaction '{$transaction->merchant}' successfully updated.";
        } else {
            Transaction::create([
                'user_id' => $userId,
                'type' => $this->type,
                'amount' => $this->amount,
                'merchant' => trim($this->merchant),
                'category_id' => $this->category_id,
                'transaction_date' => $this->transaction_date,
                'payment_method' => $this->payment_method,
                'description' => trim($this->description ?? ''),
                'is_recurring' => $this->is_recurring,
                'ai_suggested' => false,
            ]);

            $this->feedbackMessage = "Transaction '{$this->merchant}' recorded successfully.";
        }

        $this->showModal = false;
    }

    public function deleteTransaction(int $id): void
    {
        $userId = Auth::id();

        $transaction = Transaction::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $transaction) {
            $this->errorMessage = 'Transaction could not be found or unauthorized.';

            return;
        }

        $merchant = $transaction->merchant;
        $transaction->delete();

        $this->feedbackMessage = "Transaction '{$merchant}' removed from ledger.";
    }

    public function exportCsv(): StreamedResponse
    {
        $userId = Auth::id();
        $transactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $filename = 'campus_coin_transactions_'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Merchant', 'Type', 'Category', 'Amount', 'Payment Method', 'Recurring', 'Description']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->transaction_date->format('Y-m-d'),
                    $t->merchant,
                    strtoupper($t->type),
                    $t->category ? $t->category->name : 'Uncategorized',
                    $t->amount,
                    $t->payment_method,
                    $t->is_recurring ? 'Yes' : 'No',
                    $t->description ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Transaction::where('user_id', $userId)
            ->with('category');

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if (filled($this->categoryFilter)) {
            $query->where('category_id', $this->categoryFilter);
        }

        if (filled($this->methodFilter)) {
            $query->where('payment_method', $this->methodFilter);
        }

        if (filled($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('merchant', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $allowedSorts = ['transaction_date', 'amount', 'merchant', 'type'];
        $sortColumn = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'transaction_date';
        $direction = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';

        $transactions = $query->orderBy($sortColumn, $direction)->paginate(12);

        // Fetch categories available to student for filters & form
        $categories = Category::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $formCategories = $categories->where('type', $this->type);

        return view('livewire.student.transaction-list', [
            'transactions' => $transactions,
            'categories' => $categories,
            'formCategories' => $formCategories,
            'totalCount' => Transaction::where('user_id', $userId)->count(),
        ])->layout('components.layouts.app', ['title' => 'Transactions Ledger']);
    }
}
