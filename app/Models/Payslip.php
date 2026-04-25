<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Payslip — deprecated. نظام الرواتب الشهرية أُوقف.
 * استخدم SalaryPayment بدلاً.
 * @deprecated
 */
class Payslip extends Model
{
    protected $fillable = ['employee_id'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
