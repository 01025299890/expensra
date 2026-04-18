<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Budget;


class BudgetService
{
    public function index($user){
      $userBudgets =  $user->budgets()->with('category')->get();
      return $userBudgets;
    }

    public function store($validatedData , $userId){
        $user = auth()->user();
        $budget = $user->budgets()->where('start_date', $validatedData['start_date'])
            ->where('end_date', $validatedData['end_date'])
            ->where('category_id', $validatedData['category_id'])
            ->first();
        if ($budget) {
             return $budget;
        }
            
        try{
        $validatedData['start_date'] = $validatedData['start_date']?? now()->toDateString();
        $validatedData['end_date'] = $validatedData['end_date']?? now()->addMonth()->toDateString();
        $validatedData['user_id'] = $userId;
        $createBudget =  Budget::create($validatedData);
        return $createBudget;

        } catch (Exception $e) {
            Log::error("Budget Creation Failed: " . $e->getMessage());
            throw new Exception("Unable to create budget at this time.");
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

    public function show($id,$user){
        $budget = $user->budgets();
        $budget = $budget->with('category')->find($id);
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