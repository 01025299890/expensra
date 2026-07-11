<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Services\UserCategoriesService;
use App\Models\User;
use App\Notifications\CategoryBudgetEndDateNotification;
use App\Notifications\MonthlyBudgetEndDateNotification;

class TransactionService
{
    public function userTransactions($user)
    {
        foreach ($user->budgets as $CategoryBudget) {
            if ($CategoryBudget->end_date <= now()->format('Y-m-d')) {
                $user->notify(new CategoryBudgetEndDateNotification($CategoryBudget));
            }
        }
        foreach ($user->monthlyBudgets as $MonthlyBudget) {
            if ($MonthlyBudget->end_date <= now()->format('Y-m-d')) {
                $user->notify(new MonthlyBudgetEndDateNotification($MonthlyBudget));
            }
        }
        return $user->transactions()
            ->latest()
            ->get();
    }

    public function storeTransaction(array $validatedData, $userId)
{
        if (empty($validatedData)) {
            return ['error' => 'البيانات المدخلة فارغة. يرجى التحقق من البيانات المدخلة.'];
        }

        // يزة التخطي الإجبري: بنوف لو مبعو true أو 1
        $forceSave = filter_var($validatedData['force_save'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // 1. تأمين الـ transaction_type وتوحيد امسمى المكتوب الدتا بيز
        $type = $validatedData['transaction_type'] ?? $validatedData['type'] ?? 'expense';
        if ($type === 'outcome' || $type === 'expenses') {
            $type = 'expense';
        } elseif ($type === 'incomes') {
            $type = 'income';
        }
        $validatedData['transaction_type'] = $type;

        // 2. لتحق من نسبة ثقة لـ AI (Confidence)
        // ملحوظة: لو عوز الـ force_save يتخطى حماة الـ AI كمان، ضيف (!$forceSave && ...) لشط
        if (isset($validatedData['confidence']) && $validatedData['confidence'] <= 0.5) {
            return ['error' => 'نسبة الثقة في تصنيف المعاملة منخفة جدًا. يرجى التحقق من البيانات المدخلة.'];
        }

        // 3. الحقق من الرصد والـ Monthly Budget لو المعملة "مروف"
        if ($validatedData['transaction_type'] === 'expense') {
            $amount = $validatedData['amount'] ?? 0;
            $transactionDate = $validatedData['transaction_date'] ?? now()->format('Y-m-d H:i:s');

            // أ) تشك الميزانية الشهية المخصصة
            $activeMonthlyBudget = MonthlyBudget::where('user_id', $userId)
                ->where('start_date', '<=', $transactionDate)
                ->where('end_date', '>=', $transactionDate)
                ->first();

            // ⚡ تعديل: لو فيه force_save يتخطى تشيك اليمت بتاع اليزانة
            if ($activeMonthlyBudget && !$forceSave) {
                // حساب مجمع المصاريف في نفس فرة الميزانية الددة
                $totalSpentInPeriod = Transaction::where('user_id', $userId)
                    ->where('transaction_type', 'expense')
                    ->whereBetween('transaction_date', [$activeMonthlyBudget->start_date, $activeMonthlyBudget->end_date])
                    ->sum('amount');

                if (($totalSpentInPeriod + $amount) > $activeMonthlyBudget->amount) {
                    return ['error' => 'لقد تجاوزت الحد المخصص للميزانية الشهري. الحد المخصص: ' . $activeMonthlyBudget->amount . ', هل تريد إضافة المعاملة على أي حال؟'];
                }
                if(($totalSpentInPeriod + $amount) < $activeMonthlyBudget->amount && ($totalSpentInPeriod + $amount)/ $activeMonthlyBudget->amount >= 0.8){
                    return ['error' => 'لقد تجاوزت نسبة 80% من الحد المخصص للميزانية الشهري. الحد المخصص: ' . $activeMonthlyBudget->amount . ', هل تريد إضافة المعاملة على أي حال؟'];
                    
                }
            }

            // ب) تشيك امفظة العام (الرصيد المتبقي الكلي)
            $userCategoriesService = new UserCategoriesService;
            $remainingAmount = $userCategoriesService->remainingAmount(auth()->user());

            // ⚡ تعديل: لو فه force_save هيتطى تشيك عدم كفاية الريد الكلي
            
                if ($amount <= 0 || $remainingAmount <= 0 || ($remainingAmount - $amount) < 0) {
                    return ['error' => 'الرصيد المتبقي غير كافٍ لإضافة هذه المعاملة. الرصيد المتبقي: ' . $remainingAmount];
                }
            
        }

        // 4. إكمال البيانات لأساسي قبل احفظ
        $validatedData['user_id'] = $userId;
        $validatedData['transaction_date'] = $validatedData['transaction_date'] ?? now()->format('Y-m-d H:i:s');

        // 5. جلب الفئة أو إنشائها لقائيً
        $categoryName = $validatedData['category'] ?? 'Uncategorized';
        $category = Category::GetOrCreate(
            $categoryName,
            $userId,
            $validatedData['transaction_type']
        );

        $validatedData['category_id'] = $category->id;

        // تنظيف البانا الزائدة من الـ Array لضمان عدم حدث مشاكل مع الـ Mass Assignment
        unset($validatedData['category'], $validatedData['confidence'], $validatedData['type'], $validatedData['force_save']);

        // 6. إنشاء المعامة وحفظها ي قاعدة لبيانات
        return Transaction::create($validatedData);
    }
    
    public function showTransaction($id, $user)
    {
        $transaction = $user->transactions()->find($id);
        if (!$transaction) {
            return null;
        }
        return $transaction;
    }

    public function updateTransaction($id, $validatedData, $user)
    {
        $transaction = $user->transactions()->find($id);
        if (!$transaction) {
            return null;
        }

        $userId = $user->id; // تعديل الـ FK لـ id الموحد
        $categoryType = $validatedData['transaction_type'] ?? 'uncategorized';

        if (isset($validatedData['category'])) {
            $category = Category::GetOrCreate(
                $validatedData['category'],
                $userId,
                $categoryType
            );
            $validatedData['category_id'] = $category->id;

            unset($validatedData['category']);
        }

        $transaction->update($validatedData);
        $transaction->refresh();
        return $transaction;
    }

    public function deleteTransaction($id, $user)
    {
        $transaction = $user->transactions()->find($id);
        if (!$transaction) {
            return null;
        }
        $transaction->delete();
        return true;
    }

    public function monthlyTransactions($user, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $transactions = $user->transactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->latest()
            ->get();

        $MonthIncome = $user->transactions()
            ->where('transaction_type', 'income')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $MonthExpense = $user->transactions()
            ->where('transaction_type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        return [
            'transactions' => $transactions,
            'MonthIncome' => $MonthIncome,
            'MonthExpense' => $MonthExpense,
        ];
    }

    public function lastSixMonthsIncomeAndExpense($user){
    
        $lastSixMonths = now()->subMonths(5)->startOfMonth();
        $transactions = $user->transactions()
            ->where('transaction_date', '>=', $lastSixMonths)
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->transaction_date->format('Y-M');
            });

        $result = [];
        foreach ($transactions as $month => $monthTransactions) {
            $income = $monthTransactions->where('transaction_type', 'income')->sum('amount');
            $expense = $monthTransactions->where('transaction_type', 'expense')->sum('amount');
            

            $result[] = [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
            ];
        }

        return collect($result)->sortByDesc('month')->values();
    }
}