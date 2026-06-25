<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use Illuminate\Support\Facades\Storage;

class UpdateProfileController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user(); // جلب اليوزر الحالي المسجل
        $data = $request->validated();

        // تشيك لو فيه صورة جديدة مبعوتة
        if ($request->hasFile('profile_image')) {

            // 1. مسح الصورة القديمة من السيرفر لو موجودة عشان ما تملاش مساحة على الفاضي
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // 2. رفع الصورة الجديدة في مجلد profiles جوه الـ storage/app/public
            $path = $request->file('profile_image')->store('profiles', 'public');

            // 3. حفظ المسار الجديد في الـ داتا
            $data['profile_image'] = $path;
        }

        // تحديث بيانات اليوزر في الداتابيز
        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user
        ], 200);
    }
}