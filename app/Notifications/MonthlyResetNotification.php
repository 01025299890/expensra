<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MonthlyResetNotification extends Notification
{
    use Queueable; 

    private $balance;

    public function __construct($balance)
    {
        $this->balance = $balance;
    }

    public function via($notifiable)
    {
        return ['database']; // حفظ في الداتابيز ليظهر داخل التطبيق
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "انتهى الشهر ولديك فائض بمبلغ " . number_format($this->balance, 2, '.', '') . "، ماذا تحب أن تفعل به؟",
            'remaining_balance' => number_format($this->balance, 2, '.', ''),
            'type' => 'monthly_reset_decision'
        ];
    }
}