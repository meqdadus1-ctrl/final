<x-app-layout>
    <x-slot name="title">طلبات التوظيف</x-slot>

    <!-- فلتر -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jobs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">-- كل الحالات --</option>
                        <option value="new"       {{ request('status') == 'new'       ? 'selected' : '' }}>جديد</option>
                        <option value="reviewing" {{ request('status') == 'reviewing' ? 'selected' : '' }}>قيد المراجعة</option>
                        <option value="interview" {{ request('status') == 'interview' ? 'selected' : '' }}>مقابلة</option>
                        <option value="accepted"  {{ request('status') == 'accepted'  ? 'selected' : '' }}>مقبول</option>
                        <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>مرفوض</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">القسم</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- كل الأقسام --</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('jobs.index') }}" class="btn btn-secondary flex-fill">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-briefcase me-2"></i> طلبات التوظيف</span>
            <a href="{{ route('jobs.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> إضافة طلب
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الاسم</th>
                        <th>الموبايل</th>
                        <th>المسمى الوظيفي</th>
                        <th>القسم</th>
                        <th>الخبرة</th>
                        <th>CV</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td class="px-4 fw-semibold">{{ $app->full_name }}</td>
                        <td>{{ $app->mobile }}</td>
                        <td>{{ $app->position }}</td>
                        <td>{{ $app->department->name ?? '—' }}</td>
                        <td>{{ $app->experience_years }} سنة</td>
                        <td>
                            @if($app->cv_path)
                                <a href="{{ asset('storage/' . $app->cv_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $app->status_label['color'] }}">
                                {{ $app->status_label['label'] }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('jobs.show', $app) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('jobs.destroy', $app) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف الطلب؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد طلبات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
        <div class="card-footer">{{ $applications->links() }}</div>
        @endif
    </div>
</x-app-layout>