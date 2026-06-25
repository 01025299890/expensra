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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->nullable(false);
            $table->enum('transaction_type', ['income', 'expense'])->nullable(false);
            $table->dateTime('transaction_date')->nullable(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'transaction_type']);
            $table->index(['category_id', 'user_id']);

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
