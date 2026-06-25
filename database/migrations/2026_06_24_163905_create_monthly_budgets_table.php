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
        Schema::create('monthly_budgets', function (Blueprint $table) {
            $table->id('id'); // Primary Key مخصص
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2); // المبلغ الأقصى للميزانية
            $table->date('start_date')->nullable(); // بداية الفترة المخصصة
            $table->date('end_date');   // نهاية الفترة المخصصة
            $table->timestamps();

            // الربط مع جدول المستخدمين بناءً على الـ ERD بتاعك
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'start_date', 'end_date'], 'unique_user_budget_period'); // لضمان عدم تكرار الميزانية لنفس المستخدم في نفس الفترة
            $table->index(['start_date', 'end_date']); // لتحسين أداء الاستعلامات على الفترة الزمنية
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_budgets');
    }
};
