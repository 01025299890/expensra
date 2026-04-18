<?php

use App\Http\Controllers\SendDataToAiAndStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserCategoriesController;
use App\Http\Controllers\BudgetController;


Route::middleware(['auth:sanctum', 'verified'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::apiResource('transactions', TransactionController::class)->middleware('check.budget');
    Route::post('/send-to-ai', [SendDataToAiAndStore::class, 'storeByAi']);
    Route::get('/categories', [UserCategoriesController::class, 'userCategories']);
    Route::get('/categories/expenses', [UserCategoriesController::class, 'expensesByCategory']);
    Route::get('/categories/incomes', [UserCategoriesController::class, 'incomesByCategory']);
    Route::apiResource('budgets', BudgetController::class);
});

require __DIR__.'/auth.php';