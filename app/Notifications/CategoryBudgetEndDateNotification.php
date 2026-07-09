<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CategoryBudgetEndDateNotification extends Notification
{
    use Queueable;

    protected $categoryBudget;

    public function __construct($categoryBudget)
    {
        $this->categoryBudget = $categoryBudget;
    }

    public function via($notifiable)
    {
        return ['database']; // هيتسيف في جدول الـ notifications للفرونت
    }
    public function toArray(object $notifiable): array
    {
        $userName = $notifiable->first_name ?? 'يا بطل';
        $categoryName = $this->categoryBudget->category->name;
        $limitAmount = $this->categoryBudget->limit_amount;
        $startDate = $this->categoryBudget->start_date;
        $endDate = $this->categoryBudget->end_date;
        return [
            'category_budget_id' => $this->categoryBudget->id,
            'category_name' => $categoryName,
            'limit_amount' => $limitAmount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'message' => "لقد إنتهى الموعد النهائي لميزانيتك للفئة {$categoryName}, {$userName}! نأمل أن تكون قد حققت تقدمًا نحو هدفك. لا تنسى مراجعة ميزانيتك وتحديثها أو حذفها إذا لزم الأمر.",
            'type' => 'category_budget_end_date_notification',
        ];
    }
}
