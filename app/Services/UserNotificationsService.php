<?php

namespace App\Services;

use App\Models\User;

class UserNotificationsService
{
    /**
     * جلب جميع الإشعارات غير المقروءة للمستخدم (الفائض وتذكير الأهداف)
     */
    public function getNotificationForUser(User $user)
    {
        return $user->unreadNotifications()
            // بنخليه يجيب النوعين مع بعض من الداتابيز في كويري واحدة
            ->whereIn('type', [
                'App\Notifications\MonthlyResetNotification',
                'App\Notifications\GoalReminderNotification'
            ])
            ->get()
            ->map(function ($notification) {

                // لو الإشعار من نوع تذكير الأهداف الأسبوعي
                if ($notification->type === 'App\Notifications\GoalReminderNotification') {
                    return [
                        'notification_id' => $notification->id,
                        'goal_id' => $notification->data['goal_id'] ?? null,
                        'goal_name' => $notification->data['goal_name'] ?? '',
                        'message' => $notification->data['message'] ?? '',
                        'type' => $notification->data['type'] ?? 'weekly_goal_reminder',
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];
                }

                // لو الإشعار من نوع الفائض الشهري (الكود الأصلي بتاعك)
                return [
                    'notification_id' => $notification->id,
                    'message' => $notification->data['message'] ?? '',
                    'remaining_balance' => $notification->data['remaining_balance'] ?? 0,
                    'type' => $notification->data['type'] ?? 'monthly_reset_decision',
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }
}