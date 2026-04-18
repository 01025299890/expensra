<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * تحديث كلمة المرور باستخدام الـ OTP
     */
    public function store(Request $request): JsonResponse
    {
        // 1. التحقق من البيانات المرسلة
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string'], // نستخدم OTP بدلاً من token
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. البحث عن المستخدم والتأكد من صحة الكود ووقت انتهاء الصلاحية
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp !== $request->otp || now()->gt($user->otp_expires_at)) {
            return response()->json([
                'message' => 'كود التحقق غير صحيح أو انتهت صلاحيته.'
            ], 422);
        }

        // 3. تحديث كلمة المرور وتصفير الـ OTP لكي لا يستخدم مرة أخرى
        $user->forceFill([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تغيير كلمة المرور بنجاح، يمكنك تسجيل الدخول الآن.'
        ]);
    }
}