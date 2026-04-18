<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\VerifyOtpMail; // هنستخدم نفس كلاس الإيميل أو واحد جديد
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $user = User::where('email', $request->email)->first();

        // 1. توليد OTP جديد لعملية استعادة الباسورد
        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        // 2. إرسال الإيميل (ممكن تستخدم VerifyOtpMail أو تعمل واحد مخصوص لنسيان الباسورد)
        try {
            Mail::to($user->email)->send(new VerifyOtpMail($otp));

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال كود استعادة كلمة المرور إلى بريدك الإلكتروني.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال الإيميل.'
            ], 500);
        }
    }
}