<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'employee_id', 'total_amount', 'installment_amount', 'installment_type',
        'amount_paid', 'installments_total', 'installments_paid',
        'start_date', 'last_payment_date', 'is_paused', 'status', 'description',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'last_payment_date' => 'date',
        'total_amount'      => 'decimal:2',
        'installment_amount'=> 'decimal:2',
        'amount_paid'       => 'decimal:2',
        'is_paused'         => 'boolean',
    ];

    protected $attributes = [
        'installment_type' => 'weekly',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function getRemainingInstallmentsAttribute()
    {
        return $this->installments_total - $this->installments_paid;
    }

    public function getInstallmentTypeLabelAttribute(): string
    {
        return match($this->installment_type) {
            'weekly'  => 'أسبوعي',
            'monthly' => 'شهري',
            default   => $this->installment_type ?? 'أسبوعي',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'قيد المراجعة',
            'active'    => 'نشطة',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            'rejected'  => 'مرفوضة',
            default     => $this->status ?? '—',
        };
    }
}
