<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileNotification extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'body',
        'data',
        'target',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
