<?php

namespace App\Notifications;

use App\Models\SalaryPayment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SalaryPaid extends Notification
{
    public function __construct(public SalaryPayment $payment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $net    = number_format($this->payment->net_salary, 2);
        $period = $this->payment->fiscal_period;
        $from   = $this->payment->week_start;
        $to     = $this->payment->week_end;

        return (new MailMessage)
            ->subject("تم صرف راتبك — {$period} 💰")
            ->greeting("مرحباً {$notifiable->name}،")
            ->line("تم صرف راتبك للفترة من **{$from}** إلى **{$to}**.")
            ->line("**الراتب الصافي: {$net}**")
            ->action('عرض كشف الراتب', url('/portal/salary'))
            ->salutation('Fox Plus HRM');
    }
}
