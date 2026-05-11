<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer مركزي — يُسجَّل على نماذج متعددة
 * يتجاهل تحديثات balance_after الجماعية (recalculateBalances)
 */
class AuditObserver
{
    public static bool $paused = false;

    private const SKIP_FIELDS = ['updated_at', 'created_at', 'remember_token'];

    public function created(Model $model): void
    {
        if ($this->shouldSkip()) return;

        $values = collect($model->getAttributes())
            ->except(self::SKIP_FIELDS)
            ->toArray();

        AuditLog::record('created', $model, [], $values);
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip()) return;

        $dirty = collect($model->getDirty())
            ->except(self::SKIP_FIELDS)
            ->keys();

        if ($dirty->isEmpty()) return;

        $old = $dirty->mapWithKeys(fn($k) => [$k => $model->getOriginal($k)])->toArray();
        $new = $dirty->mapWithKeys(fn($k) => [$k => $model->getAttribute($k)])->toArray();

        AuditLog::record('updated', $model, $old, $new);
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip()) return;

        $values = collect($model->getAttributes())
            ->except(self::SKIP_FIELDS)
            ->toArray();

        AuditLog::record('deleted', $model, $values, []);
    }

    // تجاهل السجل أثناء إعادة حساب الأرصدة الجماعية
    private function shouldSkip(): bool
    {
        return static::$paused;
    }
}
