<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

class TransactionController extends Controller
{
   protected $transactionService;
    public function __construct(TransactionService $transactionService){
        $this->transactionService = $transactionService;
    }
    
    



    public function index()
    {
        $user = auth()->user();
        $transactions = $this->transactionService->userTransactions($user);
        return response()->json($transactions->load('category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $validatedData = $request->validated();
        $userId = auth()->id();
        $transaction = $this->transactionService->storeTransaction($validatedData,$userId);
        

        return response()->json([
            'message' => 'Transaction created successfully',
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
            return response()->json(['message' => 'Transaction not found'], 404);
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
        $transaction = $this->transactionService->updateTransaction($id,$validatedData,$user);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found or unauthorized'], 404);
        }

        // $transaction->refresh();

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction->load('category')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $deleted = $this->transactionService->deleteTransaction($id,$user);
        if (!$deleted) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
