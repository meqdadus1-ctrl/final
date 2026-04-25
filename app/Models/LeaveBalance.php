<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'year',
        'entitled_days', 'used_days'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute()
    {
        return $this->entitled_days - $this->used_days;
    }
}