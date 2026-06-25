<?php

namespace App\Console\Commands;

use App\Services\UserCategoriesService;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;
use App\Notifications\MonthlyResetNotification;

class MonthlyResetBalances extends Command
{
    // الاسم اللي بننادي بيه على الأمر
    protected $signature = 'app:monthly-reset-balances';
    protected $description = 'Reset monthly incomes and expenses, and notify users about remaining balance';

    public function handle()
    {
   
        $users = User::all();

        foreach ($users as $user) {
            $categoriesService = new UserCategoriesService();
            $remainingBalance = $categoriesService->lastMonthRemainingAmount($user);
            if ($remainingBalance > 0) {
                $user->notify(new MonthlyResetNotification($remainingBalance));
            }
        }

        $this->info('تم تحديث الموازنات الشهرية بنجاح.');
    }
}