{{--
    مكوّن Badge موحّد للحالات في جميع أنحاء التطبيق.

    الاستخدام:
        <x-status-badge :status="$model->status" type="leave" />
        <x-status-badge :status="$payslip->status" type="payslip" />
        <x-status-badge :status="$loan->status" type="loan" />
        <x-status-badge :status="$employee->status" type="employee" />

    المعاملات:
        status  - قيمة الحالة (نص)
        type    - نوع النموذج: leave | payslip | loan | employee | application | payment
--}}

@props(['status', 'type' => 'default'])

@php
    $config = match($type) {
        'leave' => [
            'pending'   => ['color' => 'warning text-dark', 'label' => 'قيد الانتظار'],
            'approved'  => ['color' => 'success',           'label' => 'موافق عليها'],
            'rejected'  => ['color' => 'danger',            'label' => 'مرفوضة'],
            'cancelled' => ['color' => 'secondary',         'label' => 'ملغاة'],
        ],
        'payslip' => [
            'draft'  => ['color' => 'secondary', 'label' => 'مسودة'],
            'issued' => ['color' => 'info',      'label' => 'صادر'],
            'paid'   => ['color' => 'success',   'label' => 'مدفوع'],
        ],
        'loan' => [
            'pending'   => ['color' => 'warning text-dark', 'label' => 'قيد المراجعة'],
            'active'    => ['color' => 'primary',           'label' => 'نشطة'],
            'completed' => ['color' => 'success',           'label' => 'مكتملة'],
            'cancelled' => ['color' => 'danger',            'label' => 'ملغاة'],
            'paid'      => ['color' => 'success',           'label' => 'مسددة'],
        ],
        'employee' => [
            'active'   => ['color' => 'success', 'label' => 'نشط'],
            'inactive' => ['color' => 'danger',  'label' => 'غير نشط'],
        ],
        'application' => [
            'new'       => ['color' => 'info',              'label' => 'جديد'],
            'reviewing' => ['color' => 'warning text-dark', 'label' => 'قيد المراجعة'],
            'interview' => ['color' => 'primary',           'label' => 'مقابلة'],
            'accepted'  => ['color' => 'success',           'label' => 'مقبول'],
            'rejected'  => ['color' => 'danger',            'label' => 'مرفوض'],
        ],
        'payment' => [
            'bank' => ['color' => 'primary',          'label' => 'بنك'],
            'cash' => ['color' => 'warning text-dark', 'label' => 'كاش'],
        ],
        default => []
    };

    $item  = $config[$status] ?? ['color' => 'secondary', 'label' => $status];
    $color = $item['color'];
    $label = $item['label'];
@endphp

<span class="badge bg-{{ $color }}">{{ $label }}</span>
