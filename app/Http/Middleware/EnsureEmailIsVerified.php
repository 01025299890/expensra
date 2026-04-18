<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // لو مفيش مستخدم مسجل دخول، أو المستخدم مسجل بس لسه مفعلش حسابه بالـ OTP
        if (
            !$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                !$request->user()->hasVerifiedEmail()) 
        ) {

            return response()->json([
                'message' => 'Please verify your account using the OTP code sent to you.',
                'verified' => false
            ], 403); // كود 403 أفضل للمنع
        }

        return $next($request);
    }
}
