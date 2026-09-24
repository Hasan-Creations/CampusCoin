<?php

namespace App\Livewire\Student;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\AiCategorizationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TransactionList extends Component
{
    use WithFileUploads, WithPagination;

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

    // AI Categorization Advisory State
    public ?array $activeSuggestion = null;

    public bool $manualCategorySelected = false;

    public bool $suggestionAccepted = false;

    // CSV Batch Import State
    public bool $showImportModal = false;

    public $csvFile = null;

    public array $importRows = [];

    public bool $importStepReview = false;

    public ?string $importError = null;

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
        // When changing type in form, clear selected category and previous suggestions
        $this->category_id = null;
        $this->activeSuggestion = null;
        $this->manualCategorySelected = false;
        $this->suggestionAccepted = false;
    }

    public function updatedDescription(): void
    {
        $this->requestCategorySuggestion();
    }

    public function updatedMerchant(): void
    {
        // If description is not yet populated, use merchant to predict category
        if (blank($this->description)) {
            $this->requestCategorySuggestion();
        }
    }

    /**
     * Request category suggestion non-blockingly from central AI service.
     */
    public function requestCategorySuggestion(): void
    {
        $queryText = trim((string) ($this->description ?: $this->merchant));

        if (mb_strlen($queryText) < 2) {
            $this->activeSuggestion = null;

            return;
        }

        $userId = (int) Auth::id();
        $availableCategories = Category::forUser($userId)
            ->active()
            ->where('type', $this->type)
            ->get();

        /** @var AiCategorizationService $service */
        $service = app(AiCategorizationService::class);
        $suggestion = $service->suggestCategory($queryText, $availableCategories, Auth::user());

        if ($suggestion) {
            $this->activeSuggestion = $suggestion->toArray();
        } else {
            $this->activeSuggestion = null;
        }
    }

    /**
     * Accept the advisory AI suggestion.
     */
    public function acceptSuggestion(): void
    {
        if ($this->activeSuggestion && isset($this->activeSuggestion['categoryId'])) {
            $this->category_id = (int) $this->activeSuggestion['categoryId'];
            $this->suggestionAccepted = true;
            $this->manualCategorySelected = false;
        }
    }

    /**
     * Student explicitly clicks or chooses a category.
     * Manual selection is always authoritative.
     */
    public function selectCategory(int $id): void
    {
        $this->category_id = $id;
        $this->manualCategorySelected = true;
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

        $this->activeSuggestion = null;
        $this->manualCategorySelected = false;
        $this->suggestionAccepted = false;

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

        $this->activeSuggestion = null;
        $this->manualCategorySelected = true;
        $this->suggestionAccepted = false;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->activeSuggestion = null;
        $this->manualCategorySelected = false;
        $this->suggestionAccepted = false;
        $this->resetErrorBag();
    }

    public function saveTransaction(): void
    {
        $this->validate();

        $userId = (int) Auth::id();

        $isAiSuggested = ($this->activeSuggestion
            && isset($this->activeSuggestion['categoryId'])
            && (int) $this->category_id === (int) $this->activeSuggestion['categoryId']
            && ! $this->manualCategorySelected);

        $aiConfidence = $isAiSuggested ? ($this->activeSuggestion['confidence'] ?? null) : null;

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
                'ai_suggested' => $isAiSuggested,
                'ai_confidence' => $aiConfidence,
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
                'ai_suggested' => $isAiSuggested,
                'ai_confidence' => $aiConfidence,
            ]);

            $this->feedbackMessage = "Transaction '{$this->merchant}' recorded successfully.";
        }

        // When transaction is saved, record learned mapping for student
        if ($this->category_id) {
            /** @var AiCategorizationService $aiService */
            $aiService = app(AiCategorizationService::class);

            if (filled($this->description)) {
                $aiService->recordCorrection($userId, (string) $this->description, (int) $this->category_id);
            }

            if (filled($this->merchant) && trim((string) $this->merchant) !== trim((string) $this->description)) {
                $aiService->recordCorrection($userId, (string) $this->merchant, (int) $this->category_id);
            }
        }

        $this->showModal = false;
        $this->activeSuggestion = null;
        $this->manualCategorySelected = false;
        $this->suggestionAccepted = false;
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

    // ==========================================
    // CSV BATCH SUGGESTIONS & IMPORT FLOW
    // ==========================================

    public function openImportModal(): void
    {
        $this->resetErrorBag();
        $this->csvFile = null;
        $this->importRows = [];
        $this->importStepReview = false;
        $this->importError = null;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->csvFile = null;
        $this->importRows = [];
        $this->importStepReview = false;
        $this->importError = null;
        $this->resetErrorBag();
    }

    public function processCsvUpload(): void
    {
        $this->importError = null;

        $this->validate([
            'csvFile' => ['required', 'file', 'max:2048'],
        ], [
            'csvFile.required' => 'Please choose a CSV spreadsheet file to upload.',
            'csvFile.file' => 'The uploaded file must be a valid file.',
            'csvFile.max' => 'The CSV file size may not exceed 2MB.',
        ]);

        $path = $this->csvFile->getRealPath();
        if (! $path || ! file_exists($path)) {
            $this->importError = 'Uploaded file could not be read by the server.';

            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->importError = 'Failed to open CSV stream.';

            return;
        }

        // Read header row
        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);
            $this->importError = 'The uploaded CSV file is empty.';

            return;
        }

        // Normalize header row
        $headers = array_map(function ($h) {
            $h = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $h);

            return strtolower(trim($h));
        }, $headerRow);

        $dateCol = null;
        $descCol = null;
        $amountCol = null;
        $typeCol = null;

        foreach ($headers as $idx => $header) {
            if (in_array($header, ['date', 'transaction_date', 'trans_date', 'tx_date'], true)) {
                $dateCol = $idx;
            } elseif (in_array($header, ['description', 'desc', 'merchant', 'details', 'name', 'item', 'notes'], true)) {
                $descCol = $idx;
            } elseif (in_array($header, ['amount', 'amt', 'value', 'price', 'total'], true)) {
                $amountCol = $idx;
            } elseif (in_array($header, ['type', 'transaction_type', 'flow'], true)) {
                $typeCol = $idx;
            }
        }

        if ($dateCol === null) {
            $dateCol = 0;
        }
        if ($descCol === null) {
            $descCol = 1;
        }
        if ($amountCol === null) {
            $amountCol = 2;
        }
        if ($typeCol === null && count($headers) > 3) {
            $typeCol = 3;
        }

        $userId = (int) Auth::id();
        $user = Auth::user();
        $availableCategories = Category::forUser($userId)->active()->get();

        /** @var AiCategorizationService $aiService */
        $aiService = app(AiCategorizationService::class);

        $rows = [];
        $rowCount = 0;
        $maxRows = 50; // Bounded batch processing (up to 50 rows)

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            $rowCount++;
            if ($rowCount > $maxRows) {
                break;
            }

            $rawDate = trim($data[$dateCol] ?? '');
            $rawDesc = trim($data[$descCol] ?? '');
            $rawAmount = trim($data[$amountCol] ?? '');
            $rawType = $typeCol !== null ? trim($data[$typeCol] ?? '') : 'expense';

            $isValid = true;
            $error = null;

            // Parse Date
            $parsedDate = date('Y-m-d');
            if (blank($rawDate)) {
                $isValid = false;
                $error = 'Missing date';
            } else {
                try {
                    $parsedDate = Carbon::parse($rawDate)->format('Y-m-d');
                } catch (Throwable) {
                    $isValid = false;
                    $error = 'Invalid date format';
                }
            }

            // Parse Description
            $description = $rawDesc;
            if (blank($description)) {
                $isValid = false;
                $error = $error ? "$error, missing description" : 'Missing description';
                $description = 'Unknown Transaction';
            }

            // Parse Amount
            $hasNegative = str_contains($rawAmount, '-');
            $cleanAmount = preg_replace('/[^\d.]/', '', $rawAmount);
            $amount = (float) $cleanAmount;
            if ($hasNegative || ! is_numeric($cleanAmount) || $amount <= 0) {
                $isValid = false;
                $error = $error ? "$error, invalid amount" : 'Amount must be greater than 0';
                $amount = 0.00;
            }

            // Parse Type
            $normType = strtolower($rawType);
            $type = in_array($normType, ['income', 'credit', 'inflow', 'deposit'], true) ? 'income' : 'expense';

            // Filter available categories for this transaction's flow type
            $typeCategories = $availableCategories->where('type', $type);

            // Categorization suggestion
            $suggestion = null;
            if ($isValid && filled($description)) {
                $suggestion = $aiService->suggestCategory($description, $typeCategories, $user);
            }

            $suggestedCatId = $suggestion?->categoryId;
            $suggestedCatName = $suggestion?->categoryName;
            $confidence = $suggestion?->confidence;
            $confidenceLevel = $suggestion?->confidenceLevel ?? 'low';
            $source = $suggestion?->source ?? 'none';

            // Pre-select suggested category or first available category of type
            $selectedCatId = $suggestedCatId ?? ($typeCategories->first()?->id);

            $rows[] = [
                'id' => $rowCount,
                'date' => $parsedDate,
                'description' => $description,
                'amount' => number_format($amount, 2, '.', ''),
                'type' => $type,
                'suggested_category_id' => $suggestedCatId,
                'suggested_category_name' => $suggestedCatName,
                'confidence' => $confidence,
                'confidence_level' => $confidenceLevel,
                'source' => $source,
                'selected_category_id' => $selectedCatId,
                'is_valid' => $isValid,
                'error' => $error,
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            $this->importError = 'No valid data rows found in the uploaded CSV.';

            return;
        }

        $this->importRows = $rows;
        $this->importStepReview = true;
    }

    public function confirmImport(): void
    {
        $userId = (int) Auth::id();
        $validRows = array_filter($this->importRows, fn ($r) => ! empty($r['is_valid']) && ! empty($r['selected_category_id']));

        if (empty($validRows)) {
            $this->importError = 'No valid rows with assigned categories to import.';

            return;
        }

        /** @var AiCategorizationService $aiService */
        $aiService = app(AiCategorizationService::class);
        $importedCount = 0;

        foreach ($validRows as $row) {
            $isAi = ($row['suggested_category_id'] && (int) $row['selected_category_id'] === (int) $row['suggested_category_id']);
            $confidence = $isAi ? $row['confidence'] : null;

            Transaction::create([
                'user_id' => $userId,
                'category_id' => (int) $row['selected_category_id'],
                'type' => $row['type'],
                'amount' => $row['amount'],
                'merchant' => $row['description'],
                'description' => $row['description'],
                'transaction_date' => $row['date'],
                'payment_method' => 'card',
                'is_recurring' => false,
                'ai_suggested' => $isAi,
                'ai_confidence' => $confidence,
            ]);

            // Save student's learned mapping for future transactions
            $aiService->recordCorrection($userId, (string) $row['description'], (int) $row['selected_category_id']);
            $importedCount++;
        }

        $this->showImportModal = false;
        $this->importRows = [];
        $this->importStepReview = false;
        $this->csvFile = null;
        $this->feedbackMessage = "Successfully imported {$importedCount} transactions with learned category mappings.";
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

        $formCategories = $categories->where('is_active', true)->where('type', $this->type);

        return view('livewire.student.transaction-list', [
            'transactions' => $transactions,
            'categories' => $categories,
            'formCategories' => $formCategories,
            'totalCount' => Transaction::where('user_id', $userId)->count(),
        ])->layout('components.layouts.app', ['title' => 'Transactions Ledger']);
    }
}
