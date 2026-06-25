<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;
// نضمن إننا بنستدعي الـ Namespaces بتاعة السيرفسز التانية صح
use App\Services\UserCategoriesService;
use App\Services\TransactionService;
use App\Services\GoalService;

class MonthlyResetService
{
    public function handleSurplus(array $data, $user)
    {
        // استخدام Database Transaction للأمان
        return DB::transaction(function () use ($data, $user) {

            // 1. حساب الفائض الفعلي للشهر المنتهي
            $categoriesService = new UserCategoriesService();
            $remainingAmount = $categoriesService->lastMonthRemainingAmount($user);

            // تأمين: لو الفائض بـ 0 أو أقل مش هنعمل حاجة
            if ($remainingAmount <= 0) {
                return [
                    'error' => true,
                    'message' => 'لا يوجد فائض للشهر المنتهي، لا يمكن تنفيذ أي إجراء.',
                ];
            }
            $pendingNotification = $user->unreadNotifications()
                    ->where('type', 'App\Notifications\MonthlyResetNotification')
                    ->first();
            if($pendingNotification){
                // 2. سيناريو الترحيل كـ Income
                if ($data['action'] === 'to_income') {
                    $transactionService = new TransactionService();
    
                    $result = $transactionService->storeTransaction([
                        'amount' => $remainingAmount, // الفائض الفعلي كاملاً
                        'transaction_type' => 'income',
                        'category' => 'فائض الشهر السابق',
                        'transaction_date' => now()->format('Y-m-d'),
                        'notes' => 'إضافة فائض الشهر السابق إلى الدخل',
                    ], $user->id);
    
                    // طالما تم ترحيله كاملاً للدخل، نمسح الإشعار فوراً
                        $pendingNotification->delete();
                    
                }
                    return $result;
                
                
                // 3. سيناريو الترحيل إلى Goal (جول)
                if ($data['action'] === 'to_goal') {
                    $goalService = new GoalService();
    
                    // بنقرأ القيمة المبعوتة من الفرونت (الـ 500 مثلاً)، وإذا مش مبعوتة بياخد المبلغ كله افتراضياً
                    $amountToProcess = $data['amount'] ?? $remainingAmount;
                    $data['amount'] = $amountToProcess;
    
                    $result = $goalService->depositToGoal($user, $data, $data['goal_id']);
    
                    // التعديل هنا: بنطرح القيمة اللي اتوزعت حالياً من إجمالي الفائض عشان نشوف خلص ولا لسه
                    if (($remainingAmount - $amountToProcess) <= 0) {
                        $pendingNotification = $user->unreadNotifications()
                            ->where('type', 'App\Notifications\MonthlyResetNotification')
                            ->first();
    
                        if ($pendingNotification) {
                            $pendingNotification->delete();
                        }
                    }
    
                    return $result;
                }
            }
            return [
                'error' => true,
                'message' => 'ترحيل الفائض لا يسمح به الا في أول أيام الشهر الجديد، أو تم بالفعل ترحيل الفائض.',
            ];
        });
    }
}