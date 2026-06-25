<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Category;

class CheckCategoryBudget
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->input('transaction_type') !== 'expense'){
            return $next($request);
        }

        // لو المستخدم باعت flag الموافقة، عدي الطلب فوراً من غير ما تشيك على الميزانية
        if ($request->boolean('force_save')) {
            return $next($request);
        }
        
        $categoryName = $request->input('category');
        $newAmount = $request->input('amount', 0);
        $user = auth()->user();

        $category = $user->categories()->where('name', $categoryName)->where('type', 'expense')->first();

        if (!$category) {
            return $next($request);
        }

        $userCategoryBudget = $category->budgets()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$userCategoryBudget) {
            return $next($request);
        }

        $categorySpent = $category->transactions()
            ->whereBetween('transaction_date', [$userCategoryBudget->start_date, $userCategoryBudget->end_date])
            ->sum('amount');

        $totalSpent = $categorySpent + $newAmount;

        if ($totalSpent > $userCategoryBudget->limit_amount) {
            return response()->json([
                'status' => 'budget_exceeded', 
                'message' => "لقد تجاوزت ميزانية {$categoryName}. هل تريد الاستمرار؟",
                'details' => [
                    'limit' => $userCategoryBudget->limit_amount,
                    'current_spent' => $categorySpent,
                    'remaining' => max(0, $userCategoryBudget->limit_amount - $categorySpent),
                    'transaction_amount' => $newAmount
                ],
                'requires_confirmation' => true 
            ], 409); 
        }
        if ( $totalSpent < $userCategoryBudget->limit_amount && $totalSpent / $userCategoryBudget->limit_amount >= 0.8 ) {
            return response()->json([
                'status' => 'budget_warning', 
                'message' => "لقد اقتربت من تجاوز ميزانية {$categoryName}. هل تريد الاستمرار؟",
                'details' => [
                    'limit' => $userCategoryBudget->limit_amount,
                    'current_spent' => $categorySpent,
                    'remaining' => max(0, $userCategoryBudget->limit_amount - $categorySpent),
                    'transaction_amount' => $newAmount
                ],
                'requires_confirmation' => true 
            ], 200); 
        }
        if( $totalSpent == $userCategoryBudget->limit_amount ) {
            return response()->json([
                'status' => 'budget_warning', 
                'message' => "لقد وصلت إلى حد ميزانية {$categoryName}. هل تريد الاستمرار؟",
                'details' => [
                    'limit' => $userCategoryBudget->limit_amount,
                    'current_spent' => $categorySpent,
                    'remaining' => max(0, $userCategoryBudget->limit_amount - $categorySpent),
                    'transaction_amount' => $newAmount
                ],
                'requires_confirmation' => true 
            ], 200);
        }

        return $next($request);
    }
}
