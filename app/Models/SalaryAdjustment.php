<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdjustment extends Model
{
    protected $fillable = [
        'employee_id', 'salary_payment_id', 'type', 'sign',
        'amount', 'adjustment_date', 'reason', 'notes',
        'status', 'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'amount'          => 'decimal:2',
        'sign'            => 'integer',
    ];

    /* ---------- Relations ---------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------- Scopes ---------- */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    public function scopeForEmployee($query, int $id)
    {
        return $query->where('employee_id', $id);
    }

    /* ---------- Helpers ---------- */

    /** هل هذا التعديل إضافة أم خصم؟ */
    public function getIsAdditionAttribute(): bool
    {
        if ($this->type === 'other') {
            return ((int) ($this->sign ?? -1)) === 1;
        }
        return in_array($this->type, ['bonus', 'expense']);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'bonus'     => 'مكافأة / بونص',
            'expense'   => 'مصروف مستحق للموظف',
            'deduction' => 'خصم تأنيبي',
            'other'     => 'متنوع (' . ($this->is_addition ? 'إضافة' : 'خصم') . ')',
            default     => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'bonus', 'expense' => 'success',
            'deduction' => 'danger',
            'other'     => $this->is_addition ? 'success' : 'danger',
            default     => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'معلّق',
            'applied'   => 'مُطبَّق',
            'cancelled' => 'ملغى',
            default     => $this->status,
        };
    }
}
