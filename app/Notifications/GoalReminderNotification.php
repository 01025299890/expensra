<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GoalReminderNotification extends Notification
{
    use Queueable;

    protected $goal;

    public function __construct($goal)
    {
        $this->goal = $goal;
    }

    public function via($notifiable)
    {
        return ['database']; // هيتسيف في جدول الـ notifications للفرونت
    }

    public function toArray($notifiable)
    {
        // ⚡ السحر هنا: بنقرأ الاسم مباشرة من الـ $notifiable لأنه بيمثل الـ User
        $userName = $notifiable->name ?? 'يا بطل';
        $goalName = $this->goal->goal_name;
        $targetAmount = $this->goal->target_amount;
        $savedAmount = $this->goal->saved_amount;

        return [
            'goal_id' => $this->goal->id,
            'goal_name' => $goalName,
            'message' => "مرحبًا {$userName}! تذكير: هدفك '{$goalName}' لم يتم تحقيقه بعد. المبلغ المستهدف هو {$targetAmount} والمبلغ المحفوظ حاليًا هو {$savedAmount}. استمر في العمل على هدفك!",
            'type' => 'weekly_goal_reminder',
            'saved_amount' => $savedAmount,
        ];
    }
}