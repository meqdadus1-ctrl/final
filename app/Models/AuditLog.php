<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'model_label', 'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            default   => $this->action,
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            default   => 'secondary',
        };
    }

    public function getModelNameAttribute(): string
    {
        $map = [
            'App\\Models\\Employee'          => 'موظف',
            'App\\Models\\SalaryPayment'     => 'راتب',
            'App\\Models\\EmployeeLedger'    => 'قيد محاسبي',
            'App\\Models\\Loan'              => 'سلفة',
            'App\\Models\\SalaryAdjustment'  => 'تعديل راتب',
            'App\\Models\\LeaveRequest'      => 'إجازة',
        ];
        return $map[$this->model_type] ?? class_basename($this->model_type);
    }

    /* =====================================================
     *  مساعد ثابت لتسجيل قيد يدوياً من أي مكان
     * ===================================================== */
    public static function record(
        string $action,
        Model  $model,
        array  $oldValues = [],
        array  $newValues = []
    ): void {
        // تجاهل إذا كنا في وضع حساب الأرصدة الجماعي
        if (defined('RECALCULATING_BALANCES') && RECALCULATING_BALANCES) {
            return;
        }

        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'model_label' => method_exists($model, 'getAuditLabel') ? $model->getAuditLabel() : null,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'ip_address'  => request()->ip(),
        ]);
    }
}
