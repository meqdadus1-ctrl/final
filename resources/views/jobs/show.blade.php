<x-app-layout>
    <x-slot name="title">تفاصيل طلب التوظيف</x-slot>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> بيانات المتقدم
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" width="35%">الاسم الكامل</th>
                            <td class="fw-semibold">{{ $job->full_name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">رقم الموبايل</th>
                            <td>{{ $job->mobile }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">البريد الإلكتروني</th>
                            <td>{{ $job->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">رقم الهوية</th>
                            <td>{{ $job->national_id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">المسمى الوظيفي</th>
                            <td>{{ $job->position }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">القسم</th>
                            <td>{{ $job->department->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">سنوات الخبرة</th>
                            <td>{{ $job->experience_years }} سنة</td>
                        </tr>
                        <tr>
                            <th class="text-muted">السيرة الذاتية</th>
                            <td>
                                @if($job->cv_path)
                                    <a href="{{ asset('storage/' . $job->cv_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-pdf me-1"></i> عرض CV
                                    </a>
                                @else
                                    <span class="text-muted">لم يرفق</span>
                                @endif
                            </td>
                        </tr>
                        @if($job->notes)
                        <tr>
                            <th class="text-muted">ملاحظات</th>
                            <td>{{ $job->notes }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="text-muted">تاريخ التقديم</th>
                            <td>{{ $job->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks me-2"></i> تحديث الحالة
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge {{ $job->status_label['color'] }} fs-6 px-3 py-2">
                            {{ $job->status_label['label'] }}
                        </span>
                    </div>

                    <form action="{{ route('jobs.status', $job) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">تغيير الحالة</label>
                            <select name="status" class="form-select">
                                <option value="new"       {{ $job->status == 'new'       ? 'selected' : '' }}>جديد</option>
                                <option value="reviewing" {{ $job->status == 'reviewing' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="interview" {{ $job->status == 'interview' ? 'selected' : '' }}>مقابلة</option>
                                <option value="accepted"  {{ $job->status == 'accepted'  ? 'selected' : '' }}>مقبول ✅</option>
                                <option value="rejected"  {{ $job->status == 'rejected'  ? 'selected' : '' }}>مرفوض ❌</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ملاحظات المراجع</label>
                            <textarea name="reviewer_notes" class="form-control" rows="4">{{ $job->reviewer_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                    </form>

                    @if($job->reviewed_at)
                    <hr>
                    <small class="text-muted">
                        آخر تحديث: {{ $job->reviewed_at->format('d/m/Y H:i') }}
                    </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>
</x-app-layout>