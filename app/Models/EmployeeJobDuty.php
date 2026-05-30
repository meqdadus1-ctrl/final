<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeJobDuty extends Model
{
    protected $fillable = ['employee_id', 'title', 'sort_order'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
