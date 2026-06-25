<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;
use App\Http\Requests\TransactionDateRequest;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $transactions = $this->transactionService->userTransactions($user);
        return response()->json([
            'message' => 'كل المعاملات الخاصة بك لشهر ' . now()->format('F Y'),
            'transactions' => $transactions->load('category')
        
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $validatedData = $request->validated();
        $userId = auth()->id();

        $transaction = $this->transactionService->storeTransaction($validatedData, $userId);

        // 1. التظبيط السحري: التشيك لو الراجع مصفوفة وفيها مفتاح error (سواء ليميت ميزانية أو رصيد محفظة)
        if (is_array($transaction) && isset($transaction['error'])) {
            return response()->json([
                'message' => $transaction['error']
            ], 400);
        }

        if (!$transaction) {
            return response()->json(['message' => 'غير قادر على إضافة المعاملة حالياً'], 500);
        }

        // 2. هنا نضمن إن الـ $transaction هو Model Object وسطر الـ load هيشتغل سليم مية في المية
        return response()->json([
            'message' => 'تم إضافة المعاملة بنجاح',
            'transaction' => $transaction->load('category')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $transaction = $this->transactionService->showTransaction($id, $user);
        if (!$transaction) {
            return response()->json(['message' => 'المعاملة غير موجودة'], 404);
        }
        return response()->json($transaction->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $user = auth()->user();

        $transaction = $this->transactionService->updateTransaction($id, $validatedData, $user);
        if (!$transaction) {
            return response()->json(['message' => 'المعاملة غير موجودة أو غير مصرح بها'], 404);
        }

        return response()->json([
            'message' => 'تم تحديث المعاملة بنجاح',
            'transaction' => $transaction->load('category')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $deleted = $this->transactionService->deleteTransaction($id, $user);
        if (!$deleted) {
            return response()->json(['message' => 'المعاملة غير موجودة'], 404);
        }
        return response()->json(['message' => 'تم حذف المعاملة بنجاح']);
    }

    /**
     * Display monthly transactions overview.
     */
    public function monthlyTransactions(TransactionDateRequest $request)
    {
        $user = auth()->user();
        $response = $this->transactionService->monthlyTransactions($user, $request->month, $request->year);

        if ($response['transactions']->isEmpty()) {
            return response()->json(['message' => 'لا يوجد معاملات لهذا الشهر'], 404);
        }

        return response()->json([
            'message' => 'كل المعاملات الخاصة بك لشهر ' . ($request->month ?? now()->month) . ' من سنة ' . ($request->year ?? now()->year),
            'MonthIncome' => $response['MonthIncome'],
            'MonthExpense' => $response['MonthExpense'],
            'transactions' => $response['transactions']->load('category')
        ]);
    }
}