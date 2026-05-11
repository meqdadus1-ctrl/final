<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id', 'reviewer_id', 'year', 'week_number',
        'punctuality', 'quality', 'productivity', 'teamwork', 'communication',
        'strengths', 'improvements', 'notes', 'status',
    ];

    protected $casts = ['year' => 'integer', 'week_number' => 'integer'];

    public function getWeekLabelAttribute(): string
    {
        $start = now()->setISODate($this->year, $this->week_number)->startOfWeek()->format('d/m/Y');
        $end   = now()->setISODate($this->year, $this->week_number)->endOfWeek()->format('d/m/Y');
        return "الأسبوع {$this->week_number} ({$start} — {$end})";
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getScoreLabelAttribute(): string
    {
        return match(true) {
            $this->overall_score >= 4.5 => 'ممتاز',
            $this->overall_score >= 3.5 => 'جيد جداً',
            $this->overall_score >= 2.5 => 'جيد',
            $this->overall_score >= 1.5 => 'مقبول',
            default                     => 'ضعيف',
        };
    }

    public function getScoreColorAttribute(): string
    {
        return match(true) {
            $this->overall_score >= 4.5 => 'success',
            $this->overall_score >= 3.5 => 'primary',
            $this->overall_score >= 2.5 => 'warning',
            default                     => 'danger',
        };
    }
}
