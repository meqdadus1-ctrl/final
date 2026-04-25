<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id','title','type','file_path','file_name',
        'file_size','expiry_date','notes','uploaded_by',
    ];

    protected $casts = ['expiry_date' => 'date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'id_card'     => 'بطاقة هوية',
            'passport'    => 'جواز سفر',
            'contract'    => 'عقد عمل',
            'certificate' => 'شهادة',
            'cv'          => 'سيرة ذاتية',
            'medical'     => 'فحص طبي',
            default       => 'أخرى',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'id_card'     => '🪪',
            'passport'    => '📘',
            'contract'    => '📝',
            'certificate' => '🎓',
            'cv'          => '📄',
            'medical'     => '🏥',
            default       => '📎',
        };
    }

    public function getExpiryStatusAttribute(): array
    {
        if (!$this->expiry_date) return ['label' => '', 'color' => ''];
        $days = now()->diffInDays($this->expiry_date, false);
        if ($days < 0)   return ['label' => 'منتهي الصلاحية', 'color' => 'danger'];
        if ($days <= 30) return ['label' => "ينتهي خلال {$days} يوم", 'color' => 'warning'];
        return ['label' => 'ساري', 'color' => 'success'];
    }

    public function getDownloadUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
