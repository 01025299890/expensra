<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
// use Illuminate\Support - Collection;

class UserCategoriesService
{
    /**
     * جلب كافة التصنيفات المتاحة للمستخدم (العامة + الخاصة به)
     */
    public function userCategories($user, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // جلب كل الفئات المتاحة (العامة + الخاصة باليوزر ده) بدون أي تكرار
        return Category::visibleToUser()
            ->with([
                'transactions' => function ($query) use ($user, $month, $year) {
                    // بنجلب المعاملات بتاعة اليوزر للشهر ده بس جوه الفئات
                    $query->where('user_id', $user->id)
                        ->whereYear('transaction_date', $year)
                        ->whereMonth('transaction_date', $month);
                }
            ])
            ->get();
    }

    /**
     * حساب المصاريف لكل فئة (بما في ذلك الفئات العامة)
     */
    public function expensesByCategory($user, $month = null, $year = null)
    {
        // خذ الشهر والسنة الحاليين كقيم افتراضية إذا لم يتم تمريرهم
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // 1. حساب إجمالي المصاريف للمستخدم في هذا الشهر مباشرة من الداتا بيز
        $totalExpenses = $user->transactions()
            ->expenses()
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum('amount');

        // 2. جلب الفئات النشطة فقط وحساب مجموع مصاريفها في خطوة واحدة
        $expensesByCategory = Category::visibleToUser()
            // بنضمن إننا مش هنجيب غير الفئات اللي ليها مصاريف فعلاً في الشهر ده
            ->whereHas('transactions', function ($query) use ($user, $month, $year) {
                $query->where('user_id', $user->id)
                    ->expenses()
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month);
            })
            // بنحسب المجموع مباشرة من الداتا بيز في حقل ديناميكي اسمه expenses
            ->withSum([
                'transactions as expenses' => function ($query) use ($user, $month, $year) {
                    $query->where('user_id', $user->id)
                        ->expenses()
                        ->whereYear('transaction_date', $year)
                        ->whereMonth('transaction_date', $month);
                }
            ], 'amount')
            ->get()
            // عمل Map خفيف فقط لتشكيل الـ الـ Response المطلوب
            ->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'category_icon' => $category->icon,
                    'expenses' => number_format($category->expenses ?? 0, 2, '.', ''),
                ];
            });

        return [
            'total_expenses' => number_format($totalExpenses, 2, '.', ''),
            'expenses_by_category' => $expensesByCategory
        ];
    }

    /**
     * حساب الدخل لكل فئة (بما في ذلك الفئات العامة)
     */
    public function incomesByCategory($user, $month = null, $year = null)
    {
        // تحديد الشهر والسنة الحاليين كقيم افتراضية
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // 1. حساب إجمالي الدخل للمستخدم في هذا الشهر من الداتا بيز مباشرة
        $totalIncomes = $user->transactions()
            ->incomes()
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum('amount');

        // 2. جلب فئات الدخل النشطة فقط وحساب مجموعها في خطوة واحدة
        $incomesByCategory = Category::visibleToUser()
            // الـ الداتا بيز هترجع فقط الفئات اللي دخلها معاملة income في الشهر ده
            ->whereHas('transactions', function ($query) use ($user, $month, $year) {
                $query->where('user_id', $user->id)
                    ->incomes()
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month);
            })
            // حساب المجموع مباشرة في الاستعلام وتخزينه في حقل total_incomes
            ->withSum([
                'transactions as total_incomes' => function ($query) use ($user, $month, $year) {
                    $query->where('user_id', $user->id)
                        ->incomes()
                        ->whereYear('transaction_date', $year)
                        ->whereMonth('transaction_date', $month);
                }
            ], 'amount')
            ->get()
            // تشكيل الـ Response النهائي بشكل خفيف وسريع
            ->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'category_icon' => $category->icon,
                    'total_incomes' => number_format($category->total_incomes ?? 0, 2, '.', ''),
                ];
            });

        return [
            'total_incomes' => number_format($totalIncomes, 2, '.', ''),
            'incomes_by_category' => $incomesByCategory
        ];
    }

    public function remainingAmount($user)
    {
        // حساب مصاريف الشهر الحالي
        $allExpenses = $user->transactions()
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    
        // حساب دخل الشهر الحالي
        $allIncome = $user->transactions()
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
            $remainingAmount = $allIncome - $allExpenses;
    
        // المتبقي الصافي المظبوط
        return $remainingAmount >= 0 ? number_format($remainingAmount, 2, '.', '') : '0.00';
    }
    
    public function lastMonthExpenses($user)
    {
        $startOfLastMonth = now()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
        $endOfLastMonth = now()->subMonth()->endOfMonth()->format('Y-m-d H:i:s');
        // حساب مصاريف الشهر السابق
        $lastMonthExpenses = $user->transactions()
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');
    
        return number_format($lastMonthExpenses, 2, '.', '');
    }
    public function lastMonthIncome($user)
    {
        $startOfLastMonth = now()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
        $endOfLastMonth = now()->subMonth()->endOfMonth()->format('Y-m-d H:i:s');
        // حساب دخل الشهر السابق
        $lastMonthIncome = $user->transactions()
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');
    
        return number_format($lastMonthIncome, 2, '.', '');
    }
    public function lastMonthRemainingAmount($user)
    {
        // حساب مصاريف الشهر السابق
        $lastMonthExpenses =  $this->lastMonthExpenses($user);
        // حساب دخل الشهر السابق
        $lastMonthIncome = $this->lastMonthIncome($user);
        // المتبقي الصافي المظبوط
        return $lastMonthIncome - $lastMonthExpenses >= 0 ? number_format($lastMonthIncome - $lastMonthExpenses, 2, '.', '') : '0.00';
    }
}