<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'bank_name',
        'branch',
        'swift_code',
        'notes',
    ];

    /**
     * Accessor ليوحد استخدام name / bank_name في الكود.
     */
    public function getBankNameAttribute($value)
    {
        return $value ?: $this->attributes['name'] ?? null;
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function salaryPayments()
    {
        return $this->hasManyThrough(SalaryPayment::class, Employee::class);
    }
}
