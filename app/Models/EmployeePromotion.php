<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePromotion extends Model
{
    protected $fillable = [
        'employee_id','type','from_title','to_title',
        'from_department_id','to_department_id',
        'from_salary','to_salary','effective_date','reason','approved_by',
    ];

    protected $casts = ['effective_date' => 'date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'promotion'     => 'ترقية',
            'transfer'      => 'نقل',
            'demotion'      => 'تخفيض رتبة',
            'title_change'  => 'تغيير المسمى',
            'salary_change' => 'تعديل الراتب',
            default         => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'promotion'     => 'success',
            'transfer'      => 'info',
            'demotion'      => 'danger',
            'title_change'  => 'secondary',
            'salary_change' => 'warning',
            default         => 'secondary',
        };
    }

    public function getSalaryDiffAttribute(): ?float
    {
        if ($this->from_salary && $this->to_salary) {
            return $this->to_salary - $this->from_salary;
        }
        return null;
    }
}
