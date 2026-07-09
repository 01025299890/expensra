<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GoalDeadlineNotification extends Notification
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
    public function toArray(object $notifiable): array
    {
        $userName = $notifiable->first_name ?? 'يا بطل';
        $goalName = $this->goal->goal_name;
        $targetAmount = $this->goal->target_amount;
        $savedAmount = $this->goal->saved_amount;
        $deadline = $this->goal->deadline;
        return [
            'goal_id' => $this->goal->id,
            'goal_name' => $goalName,
            'message' => "لقد إنتهى الموعد النهائي لهدفك، {$userName}! نأمل أن تكون قد حققت تقدمًا نحو هدفك. لا تنسى مراجعة أهدافك وتحديثها إذا لزم الأمر.",
            'type' => 'goal_deadline_notification',
            'target_amount' => $targetAmount,
            'saved_amount' => $savedAmount,
            'deadline' => $deadline,
        ];
    }
}
