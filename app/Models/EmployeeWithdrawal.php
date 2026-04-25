<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWithdrawal extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'product_description',
        'product_price',
        'status',
        'settled_at',
        'settled_in_payment_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'amount'        => 'decimal:2',
        'product_price' => 'decimal:2',
        'settled_at'    => 'datetime',
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

    public function settledInPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class, 'settled_in_payment_id');
    }

    /* ---------- Scopes ---------- */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSettled($query)
    {
        return $query->where('status', 'settled');
    }

    public function scopeForWeek($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /* ---------- Accessors ---------- */

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'قيد التسوية',
            'settled'   => 'مُسوّاة',
            'cancelled' => 'ملغاة',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'settled'   => 'success',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }
}
