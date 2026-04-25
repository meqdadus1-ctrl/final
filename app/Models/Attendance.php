<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'work_hours',
        'overtime_hours',
        'status',
        'leave_approved',
        'leave_reason',
        'is_manual',
        'updated_by',
    ];

    protected $casts = [
        'date'           => 'date',
        'leave_approved' => 'boolean',
        'is_manual'      => 'boolean',
    ];

    // علاقة مع الموظف
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // المستخدم اللي عدّل السجل
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}