<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = [
        'employee_id', 'week_start', 'week_end',
        'hours_worked', 'overtime_hours', 'overtime_rate', 'hourly_rate', 'salary_multiplier',
        'salary_from_hours', 'salary_from_overtime',
        'late_minutes', 'late_deduction', 'late_factor',
        'absence_deduction', 'manual_additions', 'manual_deductions',
        'adjustments_total', 'loan_deduction_amount', 'gross_salary',
        'total_allowances', 'total_deductions', 'loan_deduction', 'net_salary',
        'balance_before', 'balance_after',
        'payment_date', 'fiscal_period', 'payment_method', 'status',
        'notes', 'created_by',
        'statement_requested', 'statement_requested_at', 'statement_status',
    ];

    protected $casts = [
        'week_start'           => 'date',
        'week_end'             => 'date',
        'payment_date'         => 'date',
        'hours_worked'         => 'decimal:2',
        'overtime_hours'       => 'decimal:2',
        'gross_salary'         => 'decimal:2',
        'net_salary'           => 'decimal:2',
        'late_deduction'       => 'decimal:2',
        'absence_deduction'    => 'decimal:2',
        'manual_additions'     => 'decimal:2',
        'manual_deductions'    => 'decimal:2',
        'loan_deduction_amount'=> 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}