<?php
namespace App\Services;

use App\Models\Category;
use App\Models\User;

class UserCategoriesService
{
    public function userCategories( $user)
    {
        $userCategories = $user->categories()->withCount('transactions')->with('transactions')->orderBy('transactions_count', 'desc')->get();
        return $userCategories;
        // $userCategories = $user->categories()->with('transactions')->orderBy('created_at', 'desc')->get();
        // return $userCategories;
    }

    /**
     * Summary of expensesByCategory
     * @param mixed $user
     * @return array{total_expenses: mixed}
     * expenses()   function in transaction model
     * categoryExpenses() function in category model
     */
    public function expensesByCategory($user)
    {
        $totalExpenses = $user->transactions()->expenses()->sum('amount');
        $totalExpenses =['total_expenses' => $totalExpenses];
        $categories = $user->categories()->categoryExpenses()
            ->with(['transactions' => function ($query) {
                $query->expenses();
            }])
            ->get();

        $expensesByCategory = $categories->map(function ($category) {
            return
            [
                'category_name' => $category->name,
                'category_icon' => $category->icon,
                'expenses' => $category->transactions->sum('amount'),
            ];
        });
        $expensesByCategory = ['expenses_by_category' => $expensesByCategory];
        $expenses = array_merge($totalExpenses, $expensesByCategory);
        return $expenses;
    }
    /**
     * Summary of incomesByCategory
     * @param mixed $user
     * @return array{total_incomes: mixed}
     * incomes()   function in transaction model
     * categoryIncomes() function in category model
     */
    public function incomesByCategory($user)
    {
            $totalIncomes = $user->transactions()->incomes()->sum('amount');
            $totalIncomes = ['total_incomes' => $totalIncomes];
        $categories = $user->categories()->categoryIncomes()
            ->with(['transactions' => function ($query) {
                $query->incomes();
            }])
            ->get();

        $incomesByCategory = $categories->map(function ($category) {
            return [
                'category_name' => $category->name,
                'category_icon' => $category->icon,
                'total_incomes' => $category->transactions->sum('amount'),
            ];
        });
        $incomesByCategory = ['incomes_by_category' => $incomesByCategory];
        $income = array_merge($totalIncomes, $incomesByCategory);
        return $income;
    }


}