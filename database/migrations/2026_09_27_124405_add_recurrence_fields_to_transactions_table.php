<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('recurrence_frequency')->nullable();
            $table->date('next_occurrence_date')->nullable();
            $table->foreignId('recurring_source_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->unique(['recurring_source_id', 'transaction_date'], 'transactions_recurring_source_date_unique');
            $table->index(['is_recurring', 'next_occurrence_date'], 'transactions_recurring_schedule_index');
        });

        DB::table('transactions')
            ->where('is_recurring', true)
            ->orderBy('id')
            ->chunkById(100, function ($transactions): void {
                foreach ($transactions as $transaction) {
                    DB::table('transactions')->where('id', $transaction->id)->update([
                        'recurrence_frequency' => 'monthly',
                        'next_occurrence_date' => Carbon::parse($transaction->transaction_date)->addMonthNoOverflow()->toDateString(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_recurring_source_date_unique');
            $table->dropIndex('transactions_recurring_schedule_index');
            $table->dropConstrainedForeignId('recurring_source_id');
            $table->dropColumn(['recurrence_frequency', 'next_occurrence_date']);
        });
    }
};
