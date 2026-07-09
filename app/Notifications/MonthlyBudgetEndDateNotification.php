<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyBudgetEndDateNotification extends Notification
{
    use Queueable;

    protected $MonthlyBudget;

    public function __construct($MonthlyBudget)
    {
        $this->MonthlyBudget = $MonthlyBudget;
    }

    public function via($notifiable)
    {
        return ['database']; // هيتسيف في جدول الـ notifications للفرونت
    }
    public function toArray(object $notifiable): array
    {
        $userName = $notifiable->first_name ?? 'يا بطل';
        $limitAmount = $this->MonthlyBudget->amount;
        $startDate = $this->MonthlyBudget->start_date;
        $endDate = $this->MonthlyBudget->end_date;
        return [
            'monthly_budget_id' => $this->MonthlyBudget->id,
            'limit_amount' => $limitAmount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'message' => "لقد إنتهى الموعد النهائي لميزانيتك الشهرية, {$userName}! نأمل أن تكون قد حققت تقدمًا نحو هدفك. لا تنسى مراجعة ميزانيتك وتحديثها أو حذفها إذا لزم الأمر.",
            'type' => 'monthly_budget_end_date_notification',
        ];
    }
}
