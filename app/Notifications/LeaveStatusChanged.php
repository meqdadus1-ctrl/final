<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveStatusChanged extends Notification
{
    public function __construct(public LeaveRequest $leave) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status  = $this->leave->status === 'approved' ? 'موافق عليها ✅' : 'مرفوضة ❌';
        $color   = $this->leave->status === 'approved' ? '#f47c20' : '#d94f4f';
        $from    = $this->leave->start_date->format('Y/m/d');
        $to      = $this->leave->end_date->format('Y/m/d');
        $days    = $this->leave->total_days;
        $reason  = $this->leave->rejection_reason ?? '—';

        $msg = (new MailMessage)
            ->subject("طلب إجازتك — {$status}")
            ->greeting("مرحباً {$notifiable->name}،")
            ->line("تم **{$status}** طلب إجازتك للفترة من {$from} إلى {$to} ({$days} أيام).");

        if ($this->leave->status === 'rejected' && $this->leave->rejection_reason) {
            $msg->line("**سبب الرفض:** {$reason}");
        }

        return $msg->action('عرض الطلب', url('/portal/leaves'))
                   ->salutation('Fox Plus HRM');
    }
}
