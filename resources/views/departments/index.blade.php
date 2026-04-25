<x-app-layout>
    <x-slot name="title">الأقسام</x-slot>
    <div class="container-fluid py-4" dir="rtl">

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="fas fa-sitemap me-2"></i>قائمة الأقسام ({{ $departments->total() }})</span>
            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> إضافة قسم
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">#</th>
                        <th>اسم القسم</th>
                        <th>الوصف</th>
                        <th class="text-center">الموظفون</th>
                        <th class="text-center">تاريخ الإنشاء</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dep)
                    <tr>
                        <td class="px-3 text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $dep->name }}</div>
                            @if($dep->description)
                                <small class="text-muted">{{ Str::limit($dep->description, 50) }}</small>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $dep->description ? '—' : '—' }}</td>
                        <td class="text-center">
                            <a href="{{ route('departments.show', $dep->id) }}"
                               class="badge bg-primary text-decoration-none"
                               style="font-size:0.85rem; padding:6px 12px"
                               title="عرض موظفي القسم">
                                <i class="fas fa-users me-1"></i>{{ $dep->employees_count }} موظف
                            </a>
                        </td>
                        <td class="text-center text-muted small">{{ $dep->created_at->format('Y-m-d') }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('departments.show', $dep->id) }}"
                                   class="btn btn-sm btn-outline-success" title="عرض الموظفين">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="{{ route('departments.edit', $dep->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="تعديل القسم">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('departments.destroy', $dep->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف القسم؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="حذف القسم">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-sitemap fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد أقسام بعد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
        <div class="card-footer">
            {{ $departments->links() }}
        </div>
        @endif
    </div>

    </div>
</x-app-layout>