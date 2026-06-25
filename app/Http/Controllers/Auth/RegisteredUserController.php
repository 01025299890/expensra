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
use App\Mail\VerifyOtpMail;

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

        $otp = rand(100000, 999999);
        $requestData['otp'] = $otp;
        $requestData['otp_expires_at'] = now()->addMinutes(15);

        $user = User::create($requestData);

        try {
            Mail::to($user->email)
                ->send(new \App\Mail\VerifyOtpMail($otp));
        } catch (\Exception $e) {
            $user->delete();

            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال كود التحقق. يرجى المحاولة مرة أخرى.',
                'error' => $e->getMessage()
            ], 500);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم التسجيل بنجاح. يرجى فحص بريدك الإلكتروني للحصول على كود التحقق.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }




    /**
     * إعادة إرسال كود التحقق للمستخدم الحالي.
     */
    public function resendOtp(Request $request)
    {
        $user = $request->user();

        // 1. التأكد أن الحساب لم يتم تفعيله مسبقاً
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'هذا الحساب مفعل بالفعل.'
            ], 400);
        }

        // 2. توليد كود جديد وتحديث وقت الانتهاء
        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        // 3. إرسال الإيميل
        try {
            Mail::to($user->email)
                ->send(new VerifyOtpMail($otp));

            return response()->json([
                'message' => 'تم إعادة إرسال كود التحقق بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل إرسال البريد الإلكتروني، حاول لاحقاً.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
