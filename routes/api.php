<?php

use App\Http\Controllers\SendDataToAiAndStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserCategoriesController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\MonthlyBudgetController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MonthlyResetController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GetNotificationForUserController;
use App\Http\Controllers\UpgradeController;
use function Pest\Laravel\seed;


Route::middleware(['auth:sanctum', 'verified'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/transactions/monthly', [TransactionController::class, 'monthlyTransactions']);
    Route::apiResource('transactions', TransactionController::class)->middleware('check.budget');
    Route::post('/send-to-ai', [SendDataToAiAndStore::class, 'storeByAi']);
    Route::post('/categories', [UserCategoriesController::class, 'userCategories']);
    Route::post('/categories/expenses', [UserCategoriesController::class, 'expensesByCategory']);
    Route::post('/categories/incomes', [UserCategoriesController::class, 'incomesByCategory']);
    Route::get('/categories/remaining-amount', [UserCategoriesController::class, 'remainingAmount']);
    Route::apiResource('budgets', BudgetController::class)->middleware('isPremium');
    Route::apiResource('monthly-budgets', MonthlyBudgetController::class);
    Route::apiResource('goals', GoalController::class);
    Route::post('/goals/{id}/deposit', [GoalController::class, 'depositToGoal']);
    Route::post('/monthly-surplus', [MonthlyResetController::class, 'handleMonthlySurplus']);
    Route::get('/notification', [GetNotificationForUserController::class, 'getMyNotifications']);
    Route::get('upgrade-to-premium', [UpgradeController::class, 'upgrade']);
});
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'allUsers']);
    Route::get('/admin/users/normal', [AdminController::class, 'normalUsers']);
    Route::get('/admin/users/premium', [AdminController::class, 'premiumUsers']);
    Route::get('/admin/users/admin', [AdminController::class, 'adminUsers']);
    Route::get('/admin/users/{id}/transactions', [AdminController::class, 'userTransactions']);
    Route::get('/admin/users/count', [AdminController::class, 'UsersCount']);
    Route::get('/admin/users/normal/count', [AdminController::class, 'normalUsersCount']);
    Route::get('/admin/users/premium/count', [AdminController::class, 'premiumUsersCount']);
    Route::get('/admin/users/admin/count', [AdminController::class, 'adminUsersCount']);
    Route::get('/admin/users/search', [AdminController::class, 'searchUsers']);
    Route::post('/admin/users/{id}/upgrade', [AdminController::class, 'upgradeUserToPremium']);
});



require __DIR__.'/auth.php';