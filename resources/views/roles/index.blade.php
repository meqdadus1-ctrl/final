<x-app-layout>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">🔐 إدارة المستخدمين والأدوار</h4>
            <small class="text-muted">تعيين الأدوار وإدارة صلاحيات المستخدمين</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            + مستخدم جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Users Table --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">👥 المستخدمون</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">الاسم</th>
                                    <th>البريد</th>
                                    <th>الدور</th>
                                    <th class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="px-3 fw-semibold">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-secondary ms-1">أنت</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $user->email }}</td>
                                    <td>
                                        @forelse($user->roles as $role)
                                            <span class="badge" style="background:{{ match($role->name) {
                                                'admin'    => '#1e3a5f',
                                                'hr'       => '#065f46',
                                                'manager'  => '#92400e',
                                                'employee' => '#374151',
                                                default    => '#6b7280'
                                            } }};">
                                                {{ match($role->name) {
                                                    'admin'    => '👑 Admin',
                                                    'hr'       => '🧑‍💼 HR',
                                                    'manager'  => '📊 Manager',
                                                    'employee' => '👤 Employee',
                                                    default    => $role->name
                                                } }}
                                            </span>
                                        @empty
                                            <span class="text-muted small">بدون دور</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- تغيير الدور --}}
                                            <form action="{{ route('roles.assign') }}" method="POST" class="d-flex gap-1">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <select name="role" class="form-select form-select-sm" style="width:110px;">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                            {{ $role->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-primary" title="حفظ">✓</button>
                                            </form>

                                            {{-- حذف --}}
                                            @if($user->id !== auth()->id())
                                            <form action="{{ route('roles.users.delete', $user) }}" method="POST"
                                                  onsubmit="return confirm('حذف هذا المستخدم؟')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">لا يوجد مستخدمون</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($users->hasPages())
                <div class="card-footer">{{ $users->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Roles Summary --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">🎭 الأدوار وصلاحياتها</div>
                <div class="card-body p-0">
                    @foreach($roles as $role)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                {{ match($role->name) {
                                    'admin'    => '👑 Admin',
                                    'hr'       => '🧑‍💼 HR',
                                    'manager'  => '📊 Manager',
                                    'employee' => '👤 Employee',
                                    default    => $role->name
                                } }}
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary">{{ $role->permissions->count() }} صلاحية</span>
                                <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                            </div>
                        </div>
                        <div class="text-muted small">
                            {{ match($role->name) {
                                'admin'    => 'صلاحيات كاملة على كل النظام',
                                'hr'       => 'إدارة الموظفين والرواتب والحضور',
                                'manager'  => 'عرض التقارير والموافقة على الطلبات',
                                'employee' => 'عرض بياناته الشخصية فقط',
                                default    => ''
                            } }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal: مستخدم جديد --}}
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('roles.users.create') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">➕ إنشاء مستخدم جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">الدور <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary">إنشاء</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
