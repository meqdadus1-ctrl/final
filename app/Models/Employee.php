<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use App\Models\EmployeeLedger;
use App\Models\SalaryAdjustment;
use Carbon\Carbon;

class Employee extends Model
{
    protected $fillable = [
        // Basic
        'user_id','name','photo','mobile','national_id',
        'birth_date','gender','marital_status','nationality','religion',
        'email','personal_email','phone','phone2','address','city',
        'emergency_contact_name','emergency_contact_phone','emergency_contact_relation',

        // Work
        'employee_number','fingerprint_id','job_title','department_id','work_location',
        'contract_start','contract_end','contract_type','work_email','work_phone',
        'manager_id','hire_date','status',

        // Salary / Shift
        'salary','salary_type','base_salary','hourly_rate',
        'shift_start','shift_end','overtime_rate',

        // Finance (الصندوق)
        'opening_balance',

        // Bank
        'bank_id','account_name','bank_account','bank_type',
        'pending_bank_type','pending_account_name','pending_bank_account',
        'bank_info_pending','bank_info_locked',

        // Mobile App
        'fcm_token',

        // Education
        'education_level','education_major','university','graduation_year',

        // Misc
        'notes',
    ];

    protected $casts = [
        'birth_date'      => 'date',
        'hire_date'       => 'date',
        'contract_start'  => 'date',
        'contract_end'    => 'date',
        'opening_balance' => 'decimal:2',
    ];

    /* ---------- Scopes ---------- */

    /**
     * Scope to get only active employees.
     * Usage: Employee::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /* ---------- Relations ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->orderByDesc('created_at');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(EmployeePromotion::class)->orderByDesc('effective_date');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(SalaryPayment::class)->orderByDesc('payment_date');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)
            ->where('status', 'active')
            ->where('is_paused', 0);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(EmployeeLedger::class)->orderBy('entry_date')->orderBy('id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(SalaryAdjustment::class);
    }

    public function pendingAdjustments(): HasMany
    {
        return $this->hasMany(SalaryAdjustment::class)->where('status', 'pending');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class)->orderByDesc('created_at');
    }

    /* ---------- Accessors ---------- */

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getYearsOfServiceAttribute(): string
    {
        if (!$this->hire_date) return '—';
        $years  = $this->hire_date->diffInYears(now());
        $months = $this->hire_date->copy()->addYears($years)->diffInMonths(now());
        return $years > 0 ? "{$years} سنة و{$months} شهر" : "{$months} شهر";
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default_user.png');
    }

    public function getContractStatusAttribute(): array
    {
        if (!$this->contract_end) return ['label' => 'دائم', 'color' => 'success'];
        $daysLeft = now()->diffInDays($this->contract_end, false);
        if ($daysLeft < 0)   return ['label' => 'منتهي', 'color' => 'danger'];
        if ($daysLeft <= 30) return ['label' => "ينتهي خلال {$daysLeft} يوم", 'color' => 'warning'];
        return ['label' => 'ساري', 'color' => 'success'];
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male'   => 'ذكر',
            'female' => 'أنثى',
            default  => '—',
        };
    }

    public function getMaritalStatusLabelAttribute(): string
    {
        return match($this->marital_status) {
            'single'   => 'أعزب',
            'married'  => 'متزوج',
            'divorced' => 'مطلق',
            'widowed'  => 'أرمل',
            default    => '—',
        };
    }

    public function getContractTypeLabelAttribute(): string
    {
        return match($this->contract_type) {
            'permanent'  => 'دائم',
            'temporary'  => 'مؤقت',
            'part_time'  => 'دوام جزئي',
            'freelance'  => 'مستقل',
            default      => '—',
        };
    }

    /* =====================================================
     *  الحساب الجاري (الأمانات)
     *  =====================================================
     *  بيانات مالية مساعدة
     * ===================================================== */

    /** آخر قبضة أسبوعية (SalaryPayment) */
    public function getLastPaymentAttribute()
    {
        return $this->salaryPayments()
            ->orderByDesc('payment_date')
            ->first();
    }

    /** أجر اليوم التقديري (أجر الساعة × ساعات الوردية) */
    public function getDailyRateEstimateAttribute(): float
    {
        if ($this->hourly_rate && $this->shift_start && $this->shift_end) {
            $in  = strtotime($this->shift_start);
            $out = strtotime($this->shift_end);
            if ($in && $out && $out > $in) {
                $hours = ($out - $in) / 3600;
                return round($this->hourly_rate * $hours, 2);
            }
        }
        // fallback للراتب اليومي المقسّم
        if ($this->salary && $this->salary > 0) {
            return round($this->salary / 30, 2);
        }
        return 0;
    }

    /**
     * الأجر المتراكم منذ آخر قبضة حتى اليوم
     * بيعتمد على سجلات الحضور (present/late) × أجر اليوم
     *
     * ملاحظة: يعيد 0 إذا لم تكن هناك قبضة سابقة لتجنب الأرقام المضللة
     */
    public function accruedWagesSinceLastPayment(): float
    {
        $lastPayment = $this->last_payment;

        // لا توجد قبضة سابقة → لا نحسب أجراً متراكماً
        if (!$lastPayment) {
            return 0.0;
        }

        $from = $lastPayment->payment_date->copy()->addDay();

        $attendances = $this->attendances()
            ->whereBetween('date', [$from->toDateString(), now()->toDateString()])
            ->whereIn('status', ['present', 'late'])
            ->get();

        $rate = $this->daily_rate_estimate;
        $total = 0;
        foreach ($attendances as $a) {
            // إذا الساعات محسوبة فعلياً، استخدمها
            if ($this->hourly_rate && $a->work_hours > 0) {
                $total += $this->hourly_rate * $a->work_hours;
                if ($a->overtime_hours > 0 && $this->overtime_rate) {
                    $total += $this->overtime_rate * $a->overtime_hours;
                }
            } else {
                $total += $rate;
            }
        }
        return round($total, 2);
    }

    /**
     * الرصيد الحالي من الـ Ledger (آخر قيد مسجّل)
     * هذا هو الرصيد الحقيقي المحسوب من كشف الحساب.
     * موجب = الشركة مدينة للموظف
     * سالب = الموظف مدين للشركة
     *
     * ملاحظة: لا يوجد fallback للـ opening_balance لأن الرصيد الافتتاحي
     * يجب أن يُسجَّل صراحةً كقيد في الـ Ledger عبر recordOpeningBalance()
     */
    public function getLedgerBalanceAttribute(): float
    {
        $lastEntry = EmployeeLedger::where('employee_id', $this->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first();

        return $lastEntry ? (float) $lastEntry->balance_after : 0.0;
    }

    /**
     * الرصيد القديم (للتوافق مع الكود القديم)
     * @deprecated استخدم getLedgerBalanceAttribute بدلاً
     */
    public function getBalanceAttribute(): float
    {
        return $this->ledger_balance;
    }
}
