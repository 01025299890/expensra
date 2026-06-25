<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Transaction;
use App\Services\BudgetService;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBudgetRequest;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected $budgetService;
    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $budgets = $this->budgetService->index($user);

        if ($budgets->isEmpty()) {
            return response()->json([
                'Message' => 'ليس لديك أي ميزانيات حتى الآن، قم بإنشاء ميزانية جديدة لتتبع نفقاتك بشكل أفضل.',
                'Budgets' => []
            ]);
        }
        return response()->json(
            [
                'Message' => 'كل الميزانيات الخاصة بك',
                'Budgets' => $budgets
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $userId = Auth::id();
        $user = Auth::user();
        $validatedData = $request->validated();
        $allIncome = $user->transactions()->Incomes()->sum('amount');
        // $remainingBudget = $allIncome - $user->budgets()->sum('limit_amount');
        $budgetStored = $this->budgetService->store($validatedData, $userId);

        // حالة: تجاوز الدخل
        // if ($budgetStored === 'insufficient_income') {
        //     return response()->json([
        //         'message' => 'لا يمكنك إنشاء ميزانية جديدة لأن الدخل الحالي لا يغطي الحدود المطلوبة.',
        //         'total_income' => $allIncome,
        //         'Your_total_budget' => $user->budgets()->sum('limit_amount'),
        //         'remaining_budget' => $remainingBudget,
        //         'required_budget' => $validatedData['limit_amount'],
        //         'suggestion' => 'يرجى تقليل حدود الميزانية أو زيادة دخلك لتتمكن من إنشاء هذه الميزانية.'
        //     ], 400);
        // }

        // حالة: خطأ غير متوقع في قاعدة البيانات
        if (!$budgetStored) {
            return response()->json(['message' => 'غير قادر على انشاء الميزانية حالياً'], 500);
        }

        // حالة: تم الإنشاء بنجاح
        if ($budgetStored->wasRecentlyCreated) {
            return response()->json([
                'message' => 'تم انشاء الميزانية بنجاح',
                'data' => $budgetStored->load('category')
            ], 201);
        }

        // حالة: الميزانية موجودة مسبقاً
        return response()->json([
            'message' => 'يوجد ميزانية مسبقاً بنفس الفئة، يرجى تعديل الميزانية الحالية بدلاً من إنشاء واحدة جديدة.',
            'data' => $budgetStored->load('category')
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $budget = $this->budgetService->show($id, $user);
        if (!$budget) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة.'], 404);
        }
        return response()->json($budget->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, string $id)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
        $budgetUpdated = $this->budgetService->update($id, $validatedData, $user);

        if (!$budgetUpdated) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة أو غير مصرح لك بتحديثها.'], 404);
        }

        return response()->json([
            'Message' => 'تم تحديث الميزانية بنجاح.',
            'Budget' => $budgetUpdated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $deleted = $this->budgetService->destroy($id, $user);
        if (!$deleted) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة أو غير مصرح لك بحذفها.'], 404);
        }
        return response()->json(['Message' => 'تم حذف الميزانية بنجاح.']);
    }
}
