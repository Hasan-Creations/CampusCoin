<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->enum('type', ['income', 'expense'])->index();
            $table->decimal('amount', 10, 2);
            $table->string('merchant', 150);
            $table->text('description')->nullable();
            $table->date('transaction_date')->index();
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'upi', 'digital_wallet', 'other'])->default('card');
            $table->boolean('is_recurring')->default(false);
            $table->boolean('ai_suggested')->default(false);
            $table->decimal('ai_confidence', 3, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
