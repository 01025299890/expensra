<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonthlyBudgetRequest;
use App\Http\Requests\UpdateMonthlyBudgetRequest;
use App\Services\MonthlyBudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyBudgetController extends Controller
{
    protected $monthlyBudgetService;

    public function __construct(MonthlyBudgetService $monthlyBudgetService)
    {
        $this->monthlyBudgetService = $monthlyBudgetService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $budgets = $this->monthlyBudgetService->index($user);

        if ($budgets->isEmpty()) {
            return response()->json([
                'Message' => 'ليس لديك أي ميزانيات شهرية مخصصة حتى الآن.',
                'Budgets' => []
            ]);
        }

        return response()->json([
            'Message' => 'كل الميزانيات الشهرية الخاصة بك',
            'Budgets' => $budgets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMonthlyBudgetRequest $request)
    {
        $userId = Auth::id();
        $validatedData = $request->validated();

        $budgetStored = $this->monthlyBudgetService->store($validatedData, $userId);

        // حالة: وجود تداخل في التواريخ
        if ($budgetStored === 'overlapping') {
            return response()->json([
                'message' => 'لديك ميزانية مسجلة بالفعل تتداخل مع التواريخ المحددة، يرجى اختيار فترة أخرى.'
            ], 400);
        }

        // حالة: خطأ غير متوقع
        if (!$budgetStored) {
            return response()->json(['message' => 'غير قادر على انشاء الميزانية الشهرية حالياً'], 500);
        }

        // حالة: تم الإنشاء بنجاح
        return response()->json([
            'message' => 'تم انشاء الميزانية الشهرية بنجاح',
            'data' => $budgetStored
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $budget = $this->monthlyBudgetService->show($id, $user);

        if (!$budget) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة.'], 404);
        }


        return response()->json($budget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMonthlyBudgetRequest $request, string $id)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
        $budgetUpdated = $this->monthlyBudgetService->update($id, $validatedData, $user);

        if ($budgetUpdated === 'overlapping') {
            return response()->json([
                'message' => 'التعديل يتسبب في تداخل التواريخ مع ميزانية أخرى مسجلة مسبقاً.'
            ], 400);
        }

        if (!$budgetUpdated) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة أو غير مصرح لك بتحديثها.'], 404);
        }

        return response()->json([
            'Message' => 'تم تحديث الميزانية الشهرية بنجاح.',
            'Budget' => $budgetUpdated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $deleted = $this->monthlyBudgetService->destroy($id, $user);

        if (!$deleted) {
            return response()->json(['Message' => 'هذه الميزانية غير موجودة أو غير مصرح لك بحذفها.'], 404);
        }

        return response()->json(['Message' => 'تم حذف الميزانية الشهرية بنجاح.']);
    }
}