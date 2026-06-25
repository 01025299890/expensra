<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        // جلب المستخدم صاحب التوكن الحالي
        $user = $request->user();

        // التأكد من الكود
        if ($user->otp === $request->otp && now()->lt($user->otp_expires_at)) {

            $user->update([
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            return response()->json([
                'message' => 'تم تفعيل حسابك بنجاح، يمكنك الآن استخدام التطبيق بالكامل.',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'الكود غير صحيح أو انتهت صلاحيته'], 422);
    }

}
