<?php

namespace App\Http\Controllers;

use App\Services\UserCategoriesService;
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
    public function userCategories()
    {
        $categories = $this->userCategoriesService->userCategories(auth()->user());
        return response()->json($categories);
    }

    public function expensesByCategory()
    {
        $expensesByCategory = $this->userCategoriesService->expensesByCategory(auth()->user());
        return response()->json($expensesByCategory);
    }

    public function incomesByCategory()
    {
        $incomesByCategory = $this->userCategoriesService->incomesByCategory(auth()->user());
        return response()->json($incomesByCategory);
    }

}
