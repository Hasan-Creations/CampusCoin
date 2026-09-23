<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\FinancialCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function exportPdf(Request $request, FinancialCalculationService $calculationService): Response
    {
        $userId = Auth::id();
        $user = Auth::user();

        [$startDate, $endDate, $filterLabels, $filters] = $this->resolveReportParameters($request);

        $summary = $calculationService->getReportSummary($userId, $startDate, $endDate, $filters);
        $categoryReport = $calculationService->getCategoryWiseReport($userId, $startDate, $endDate, $filters);

        $txQuery = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('category')
            ->orderBy('transaction_date', 'desc');

        if (! empty($filters['category_id'])) {
            $txQuery->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type'])) {
            $txQuery->where('type', $filters['type']);
        }
        $transactions = $txQuery->get();

        $viewData = [
            'user' => $user,
            'summary' => $summary,
            'categoryReport' => $categoryReport,
            'transactions' => $transactions,
            'generatedAt' => Carbon::now()->format('F j, Y, g:i a'),
            'periodLabel' => $summary['period_label'],
            'filterLabels' => $filterLabels,
        ];

        // If preview / HTML requested (or print view)
        if ($request->query('format') === 'html' || $request->has('preview')) {
            return response()->view('reports.pdf', $viewData);
        }

        $pdf = Pdf::loadView('reports.pdf', $viewData);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'campus_coin_report_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export the financial statement data and ledger as a structured CSV.
     */
    public function exportCsv(Request $request, FinancialCalculationService $calculationService): StreamedResponse
    {
        $userId = Auth::id();
        $user = Auth::user();

        [$startDate, $endDate, $filterLabels, $filters] = $this->resolveReportParameters($request);

        $summary = $calculationService->getReportSummary($userId, $startDate, $endDate, $filters);
        $categoryReport = $calculationService->getCategoryWiseReport($userId, $startDate, $endDate, $filters);

        $txQuery = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('category')
            ->orderBy('transaction_date', 'desc');

        if (! empty($filters['category_id'])) {
            $txQuery->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type'])) {
            $txQuery->where('type', $filters['type']);
        }
        $transactions = $txQuery->get();

        $filename = 'campus_coin_report_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($user, $summary, $categoryReport, $transactions) {
            $handle = fopen('php://output', 'w');

            // Metadata
            fputcsv($handle, ['Campus Coin — Financial Statement']);
            fputcsv($handle, ['Student', $user->name, 'Email', $user->email]);
            fputcsv($handle, ['Period', $summary['period_label']]);
            fputcsv($handle, ['Generated', Carbon::now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Summary KPIs
            fputcsv($handle, ['--- EXECUTIVE SUMMARY ---']);
            fputcsv($handle, ['Metric', 'Amount / Value']);
            fputcsv($handle, ['Total Inflow', '$'.$summary['total_income']]);
            fputcsv($handle, ['Total Outflow', '$'.$summary['total_expense']]);
            fputcsv($handle, ['Net Cash Movement', '$'.$summary['net_movement']]);
            fputcsv($handle, ['Savings Rate', $summary['savings_rate'].'%']);
            fputcsv($handle, ['Total Entries', $summary['total_count']]);
            fputcsv($handle, []);

            // Category Breakdown
            fputcsv($handle, ['--- CATEGORY BREAKDOWN ---']);
            fputcsv($handle, ['Category', 'Type', 'Spent', '% Share', 'Count', 'Avg Amount', 'Prior Spent', 'Delta']);
            foreach ($categoryReport['categories'] as $c) {
                fputcsv($handle, [
                    $c['name'],
                    $c['type'],
                    $c['spent'],
                    $c['percentage_of_total'].'%',
                    $c['count'],
                    $c['average_amount'],
                    $c['prev_spent'],
                    $c['delta'],
                ]);
            }
            fputcsv($handle, []);

            // Detailed Ledger Entries
            fputcsv($handle, ['--- VERIFIED LEDGER TRANSACTIONS ---']);
            fputcsv($handle, ['Date', 'Merchant', 'Category', 'Type', 'Amount', 'Payment Method', 'Recurring', 'Description']);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->merchant,
                    $t->category?->name ?? 'Uncategorized',
                    strtoupper($t->type),
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

    /**
     * Parse query parameters into date range and filter arrays.
     *
     * @return array{0: Carbon, 1: Carbon, 2: array<int, string>, 3: array{category_id?: ?int, type?: ?string}}
     */
    protected function resolveReportParameters(Request $request): array
    {
        $preset = $request->query('preset', 'this_month');
        $now = Carbon::now();

        switch ($preset) {
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = $now->copy()->subMonths(2)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'last_6_months':
                $startDate = $now->copy()->subMonths(5)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
                $dateFrom = $request->query('date_from');
                $dateTo = $request->query('date_to');
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfMonth();
                break;
            case 'this_month':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        $filterLabels = [];

        $filters = [];
        if ($request->filled('category_id')) {
            $catId = (int) $request->query('category_id');
            $filters['category_id'] = $catId;
            $cat = Category::find($catId);
            if ($cat) {
                $filterLabels[] = "Category: {$cat->name}";
            }
        }

        if ($request->filled('type') && in_array($request->query('type'), ['income', 'expense'], true)) {
            $type = $request->query('type');
            $filters['type'] = $type;
            $filterLabels[] = 'Type: '.ucfirst($type);
        }

        return [$startDate, $endDate, $filterLabels, $filters];
    }
}
