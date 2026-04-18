<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request)
    {
        $requestData = $request->validated();

        if ($request->hasFile('profile_image')) {
            $requestData['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $requestData['password'] = Hash::make($requestData['password']);

        // 1. توليد الـ OTP العشوائي
        $otp = rand(100000, 999999);
        $requestData['otp'] = $otp;
        $requestData['otp_expires_at'] = now()->addMinutes(15);

        // 2. إنشاء المستخدم في قاعدة البيانات
        $user = User::create($requestData);

        // 3. إرسال الإيميل فوراً (هذا هو الجزء الذي طلبته)
        try {
            Mail::to($user->email)
                ->send(new \App\Mail\VerifyOtpMail($otp));
        } catch (\Exception $e) {
            // لو حصل خطأ في إرسال الإيميل، نحذف المستخدم اللي اتسجل للتو
            $user->delete();

            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال كود التحقق. يرجى المحاولة مرة أخرى.',
                'error' => $e->getMessage()
            ], 500);
        }

        event(new Registered($user));

        // 4. إصدار التوكن (Sanctum) لكي يتمكن من استخدامه في دالة verify-otp
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم التسجيل بنجاح. يرجى فحص بريدك الإلكتروني للحصول على كود التحقق.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
