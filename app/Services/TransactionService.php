<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Services\UserCategoriesService;
use App\Models\User;

class TransactionService
{
    public function userTransactions($user)
    {
        return $user->transactions()
            ->latest()
            ->get();
    }

    public function storeTransaction(array $validatedData, $userId)
    {
        if (empty($validatedData)) {
            return ['error' => 'البيانات المدخلة فارغة. يرجى التحقق من البيانات المدخلة.'];
        }

        // ميزة التخطي الإجباري: بنشوف لو مبعوت true أو 1
        $forceSave = filter_var($validatedData['force_save'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // 1. تأمين الـ transaction_type وتوحيد المسمى المكتوب بالداتا بيز
        $type = $validatedData['transaction_type'] ?? $validatedData['type'] ?? 'expense';
        if ($type === 'outcome' || $type === 'expenses') {
            $type = 'expense';
        } elseif ($type === 'incomes') {
            $type = 'income';
        }
        $validatedData['transaction_type'] = $type;

        // 2. التحقق من نسبة ثقة الـ AI (Confidence)
        // ملحوظة: لو عاوز الـ force_save يتخطى حماية الـ AI كمان، ضيف (!$forceSave && ...) للشط
        if (isset($validatedData['confidence']) && $validatedData['confidence'] <= 0.5) {
            return ['error' => 'نسبة الثقة في تصنيف المعاملة منخفضة جدًا. يرجى التحقق من البيانات المدخلة.'];
        }

        // 3. التحقق من الرصيد والـ Monthly Budget لو المعاملة "مصروف"
        if ($validatedData['transaction_type'] === 'expense') {
            $amount = $validatedData['amount'] ?? 0;
            $transactionDate = $validatedData['transaction_date'] ?? now()->format('Y-m-d :H:i:s');

            // أ) تشيك الميزانية الشهرية المخصصة
            $activeMonthlyBudget = MonthlyBudget::where('user_id', $userId)
                ->where('start_date', '<=', $transactionDate)
                ->where('end_date', '>=', $transactionDate)
                ->first();

            // ⚡ تعديل: لو فيه force_save هيتخطى تشيك الليميت بتاع الميزانية
            if ($activeMonthlyBudget && !$forceSave) {
                // حساب مجموع المصاريف في نفس فترة الميزانية المحددة
                $totalSpentInPeriod = Transaction::where('user_id', $userId)
                    ->where('transaction_type', 'expense')
                    ->whereBetween('transaction_date', [$activeMonthlyBudget->start_date, $activeMonthlyBudget->end_date])
                    ->sum('amount');

                if (($totalSpentInPeriod + $amount) > $activeMonthlyBudget->amount) {
                    return ['error' => 'لقد تجاوزت الحد المخصص للميزانية الشهرية. الحد المخصص: ' . $activeMonthlyBudget->amount . ', هل تريد إضافة المعاملة على أي حال؟'];
                }
            }

            // ب) تشيك المحفظة العام (الرصيد المتبقي الكلي)
            $userCategoriesService = new UserCategoriesService;
            $remainingAmount = $userCategoriesService->remainingAmount(auth()->user());

            // ⚡ تعديل: لو فيه force_save هيتخطى تشيك عدم كفاية الرصيد الكلي
            
                if ($amount <= 0 || $remainingAmount <= 0 || ($remainingAmount - $amount) < 0) {
                    return ['error' => 'الرصيد المتبقي غير كافٍ لإضافة هذه المعاملة. الرصيد المتبقي: ' . $remainingAmount];
                }
            
        }

        // 4. إكمال البيانات الأساسية قبل الحفظ
        $validatedData['user_id'] = $userId;
        $validatedData['transaction_date'] = $validatedData['transaction_date'] ?? now()->format('Y-m-d:H:i:s');

        // 5. جلب الفئة أو إنشائها تلقائياً
        $categoryName = $validatedData['category'] ?? 'Uncategorized';
        $category = Category::GetOrCreate(
            $categoryName,
            $userId,
            $validatedData['transaction_type']
        );

        $validatedData['category_id'] = $category->id;

        // تنظيف البيانات الزائدة من الـ Array لضمان عدم حدوث مشاكل مع الـ Mass Assignment
        unset($validatedData['category'], $validatedData['confidence'], $validatedData['type'], $validatedData['force_save']);

        // 6. إنشاء المعاملة وحفظها في قاعدة البيانات
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
}