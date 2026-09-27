<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('transactions:generate-recurring {--date= : Generate occurrences due on or before this date}')]
#[Description('Generate due monthly recurring transactions')]
class GenerateRecurringTransactions extends Command
{
    public function handle(): int
    {
        $runDate = Carbon::parse($this->option('date') ?: now()->toDateString())->startOfDay();
        $created = 0;

        Transaction::query()
            ->where('is_recurring', true)
            ->where('recurrence_frequency', 'monthly')
            ->whereDate('next_occurrence_date', '<=', $runDate->toDateString())
            ->pluck('id')
            ->each(function (int $transactionId) use ($runDate, &$created): void {
                DB::transaction(function () use ($transactionId, $runDate, &$created): void {
                    $source = Transaction::query()->lockForUpdate()->find($transactionId);

                    if (! $source || ! $source->is_recurring || ! $source->next_occurrence_date) {
                        return;
                    }

                    $nextDate = $source->next_occurrence_date->copy()->startOfDay();

                    while ($nextDate->lte($runDate)) {
                        $occurrence = Transaction::firstOrCreate([
                            'recurring_source_id' => $source->id,
                            'transaction_date' => $nextDate->toDateString(),
                        ], [
                            'user_id' => $source->user_id,
                            'category_id' => $source->category_id,
                            'type' => $source->type,
                            'amount' => $source->amount,
                            'merchant' => $source->merchant,
                            'description' => $source->description,
                            'payment_method' => $source->payment_method,
                            'is_recurring' => false,
                            'ai_suggested' => false,
                        ]);

                        if ($occurrence->wasRecentlyCreated) {
                            $created++;
                        }

                        $nextDate->addMonthNoOverflow();
                    }

                    $source->update(['next_occurrence_date' => $nextDate->toDateString()]);
                });
            });

        $this->info("Generated {$created} recurring transaction(s).");

        return self::SUCCESS;
    }
}
