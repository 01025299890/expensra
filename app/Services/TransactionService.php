<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Contracts\Support\ValidatedData;


class TransactionService 
{
    public function userTransactions($user){
        $transactions =$user->transactions()
            ->latest()
            ->get();

        return $transactions;
    }
    public function storeTransaction(array $validatedData, $userId){
        if(empty($validatedData)){
            return ['error' => 'No data to received'];
            // للتجربة
            // $validatedData = [
            //     'category' => 'food',
            //     'amount' => 0,
            //     'transaction_type' => 'expense',
            //      'transaction_date' => now()->format('Y-m-d'),
            //     'notes' => null,
            //     'confidence' => 0.6
            // ];
        }
        if(isset($validatedData['confidence']) && $validatedData['confidence'] <= 0.5){
            return ['error' => 'AI confidence too low'];
        }
        $validatedData['user_id'] = $userId;

        $validatedData['transaction_date'] = $validatedData['transaction_date'] ?? now()->format('Y-m-d');
        $categoryType = $validatedData['transaction_type'] ?? 'uncategorized';
        $category = Category::GetOrCreate(
             $validatedData['category'],
              $userId,
              $categoryType, 
        );

        $validatedData['category_id'] = $category->id;
        if(isset($validatedData['category'])){
            unset($validatedData['category']);
        }
        if(isset($validatedData['confidence'])){
            unset($validatedData['confidence']);
        }

        $transaction = Transaction::create($validatedData);

        return $transaction;
    }

    public function showTransaction($id, $user){
        $transaction = $user->transactions()
            ->find($id);
            if (!$transaction) {
                return null;
            }
            return $transaction;
    }

    public function updateTransaction($id,$validatedData,$user){
        $transaction = $user->transactions()->find($id);
        if (!$transaction) {
            return null;
        }
        $userId = $user->id;
        $categoryType = $validatedData['transaction_type'] ?? 'uncategorized';
        if (isset($validatedData['category'])) {
            $category = Category::GetOrCreate(
                $validatedData['category'],
                $userId,
                $categoryType,
            );
            $validatedData['category_id'] = $category->id;

            unset($validatedData['category']);
        }

        $transaction->update($validatedData);
        $transaction->refresh();
        return $transaction;

    }

    public function deleteTransaction($id,$user){
        $transaction =$user->transactions()->find($id);
        if (!$transaction) {
            return null;
        }
        $transaction->delete();
        return true;

    }






}