<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class UpdatePasswordController extends Controller
{
public function update(Request $request): JsonResponse
{
// 1. التحقق من البيانات
$request->validate([
'current_password' => ['required', 'current_password'], // 'current_password' قاعدة جاهزة في لارفيل للتأكد من صحة
// الباسورد القديم
'new_password' => ['required', 'confirmed', Password::defaults()],
]);

// 2. تحديث كلمة المرور للمستخدم المسجل دخوله حالياً
$request->user()->update([
'password' => Hash::make($request->new_password),
]);

return response()->json([
'status' => 'success',
'message' => 'تم تحديث كلمة المرور بنجاح.'
]);
}
}