<?php

namespace App\Services;

use App\Models\MonthlyBudget;
use App\Models\Transaction;

class MonthlyBudgetService
{
    /**
     * جلب الميزانيات المخصصة بعد تنظيف المنتهي منها وحساب المصاريف من جدول المعاملات مباشرة
     */
    public function index($user)
    {
        // 1. تنظيف تلقائي: مسح أي ميزانية تاريخ نهايتها أصغر من تاريخ اليوم الحالي
        $user->monthlyBudgets()
            ->where('end_date', '<', now()->format('Y-m-d'))
            ->delete();

        // 2. جلب الميزانيات الحية الحالية للمستخدم
        $budgets = $user->monthlyBudgets()->latest()->get();

        // 3. حساب المصاريف يدويًا لكل ميزانية بشكل ديناميكي ومستقل عن العلاقات
        $budgets->map(function ($budget) use ($user) {
            $totalSpent = Transaction::where('user_id', $user->id)
                ->where('transaction_type', 'expense')
                ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
                ->sum('amount');

            // إضافة القيمة المصروفة وتنسيقها للـ API
            $budget->total_spent = number_format($totalSpent, 2, '.', '');
            return $budget;
        });

        return $budgets;
    }

    public function store(array $data, $userId)
    {
        // تنظيف الميزانيات القديمة أولاً لضمان عدم حدوث تداخل مع ميزانية ميتة
        MonthlyBudget::where('user_id', $userId)
            ->where('end_date', '<', now()->format('Y-m-d'))
            ->delete();

        // تشيك التداخل للميزانيات الحية المتبقية
        $isOverlapping = MonthlyBudget::where('user_id', $userId)
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']]);
            })->exists();

        if ($isOverlapping) {
            return 'overlapping';
        }

        $data['user_id'] = $userId;
        return MonthlyBudget::create($data);
    }

    public function show($id, $user)
    {
        // استخدم find بدل findOrFail عشان نتحكم في الـ Flow براحتنا
        $budget = $user->monthlyBudgets()->find($id);

        // 1. لو الميزانية مش موجودة أصلاً من البداية
        if (!$budget) {
            return null;
        }

        // 2. لو الميزانية منتهية: امسحها ورجع null
        if ($budget->end_date < now()->format('Y-m-d')) {
            $budget->delete();
            return null;
        }

        // 3. حساب الـ total_spent لو الميزانية موجودة وصالحة
        $budget->total_spent = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
            ->sum('amount');

        return $budget;
    }

    public function update($id, array $data, $user)
    {
        $budget = $user->monthlyBudgets()->find($id);
        if (!$budget) {
            return null;
        }

        // لو الـ Request مش جاي فيه تواريخ، استخدم التواريخ الحالية المخزنة في الداتا بيز عشان الـ check ميتكسرش
        $startDate = $data['start_date'] ?? $budget->start_date;
        $endDate = $data['end_date'] ?? $budget->end_date;

        // تشيك التداخل مع الميزانيات الأخرى مع استثناء الميزانية الحالية نفسها
        $isOverlapping = MonthlyBudget::where('user_id', $user->id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->exists();

        if ($isOverlapping) {
            return 'overlapping';
        }

        $budget->update($data);
        return $budget->refresh();
    }

    public function destroy($id, $user)
    {
        $budget = $user->monthlyBudgets()->find($id);
        if (!$budget) {
            return null;
        }
        return $budget->delete();
    }
}