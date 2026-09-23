<?php

namespace App\Livewire\Student;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\FinancialCalculationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MonthlyReports extends Component
{
    use WithPagination;

    /**
     * Active report viewing tab.
     * Options: 'monthly', 'category', 'six_month', 'daily', 'weekly', 'ledger'.
     */
    public string $reportTab = 'monthly';

    /**
     * Preset period selector.
     * Options: 'this_month', 'last_month', 'last_3_months', 'last_6_months', 'year', 'custom'.
     */
    public string $presetPeriod = 'this_month';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $categoryFilter = '';

    public string $typeFilter = 'all'; // all, expense, income

    protected $queryString = [
        'reportTab' => ['except' => 'monthly'],
        'presetPeriod' => ['except' => 'this_month'],
        'categoryFilter' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $now = Carbon::now();
        $this->dateFrom = $now->copy()->startOfMonth()->toDateString();
        $this->dateTo = $now->copy()->endOfMonth()->toDateString();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['monthly', 'category', 'six_month', 'daily', 'weekly', 'ledger'], true)) {
            $this->reportTab = $tab;
        }
    }

    public function setPresetPeriod(string $preset): void
    {
        $allowed = ['this_month', 'last_month', 'last_3_months', 'last_6_months', 'year', 'custom'];
        if (! in_array($preset, $allowed, true)) {
            return;
        }

        $this->presetPeriod = $preset;
        $now = Carbon::now();

        switch ($preset) {
            case 'last_month':
                $this->dateFrom = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $this->dateTo = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'last_3_months':
                $this->dateFrom = $now->copy()->subMonths(2)->startOfMonth()->toDateString();
                $this->dateTo = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'last_6_months':
                $this->dateFrom = $now->copy()->subMonths(5)->startOfMonth()->toDateString();
                $this->dateTo = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'year':
                $this->dateFrom = $now->copy()->startOfYear()->toDateString();
                $this->dateTo = $now->copy()->endOfYear()->toDateString();
                break;
            case 'this_month':
                $this->dateFrom = $now->copy()->startOfMonth()->toDateString();
                $this->dateTo = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'custom':
                // retain existing dateFrom and dateTo
                break;
        }

        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->presetPeriod = 'custom';
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->presetPeriod = 'custom';
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->presetPeriod = 'this_month';
        $this->categoryFilter = '';
        $this->typeFilter = 'all';
        $now = Carbon::now();
        $this->dateFrom = $now->copy()->startOfMonth()->toDateString();
        $this->dateTo = $now->copy()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function render(FinancialCalculationService $calculationService)
    {
        $userId = Auth::id();
        $user = Auth::user();
        $now = Carbon::now();

        $startDate = filled($this->dateFrom) ? Carbon::parse($this->dateFrom)->startOfDay() : $now->copy()->startOfMonth();
        $endDate = filled($this->dateTo) ? Carbon::parse($this->dateTo)->endOfDay() : $now->copy()->endOfMonth();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $filters = [];
        if (filled($this->categoryFilter)) {
            $filters['category_id'] = (int) $this->categoryFilter;
        }
        if ($this->typeFilter !== 'all') {
            $filters['type'] = $this->typeFilter;
        }

        // --- Data Gathering via Calculation Service ---
        $summary = $calculationService->getReportSummary($userId, $startDate, $endDate, $filters);
        $categoryReport = $calculationService->getCategoryWiseReport($userId, $startDate, $endDate, $filters);
        $sixMonthTrends = $calculationService->getSixMonthCashFlow($userId, $now);
        $dailySummary = $calculationService->getCurrentMonthDailySummary($userId, $now, $filters);
        $weeklySummary = $calculationService->getCurrentMonthWeeklySummary($userId, $now, $filters);

        // --- Detailed Ledger Query ---
        $txQuery = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($filters['category_id'])) {
            $txQuery->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type'])) {
            $txQuery->where('type', $filters['type']);
        }

        $transactions = $txQuery->paginate(15);

        // Categories available for student
        $categories = Category::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('livewire.student.monthly-reports', [
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'categoryReport' => $categoryReport,
            'sixMonthTrends' => $sixMonthTrends,
            'dailySummary' => $dailySummary,
            'weeklySummary' => $weeklySummary,
            'transactions' => $transactions,
            'categories' => $categories,
            'periodLabel' => $summary['period_label'],
        ])->layout('components.layouts.app', ['title' => 'Monthly Financial Reports']);
    }
}
