<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;


class BudgetService
{

    public function index($user)
    {
        // 1. تنظيف تلقائي: مسح أي ميزانية تاريخ نهايتها أصغر من تاريخ اليوم الحالي
        $user->budgets()
            ->where('end_date', '<', now()->format('Y-m-d'))
            ->delete();

        // 2. جلب الميزانيات المتبقية (الحية) وحساب المصاريف لكل منها
        return $user->budgets()
            ->with(['category'])
            // بنحسب مجموع عمود amount من جدول الـ transactions للميزانيات الحية فقط
            ->withSum([
                'transactions as total_spent' => function ($query) {
                    $query->where('transaction_type', 'expense')
                        ->whereRaw('transaction_date BETWEEN budgets.start_date AND budgets.end_date');
                }
            ], 'amount')
            ->latest() // ترتيبها من الأحدث للأقدم
            ->get()
            ->map(function ($budget) {
                // لو مفيش مصاريف بنخليها 0 بدل null وبنظبط الفورمات
                $budget->total_spent = number_format($budget->total_spent ?? 0, 2, '.', '');
                return $budget;
            });
    }
    // public function index($user)
    // {
    //     return $user->budgets()->with(['category'])->get()->map(function ($budget) {
           
    //         $totalSpent = Transaction::query() 
    //             ->where('user_id', $budget->user_id)
    //             ->where('category_id', $budget->category_id)
    //             ->where('type', 'expense') 
    //             ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date]) 
    //             ->sum('amount'); 

    //         $budget->total_spent = number_format($totalSpent, 2, '.', '');

    //         return $budget;
    //     });
    // }

    public function store(array $validatedData, $userId)
    {
        // 1. تحديد الـ category_id سواء كان جاي جاهز أو محتاجين نعمل له GetOrCreate
        if (empty($validatedData['category_id'])) {
            $categoryName = $validatedData['category_name'];
            $category = Category::GetOrCreate($categoryName, $userId, 'expense');
            $validatedData['category_id'] = $category->id;
        }

        // خلاص مش محتاجين الاسم في الـ Budget create
        unset($validatedData['category_name']);

        // 2. فحص الميزانية الحالية (سواء الـ ID جاي من بره أو لسه عاملينه فوق)
        // ده بيغنيك عن الشرطين المعقدين اللي كانوا في أول الكود
        $existingBudget = Budget::where('user_id', $userId)
            ->where('category_id', $validatedData['category_id'])
            ->first();

        if ($existingBudget) {
            // بنرجع الموديل وجواه خاصية وهمية عشان الكنترولر يعرف إنه مجاش جديد
            $existingBudget->wasRecentlyCreated = false;
            return $existingBudget;
        }

        // 3. إنشاء الـ Budget في مكان واحد نضيف
        try {
            $validatedData['user_id'] = $userId;
            return Budget::create($validatedData);
        } catch (\Exception $e) {
            Log::error("Budget Creation Failed: " . $e->getMessage());
            return null;
        }
    }
    public function update($id , $validatedData , $user){
        $budget = $user->budgets()->find($id);
        if(!$budget){
            return null;
        }
        $budget->update($validatedData);
        $budget->refresh();
        return $budget;
    }

    public function show($id, $user)
    {
        $budget = $user->budgets()
            ->with(['category'])
            // بنحسب مجموع الـ amount من الـ transactions المطبقة عليها الشروط
            ->withSum([
                'transactions as total_spent' => function ($query) {
                    $query->where('transaction_type', 'expense')
                        ->whereRaw('transaction_date BETWEEN budgets.start_date AND budgets.end_date');
                }
            ], 'amount')
            ->find($id); // أو استخدم findOrFail($id) عشان يرمي 404 لو مش موجودة

        // لو الميزانية موجودة، بنظبط فورمات الـ total_spent
        if ($budget) {
            $budget->total_spent = number_format($budget->total_spent ?? 0, 2, '.', '');
        }

        return $budget;
    }

    public function destroy($id, $user){
        $budget = $user->budgets()->find($id);
        if(!$budget){
            return false;
        }
        return $budget->delete();
    }
}