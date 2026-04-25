<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'full_name', 'mobile', 'email', 'national_id',
        'department_id', 'position', 'experience_years',
        'cv_path', 'notes', 'status', 'reviewed_by',
        'reviewed_at', 'reviewer_notes'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'new'       => ['label' => 'جديد',        'color' => 'bg-primary'],
            'reviewing' => ['label' => 'قيد المراجعة', 'color' => 'bg-warning text-dark'],
            'interview' => ['label' => 'مقابلة',       'color' => 'bg-info text-dark'],
            'accepted'  => ['label' => 'مقبول',        'color' => 'bg-success'],
            'rejected'  => ['label' => 'مرفوض',        'color' => 'bg-danger'],
            default     => ['label' => $this->status,  'color' => 'bg-secondary'],
        };
    }
}