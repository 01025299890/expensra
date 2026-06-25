<?php

namespace App\Http\Controllers;

use App\Services\UserCategoriesService;
use App\Http\Requests\CategoryDateRequest;
use App\Http\Requests\ExpensesDateRequest;
use App\Http\Requests\IncomesDateRequest;
use Illuminate\Http\Request;

class UserCategoriesController extends Controller
{
    protected $userCategoriesService;

    public function __construct(UserCategoriesService $userCategoriesService){
        $this->userCategoriesService =  $userCategoriesService;
    }
    /**
     * Display a listing of the resource.
     */
    public function userCategories(CategoryDateRequest $request)
    {
        $categories = $this->userCategoriesService->userCategories(auth()->user(), $request->month, $request->year);
        return response()->json([
            'message' => 'كل الفئات الخاصة بك.',
            'categories' => $categories,
        ]);
    }

    public function expensesByCategory(ExpensesDateRequest $request)
    {
        $expensesByCategory = $this->userCategoriesService->expensesByCategory(auth()->user(), $request->month, $request->year);
        return response()->json([
            'message' => 'المصاريف المصنفة حسب الفئة لشهر ' . ($request->month ?? now()->month) . ' من سنة ' . ($request->year ?? now()->year) . '.',
            'expenses' => $expensesByCategory,
        ]);
    }

    public function incomesByCategory(IncomesDateRequest $request)
    {
        $incomesByCategory = $this->userCategoriesService->incomesByCategory(auth()->user(), $request->month, $request->year);
        return response()->json([
            'message' => 'الدخل المصنف حسب الفئة لشهر ' . ($request->month ?? now()->month) . ' من سنة ' . ($request->year ?? now()->year) . '.',
            'incomes_by_category' => $incomesByCategory
        ]);
    }

    public function remainingAmount(){
        $remainingAmount = $this->userCategoriesService->remainingAmount(auth()->user());
        return response()->json([
            'message' => 'المبلغ المتبقي بعد خصم النفقات من الدخل.',
            'remaining_amount' => $remainingAmount
        ]);
    }

}
