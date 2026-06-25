<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UpgradeService;
use Illuminate\Http\Request;

class UpgradeController extends Controller
{
    protected $upgradeService;

    public function __construct(UpgradeService $upgradeService)
    {
        $this->upgradeService = $upgradeService;
    }

    /**
     * أكشن الترقية لـ Premium
     */
    public function upgrade()
    {
        $user = auth()->user();

        // تأمين: لو اليوزر بريميوم أصلاً مش هنعمل حاجة
        if ($user->system_role === 'premium_user') { // أو $user->is_premium
            return response()->json([
                'success' => false,
                'message' => 'حسابك مفعل بالفعل كـ Premium.'
            ], 400);
        }
        if ($user->system_role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'حسابات المشرفين لا يمكن ترقيتها إلى Premium.'
            ], 400);
        }
      $user =  $this->upgradeService->upgradeToPremium($user);

        return response()->json([
            'success' => true,
            'message' => 'مبروك! تم ترقية حسابك إلى باقة Premium بنجاح الاستمتاع بكافة الميزات.',
            'user' => $user
        ], 200);
    }
}