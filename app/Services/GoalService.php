<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use \App\Notifications\GoalDeadlineNotification;
class GoalService
{
    public function index($user)
    {
        $goals = $user->goals()->get();
        foreach ($goals as $goal) {
            if ($goal->deadline <= now()) {
                $user->notify(new GoalDeadlineNotification($goal));
            } 
        }
        if ($goals->isNotEmpty()) {
            return [
                'message' => 'أهدافك',
                'goals' => $goals
            ];
        }

        return [
            'message' => 'ليس لديك أهداف حالياً',
            'goals' => []
        ];
    }

    public function store($user, $validatedRequest)
    {
        return DB::transaction(function () use ($user, $validatedRequest) {
            $userCategoriesService = new UserCategoriesService();
            $remainingAmount = $userCategoriesService->remainingAmount($user);

            $savedAmount = $validatedRequest['saved_amount'] ?? 0;

            if ($remainingAmount - $savedAmount < 0 || $savedAmount < 0 || $remainingAmount < 0) {
                return [
                    // 'error' => true,
                    'message' => 'المبلغ الذي تحاول إيداعه يتجاوز المبلغ المتبقي في حسابك أو غير صالح',
                ];
            }

            $userGoalsExist = $user->goals()->where('goal_name', $validatedRequest['goal_name'])->exists();
            if ($userGoalsExist) {
                return [
                    // 'error' => true,
                    'message' => 'هذا الهدف موجود بالفعل',
                ];
            }

            $validatedRequest['saved_amount'] = $savedAmount;
            $goal = $user->goals()->create($validatedRequest);

            // تسجيل العملية كـ Expense فقط إذا كان هناك مبلغ مودع مبدئياً
            if ($savedAmount > 0) {
                $user->transactions()->create([
                    'amount' => $savedAmount,
                    'transaction_type' => 'expense',
                    'transaction_date' => now()->format('Y-m-d H:i:s'), // تعديل صيغة الوقت
                    'notes' => 'إيداع في الهدف: ' . $goal->goal_name,
                    'category' => 'Goal Deposit',
                    'category_id' => Category::GetOrCreate(
                        'Goal Deposit',
                        $user->id,
                        'expense'
                    )->id,
                ]);
            }

            return [
                'message' => 'تم إنشاء الهدف بنجاح',
                'goals' => $goal
            ];
        });
    }

    public function show($user, $goalId)
    {
        $goal = $user->goals()->find($goalId);
        if ($goal) {
            return [
                'goal' => $goal
            ];
        }

        return [
            'message' => 'هذا الهدف غير موجود',
            'goal' => []
        ];
    }

    public function update($user, $validatedRequest, $id)
    {
        $goal = $user->goals()->find($id);

        if (!$goal) {
            return [
                // 'error' => true,
                'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية تعديله',
            ];
        }

        // حماية النظام: منع تعديل المبالغ أو اسم الهدف من هنا لعدم تخريب الـ Log والـ Transactions المربوطة بالاسم
        unset($validatedRequest['saved_amount']);
        unset($validatedRequest['goal_name']);

        $goal->update($validatedRequest);

        return [
            'message' => 'تم تعديل الهدف بنجاح',
            'goal' => $goal
        ];
    }

    public function destroy($user, $id)
    {
        return DB::transaction(function () use ($user, $id) {
            $goal = $user->goals()->find($id);

            if (!$goal) {
                return [
                    'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية حذفه',
                ];
            }

            if ($goal->saved_amount > 0) {
                // 1. جلب وحذف عمليات الشهر الحالي المربوطة بالهدف فوراً
                $currentMonthTransactions = $user->transactions()
                    ->where('notes', 'إيداع في الهدف: ' . $goal->goal_name)
                    ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->get();

                foreach ($currentMonthTransactions as $transaction) {
                    $transaction->delete();
                }

                // 2. التحقق من وجود عمليات قديمة (خارج الشهر الحالي)
                $hasOldTransactions = $user->transactions()
                    ->where('notes', 'إيداع في الهدف: ' . $goal->goal_name)
                    ->whereNotBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->exists();

                // 3. إرجاع المبلغ كـ Income "مرة واحدة فقط" إذا وُجدت عمليات قديمة
                if ($hasOldTransactions) {
                    $transactionService = new TransactionService();
                    $transactionService->storeTransaction([
                        'amount' => $goal->saved_amount,
                        'transaction_type' => 'income',
                        'transaction_date' => now()->format('Y-m-d H:i:s'),
                        'notes' => 'إرجاع المبلغ المحفوظ بعد حذف الهدف: ' . $goal->goal_name,
                        'category' => 'Goal remove',
                    ], $user->id);
                }
            }

            // 4. حذف الهدف نفسه
            $goal->delete();

            return [
                'message' => 'تم حذف الهدف بنجاح',
            ];
        });
    }

    public function depositToGoal($user, $validatedRequest, $id)
    {
        return DB::transaction(function () use ($user, $validatedRequest, $id) {
            $goal = $user->goals()->find($id);

            if (!$goal) {
                return [
                    // 'error' => true,
                    'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية تعديله',
                ];
            }

            $userCategoriesService = new UserCategoriesService();
            $remainingAmount = $userCategoriesService->remainingAmount($user);

            if ($remainingAmount - $validatedRequest['amount'] < 0 || $validatedRequest['amount'] <= 0 || $remainingAmount <= 0) {
                return [
                    // 'error' => true,
                    'message' => 'المبلغ الذي تحاول إيداعه يتجاوز المبلغ المتبقي في حسابك أو غير صالح',
                ];
            }

            $newSavedAmount = $goal->saved_amount + $validatedRequest['amount'];
            if ($newSavedAmount > $goal->target_amount) {
                return [
                    // 'error' => true,
                    'message' => 'المبلغ المودع يتجاوز المبلغ المستهدف للهدف',
                ];
            }

            // تسجيل عملية الإيداع في جدول العمليات
            $user->transactions()->create([
                'amount' => $validatedRequest['amount'],
                'transaction_type' => 'expense',
                'transaction_date' => now()->format('Y-m-d H:i:s'), // تعديل صيغة الوقت
                'notes' => 'إيداع في الهدف: ' . $goal->goal_name,
                'category' => 'Goal Deposit',
                'category_id' => Category::GetOrCreate(
                    'Goal Deposit',
                    $user->id,
                    'expense'
                )->id,
            ]);

            $goal->update(['saved_amount' => $newSavedAmount]);
            $goal->refresh();

            return [
                'message' => 'تم إيداع المبلغ بنجاح',
                'goal' => $goal
            ];
        });
    }
}