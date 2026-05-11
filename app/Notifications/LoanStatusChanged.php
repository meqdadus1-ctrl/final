<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanStatusChanged extends Notification
{
    public function __construct(public Loan $loan) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->loan->status === 'active' ? 'موافق عليها ✅' : 'مرفوضة ❌';
        $amount = number_format($this->loan->total_amount, 2);

        $msg = (new MailMessage)
            ->subject("طلب السلفة — {$status}")
            ->greeting("مرحباً {$notifiable->name}،")
            ->line("تم **{$status}** طلب السلفة بمبلغ **{$amount}**.");

        if ($this->loan->status === 'active') {
            $installment = number_format($this->loan->installment_amount, 2);
            $msg->line("سيتم خصم **{$installment}** شهرياً من راتبك.");
        }

        if ($this->loan->status === 'rejected' && $this->loan->rejection_reason) {
            $msg->line("**سبب الرفض:** {$this->loan->rejection_reason}");
        }

        return $msg->action('عرض السلف', url('/portal/loans'))
                   ->salutation('Fox Plus HRM');
    }
}
