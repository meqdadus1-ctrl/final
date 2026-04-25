<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLedger extends Model
{
    protected $table = 'employee_ledger';

    protected $fillable = [
        'employee_id', 'entry_date', 'period_start', 'period_end',
        'entry_type', 'credit', 'debit', 'balance_after',
        'description', 'reference_type', 'reference_id',
        'fiscal_period', 'created_by',
    ];

    protected $casts = [
        'entry_date'    => 'date',
        'period_start'  => 'date',
        'period_end'    => 'date',
        'credit'        => 'decimal:2',
        'debit'         => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /* ---------- Relations ---------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------- Scopes ---------- */

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeInPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('entry_date', [$from, $to]);
    }

    public function scopeCredits($query)
    {
        return $query->where('credit', '>', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('debit', '>', 0);
    }

    /* ---------- Accessors ---------- */

    public function getEntryTypeLabelAttribute(): string
    {
        return match($this->entry_type) {
            'salary'             => 'راتب',
            'overtime'           => 'أوفرتايم',
            'bonus'              => 'مكافأة',
            'deduction_late'     => 'خصم تأخير',
            'deduction_absence'  => 'خصم غياب',
            'deduction_manual'   => 'خصم يدوي',
            'loan_installment'   => 'قسط سلفة',
            'loan_disbursement'  => 'صرف سلفة',
            'withdrawal'         => 'سحب / مصروف',
            'payment'            => 'دفع للموظف',
            'adjustment'         => 'تسوية',
            'opening_balance'    => 'رصيد افتتاحي',
            default              => $this->entry_type,
        };
    }

    public function getEntryTypeColorAttribute(): string
    {
        return match($this->entry_type) {
            'salary', 'overtime', 'bonus', 'opening_balance' => 'success',
            'deduction_late', 'deduction_absence',
            'deduction_manual', 'loan_installment',
            'loan_disbursement', 'withdrawal', 'payment'     => 'danger',
            'adjustment'                                      => 'warning',
            default                                           => 'secondary',
        };
    }

    /** هل القيد دائن (لصالح الموظف) */
    public function getIsCreditAttribute(): bool
    {
        return $this->credit > 0;
    }

    /** المبلغ مع الإشارة */
    public function getSignedAmountAttribute(): string
    {
        if ($this->credit > 0) {
            return '+' . number_format($this->credit, 2);
        }
        return '-' . number_format($this->debit, 2);
    }
}
