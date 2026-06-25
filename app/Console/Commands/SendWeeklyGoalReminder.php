<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\GoalReminderNotification; // تأكد إنك كريت الملف ده

class SendWeeklyGoalReminder extends Command
{
    protected $signature = 'app:send-weekly-goal-reminder';
    protected $description = 'إرسال تذكير أسبوعي لليوزر بهدف عشوائي من أهدافه المعلقة';

    public function handle()
    {
        // جلب المستخدمين الذين لديهم أهداف معلقة ولم تنتهِ صلاحيتها بعد
        $users = User::whereHas('goals', function ($query) {
            $query->whereColumn('saved_amount', '<', 'target_amount')
                ->where('deadline', '>=', now());
        })->with('goals')->get();

        foreach ($users as $user) {
            // جلب هدف عشوائي واحد مستوفٍ للشروط
            $randomGoal = $user->goals()
                ->whereColumn('saved_amount', '<', 'target_amount')
                ->where('deadline', '>=', now())
                ->inRandomOrder()
                ->first();

            if ($randomGoal) {
                // تفعيل الإرسال الفعلي
                $user->notify(new GoalReminderNotification($randomGoal));
            }
        }

        $this->info('تم إرسال تذكيرات الأهداف الأسبوعية بنجاح!');
    }
}