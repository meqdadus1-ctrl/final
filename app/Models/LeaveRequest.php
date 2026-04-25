<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date',
        'total_days', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}