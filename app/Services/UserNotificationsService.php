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
        // 1. جلب الكويري الأساسية للإشعارات غير المقروءة
        $notificationsQuery = $user->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\MonthlyResetNotification',
                'App\Notifications\GoalReminderNotification',
                'App\Notifications\GoalDeadlineNotification',
                'App\Notifications\CategoryBudgetEndDateNotification',
                'App\Notifications\MonthlyBudgetEndDateNotification',
            ]);

        // 2. جلب البيانات الفعليه في متغير
        $notifications = $notificationsQuery->get();

        // 3. تحديث حالتها في الداتابيز فوراً لتصبح مقروءة (read_at لن يصبح NULL بعد الآن)
        $notificationsQuery->update(['read_at' => now()]);
        // أو بديل لها: $user->unreadNotifications->markAsRead();

        // 4. عمل الـ Map والـ Formatting المعتاد بتاعك
        return $notifications->map(function ($notification) {
            // ... نفس كود الـ if والـ return بتاعك بدون أي تغيير ...
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
                if ($notification->type === 'App\Notifications\MonthlyResetNotification') {
                    
                
                return [
                    'notification_id' => $notification->id,
                    'message' => $notification->data['message'] ?? '',
                    'remaining_balance' => $notification->data['remaining_balance'] ?? 0,
                    'type' => $notification->data['type'] ?? 'monthly_reset_decision',
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
                }

                // لو الإشعار من نوع تذكير الموعد النهائي للأهداف
                if ($notification->type === 'App\Notifications\GoalDeadlineNotification') {
                    return [
                        'notification_id' => $notification->id,
                        'goal_id' => $notification->data['goal_id'] ?? null,
                        'goal_name' => $notification->data['goal_name'] ?? '',
                        'message' => $notification->data['message'] ?? '',
                        'type' => $notification->data['type'] ?? 'goal_deadline_reminder',
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];
                }
                if ($notification->type === 'App\Notifications\CategoryBudgetEndDateNotification') {
                    return [
                        'notification_id' => $notification->id,
                        'category_id' => $notification->data['category_id'] ?? null,
                        'category_name' => $notification->data['category_name'] ?? '',
                        'message' => $notification->data['message'] ?? '',
                        'type' => $notification->data['type'] ?? 'category_budget_end_date_reminder',
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];  
                }
                if ($notification->type === 'App\Notifications\MonthlyBudgetEndDateNotification') {
                    return [
                        'notification_id' => $notification->id,
                        'budget_id' => $notification->data['budget_id'] ?? null,
                        'budget_name' => $notification->data['budget_name'] ?? '',
                        'message' => $notification->data['message'] ?? '',
                        'type' => $notification->data['type'] ?? 'monthly_budget_end_date_reminder',
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];  
                }
            });
    }
}