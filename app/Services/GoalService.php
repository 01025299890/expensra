<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Category;
class GoalService{
public function index($user)
{
    $goals = $user->goals()->get();

    if ($goals->isNotEmpty()) {
        return [
            // 'status' => 'success',
            'message' => 'أهدافك',
            'goals'   => $goals
        ];
    }

    return [
        'error' => true,
        'message' => 'ليس لديك أهداف حالياً',
        'goals'   => [] 
    ];
}

    public function store($user , $validatedRequest){
        $remainingAmount = new UserCategoriesService;
        $remaningAmount = $remainingAmount->remainingAmount($user);
        if ($remaningAmount - $validatedRequest['saved_amount'] < 0 || $validatedRequest['saved_amount'] < 0 || $remaningAmount < 0) {
            return [
                'error' => true,
                'message' => 'المبلغ الذي تحاول إيداعه يتجاوز المبلغ المتبقي في حسابك أو غير صالح',
            ];
        }
        $userGoalsExist = $user->goals()->where('goal_name',$validatedRequest['goal_name'])->exists();
        if($userGoalsExist){
            return [
                'error' => true,
                'message' => 'هذا الهدف موجود بالفعل',
            ];
        }
        if($validatedRequest['saved_amount'] === null){
            $validatedRequest['saved_amount'] = 0;
        }
        $goal =$user->goals()->create($validatedRequest);

        $user->transactions()->create([
            'amount' => $validatedRequest['saved_amount'],
            'transaction_type' => 'expense',
            'transaction_date' => now()->format('Y-m-d:H:i:s'),
            'notes' => 'إيداع في الهدف: ' . $goal->goal_name,
            'category' => 'Goal Deposit',
            'category_id' => Category::GetOrCreate(
                'Goal Deposit',
                $user->id,
                'expense',
            )->id,
        ]);
        return [
            'message' => 'نم إنشاء الهدف ',
            'goals' => $goal
        ];
    }

    public function show($user , $goalId){
        $goal = $user->goals()->find($goalId);
        if ($goal) {
            return [
                // 'status' => 'success',
                'goal' => $goal
            ];
        }

        return [
            'status' => 'error',
            'goal' => []
        ];
    }

    public function update($user, $validatedRequest, $id)
    {
        $goal = $user->goals()->find($id);

        if (!$goal) {
            return [
                'error' => true,
                'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية تعديله',
            ];
        }

      
        if (!isset($validatedRequest['saved_amount'])) {
            $validatedRequest['saved_amount'] = 0;
        }

        $goal->update($validatedRequest);

        return [
            // 'status' => 'success',
            'message' => 'تم تعديل الهدف بنجاح',
            'goal' => $goal
        ];
    }

    public function destroy($user, $id)
    {
        $goal = $user->goals()->find($id);

        if (!$goal) {
            return [
                'error' => true,
                'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية حذفه',
            ];
        }

        $goal->delete();
        if($goal->saved_amount > 0){
            $transactions = new TransactionService;
            $transactions->storeTransaction([
                'amount' => $goal->saved_amount,
                'transaction_type' => 'income',
                'transaction_date' => now()->format('Y-m-d:H:i:s'),
                'notes' => 'إرجاع المبلغ المحفوظ بعد حذف الهدف: ' . $goal->goal_name,
                'category' => 'Goal remove',
            ], $user->id);
        }
        return [
            // 'status' => 'success',
            'message' => 'تم حذف الهدف بنجاح',
        ];
    }


    public function depositToGoal($user , $validatedRequest, $id){
        $goal = $user->goals()->find($id);
        $remainingAmount = new UserCategoriesService;
        $remaningAmount = $remainingAmount->remainingAmount($user);
            if($remaningAmount - $validatedRequest['amount'] < 0 || $validatedRequest['amount'] <= 0 || $remaningAmount <= 0 ){
                return [
                    'error' => true,
                    'message' => 'المبلغ الذي تحاول إيداعه يتجاوز المبلغ المتبقي في حسابك أو غير صالح',
                ];
            }
        if (!$goal) {
            return [
                'error' => true,
                'message' => 'هذا الهدف غير موجود أو لا تملك صلاحية تعديله',
            ];
        }
        $newSavedAmount = $goal->saved_amount + $validatedRequest['amount'];
        if ($newSavedAmount > $goal->target_amount) {
            return [
                'error' => true,
                'message' => 'المبلغ المودع يتجاوز المبلغ المستهدف للهدف',
            ];
        }
        $category = Category::GetOrCreate(
            'Goal Deposit',
            $user->id,
            'expense',
        );
        $user->transactions()->create([
            'amount' => $validatedRequest['amount'],
            'transaction_type' => 'expense',
            'transaction_date' => now()->format('Y-m-d:H:i:s'),
            'notes' => 'إيداع في الهدف: ' . $goal->goal_name,
            'category' => 'Goal Deposit',
            'category_id' => $category->id,
        ]);


        $goal->update(['saved_amount' => $newSavedAmount]);
        $goal->refresh();
        return [
            // 'status' => 'success',
            'message' => 'تم إيداع المبلغ بنجاح',
            'goal' => $goal
        ];
        
    }
}