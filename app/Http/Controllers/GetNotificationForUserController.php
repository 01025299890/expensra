<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserNotificationsService;
use Illuminate\Http\Request;

class GetNotificationForUserController extends Controller
{
    protected $notificationService;

    public function __construct(UserNotificationsService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getMyNotifications(Request $request)
    {
        // استدعاء السيرفس المظبوطة
        $notifications = $this->notificationService->getNotificationForUser($request->user());

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ], 200);
    }
}