<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBudgetRequest;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected $budgetService;
    public function __construct(BudgetService $budgetService){
        $this->budgetService = $budgetService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $budgets = $this->budgetService->index($user);

        if($budgets->isEmpty()){
            return response()->json([
                'Message' => 'ليس لديك أي ميزانيات حتى الآن، قم بإنشاء ميزانية جديدة لتتبع نفقاتك بشكل أفضل.',
                'Budgets' => []
            ]);
        }
        return response()->json(
            [
                'Message' => 'كل الميزانيات الخاصة بك',
                'Budgets' => $budgets
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $userId = Auth::id();
        $validatedData = $request->validated();
        $budgetStored = $this->budgetService->store($validatedData, $userId);
        if(!$budgetStored){
            return response()->json(['Message' => 'غير قادر على انشاء الميزانية'], 500);
        }
        if ($budgetStored->wasRecentlyCreated) {
            return response()->json([
                'message' => 'تم انشاء الميزانية بنجاح',
                'data' => $budgetStored->load('category')
            ], 201);
        } else {
            return response()->json([
                'message' => 'الميزانية موجودة بالفعل لهذه الفئة والفترة الزمنية. تم إرجاع الميزانية الموجودة يمكنك تعديلها عبر زر تعديل',
                'data' => $budgetStored->load('category')
            ], 200); // رجعنا 200 لأنها موجودة فعلاً مش لسه مكرية
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $budget = $this->budgetService->show($id, $user);
            if(!$budget){
                return response()->json(['Message' => 'Budget not found or not authorized to view.'], 404);
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

        if(!$budgetUpdated){
            return response()->json(['Message' => 'Budget not found or not authorized to update.'], 404);
        }

        return response()->json([
            'Message' => 'Budget updated successfully.',
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
        if(!$deleted){
            return response()->json(['Message' => 'Budget not found or not authorized to delete.'], 404);
        }
        return response()->json(['Message' => 'Budget deleted successfully.']);
    }
}
