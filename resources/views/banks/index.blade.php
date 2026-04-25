<x-app-layout>
    <x-slot name="title">البنوك</x-slot>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-university me-2"></i> قائمة البنوك ({{ $banks->total() }})</span>
            <div class="d-flex gap-2">
                <a href="{{ route('banks.report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> تقرير المدفوعات
                </a>
                <a href="{{ route('banks.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة بنك
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">#</th>
                        <th>الاسم</th>
                        <th>الفرع</th>
                        <th>رمز السويفت</th>
                        <th>عدد الموظفين</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $bank)
                    <tr>
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $bank->name }}</td>
                        <td class="text-muted">{{ $bank->branch ?? '—' }}</td>
                        <td><code>{{ $bank->swift_code ?? '—' }}</code></td>
                        <td>
                            <span class="badge bg-primary">{{ $bank->employees_count }} موظف</span>
                        </td>
                        <td>
                            <a href="{{ route('banks.show', $bank->id) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('banks.edit', $bank->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('banks.destroy', $bank->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف البنك؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">لا توجد بنوك بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($banks->hasPages())
        <div class="card-footer">
            {{ $banks->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
