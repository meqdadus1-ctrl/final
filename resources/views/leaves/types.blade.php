<x-app-layout>
    <x-slot name="title">أنواع الإجازات</x-slot>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-2"></i> إضافة نوع إجازة
                </div>
                <div class="card-body">
                    <form action="{{ route('leaves.types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">اسم الإجازة</label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="مثال: سنوية، مرضية، طارئة"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الحد الأقصى (أيام/سنة)</label>
                            <input type="number" name="max_days_yearly"
                                class="form-control" placeholder="اتركه فارغاً = غير محدود"
                                value="{{ old('max_days_yearly') }}" min="1">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                {{-- hidden input يضمن إرسال 0 عندما يكون الـ checkbox غير محدد --}}
                                <input type="hidden" name="is_paid" value="0">
                                <input class="form-check-input" type="checkbox"
                                    name="is_paid" value="1"
                                    {{ old('is_paid', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">إجازة مدفوعة</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> أنواع الإجازات الحالية
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-4">الاسم</th>
                                <th>الحد الأقصى</th>
                                <th>مدفوعة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($types as $type)
                            <tr>
                                <td class="px-4 fw-semibold">{{ $type->name }}</td>
                                <td>{{ $type->max_days_yearly ?? 'غير محدود' }} يوم</td>
                                <td>
                                    <span class="badge {{ $type->is_paid ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $type->is_paid ? 'مدفوعة' : 'غير مدفوعة' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('leaves.types.destroy', $type) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">لا توجد أنواع بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>
</x-app-layout>
