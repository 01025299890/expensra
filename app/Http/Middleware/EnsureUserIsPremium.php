<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPremium
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->system_role === 'premium_user') {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'هذه الميزة متاحة فقط لمشتركي باقة Premium.'
        ], 403);
    }
}
