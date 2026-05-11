<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'priority', 'status',
        'due_date', 'department_id', 'parent_id', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /* ---------- Scopes ---------- */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'completed');
    }

    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /* ---------- Relations ---------- */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------- Accessors ---------- */

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'منخفضة',
            'medium' => 'متوسطة',
            'high'   => 'عالية',
            default  => '—',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'success',
            'medium' => 'warning',
            'high'   => 'danger',
            default  => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'قيد الانتظار',
            'in_progress' => 'جارٍ التنفيذ',
            'completed'   => 'مكتملة',
            default       => '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'secondary',
            'in_progress' => 'primary',
            'completed'   => 'success',
            default       => 'secondary',
        };
    }

    public function getCalendarColorAttribute(): string
    {
        if ($this->is_overdue) return '#dc3545';

        return match($this->status) {
            'pending'     => '#6c757d',
            'in_progress' => '#1e3a5f',
            'completed'   => '#198754',
            default       => '#1e3a5f',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status !== 'completed';
    }
}
