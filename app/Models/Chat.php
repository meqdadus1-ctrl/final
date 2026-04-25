<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'employee_id',
        'sender_type',
        'message',
        'attachment_path',
        'attachment_type',
        'attachment_name',
        'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ---------- Relations ---------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /* ---------- Scopes ---------- */

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /* ---------- Accessors ---------- */

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? asset('storage/' . $this->attachment_path)
            : null;
    }
}
