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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable(false);
            $table->string('icon')->nullable();
            $table->enum('type', ['income', 'expense'])->nullable(false);
            $table->timestamps();
            $table->index(['user_id', 'type']);
            //unique => نوع من انواع ال index  يضمن عدم تكرار نفس الاسم لنفس المستخدم
            // $table->unique(['user_id', 'name']);
            // $table->index(['user_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // لحذف Index واحد (تمرر اسم العمود داخل مصفوفة)
            // $table->dropIndex(['user_id']);

            // // لحذف Index آخر
            // $table->dropIndex(['name']);

            // ملاحظة: إذا كان الـ Index مركباً (Composite Index) يجمع العمودين معاً:
            $table->dropUnique(['user_id', 'name']);
        });
    }
};
