<x-app-layout>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">← رجوع</a>
        <div>
            <h4 class="fw-bold mb-0">
                🔐 صلاحيات دور:
                {{ match($role->name) {
                    'admin'    => '👑 Admin',
                    'hr'       => '🧑‍💼 HR',
                    'manager'  => '📊 Manager',
                    'employee' => '👤 Employee',
                    default    => $role->name
                } }}
            </h4>
            <small class="text-muted">{{ count($rolePermissions) }} صلاحية مفعّلة</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf @method('PUT')

        <div class="row g-4">
            @php
            $groupLabels = [
                'employees'   => '👥 الموظفون',
                'departments' => '🏢 الأقسام',
                'attendance'  => '⏰ الحضور',
                'payslips'    => '💰 الرواتب',
                'loans'       => '💳 السلف',
                'leaves'      => '🌴 الإجازات',
                'jobs'        => '📋 التوظيف',
                'reports'     => '📊 التقارير',
                'banks'       => '🏦 البنوك',
                'users'       => '🔐 المستخدمون',
            ];
            @endphp

            @foreach($permissions as $group => $groupPerms)
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        {{ $groupLabels[$group] ?? $group }}
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($groupPerms as $perm)
                            <div class="col-6">
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           id="perm_{{ $perm->id }}"
                                           class="form-check-input"
                                           {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}
                                           {{ $role->name === 'admin' ? 'disabled checked' : '' }}>
                                    <label class="form-check-label small" for="perm_{{ $perm->id }}">
                                        {{ explode('.', $perm->name)[1] ?? $perm->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- تحديد الكل --}}
                        <div class="mt-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="toggleGroup(this, '{{ $group }}')">
                                تحديد الكل
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($role->name !== 'admin')
        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-5">💾 حفظ الصلاحيات</button>
        </div>
        @else
        <div class="alert alert-info mt-4">
            👑 دور Admin يمتلك كل الصلاحيات تلقائياً ولا يمكن تعديله.
        </div>
        @endif
    </form>
</div>

<script>
function toggleGroup(btn, group) {
    const checkboxes = document.querySelectorAll(`input[value^="${group}."]`);
    const allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    btn.textContent = allChecked ? 'تحديد الكل' : 'إلغاء الكل';
}
</script>
</x-app-layout>
