<x-app-layout>
    <x-slot name="title">لوحة التحكم</x-slot>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-white" style="background: #1e3a5f;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ \App\Models\Employee::count() }}</div>
                        <div>إجمالي الموظفين</div>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background: #2d8a4e;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ \App\Models\Department::count() }}</div>
                        <div>الأقسام</div>
                    </div>
                    <i class="fas fa-sitemap fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background: #c0392b;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ \App\Models\Employee::where('status','inactive')->count() }}</div>
                        <div>موظفون غير نشطين</div>
                    </div>
                    <i class="fas fa-user-slash fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background: #d35400;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ \App\Models\Bank::count() }}</div>
                        <div>البنوك</div>
                    </div>
                    <i class="fas fa-university fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> آخر الموظفين المضافين
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الاسم</th>
                        <th>القسم</th>
                        <th>نوع الراتب</th>
                        <th>الحالة</th>
                        <th>تاريخ التعيين</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Employee::with('department')->latest()->take(5)->get() as $emp)
                    <tr>
                        <td class="px-4 fw-semibold">{{ $emp->name }}</td>
                        <td>{{ $emp->department->name ?? '—' }}</td>
                        <td>{{ $emp->salary_type == 'fixed' ? 'ثابت' : 'بالساعة' }}</td>
                        <td>
                            <span class="badge {{ $emp->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $emp->status == 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td>{{ $emp->hire_date ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد موظفون بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>