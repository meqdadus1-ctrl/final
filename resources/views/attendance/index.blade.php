<x-app-layout>
    <x-slot name="title">الحضور والانصراف</x-slot>

    {{-- استيراد من Excel --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header d-flex align-items-center gap-2"
             style="background:#f8f9fa;cursor:pointer"
             data-bs-toggle="collapse" data-bs-target="#excelImportBody">
            <i class="fas fa-file-excel text-success"></i>
            <span class="fw-semibold">استيراد الحضور من ملف Excel (جهاز البصمة)</span>
            <i class="fas fa-chevron-down ms-auto text-muted small"></i>
        </div>
        <div id="excelImportBody" class="collapse show">
        <div class="card-body">
            <form action="{{ route('attendance.import.excel') }}" method="POST"
                  enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-upload me-1"></i> اختر ملف XLS / XLSX
                    </label>
                    <input type="file" name="excel_file" class="form-control"
                           accept=".xls,.xlsx,.csv" required
                           onchange="document.getElementById('excelFileName').textContent = this.files[0]?.name ?? ''">
                    <div class="small text-muted mt-1">
                        الأعمدة المطلوبة: <strong>رقم البصمة</strong> ·
                        <strong>الاسم</strong> ·
                        <strong>التاريخ والوقت</strong>
                        <span id="excelFileName" class="text-primary ms-2"></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-file-import me-1"></i> استيراد
                    </button>
                </div>
                <div class="col-12">
                    <div class="alert alert-info py-2 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        سيتم مطابقة الموظف عبر <strong>رقم البصمة</strong> الموجود في بيانات كل موظف.
                        أول بصمة في اليوم = وقت الدخول · آخر بصمة = وقت الخروج.
                        أيام الجمعة تُتجاهل تلقائياً.
                        لن تُعدَّل السجلات اليدوية المدخلة مسبقاً.
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>

    {{-- زر سحب من البصمة --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2"
             style="cursor:pointer"
             data-bs-toggle="collapse" data-bs-target="#pullDeviceBody">
            <i class="fas fa-fingerprint me-2"></i>
            <span class="fw-semibold">سحب البيانات من جهاز البصمة مباشرة</span>
            <i class="fas fa-chevron-down ms-auto text-muted small"></i>
        </div>
        <div id="pullDeviceBody" class="collapse">
        <div class="card-body">
            <form action="{{ route('attendance.pull') }}" method="POST" class="row g-3 align-items-end">
    @csrf
    <div class="col-md-3">
        <label class="form-label">IP الجهاز</label>
        <input type="text" name="ip" class="form-control" value="10.10.1.3" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">البورت</label>
        <input type="number" name="port" class="form-control" value="4370" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">من تاريخ</label>
        <input type="date" name="date_from" class="form-control"
            value="{{ now()->startOfWeek()->format('Y-m-d') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">إلى تاريخ</label>
        <input type="date" name="date_to" class="form-control"
            value="{{ now()->endOfWeek()->format('Y-m-d') }}" required>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-download me-1"></i> سحب البيانات
        </button>
    </div>
</form>
        </div>
        </div>{{-- /.collapse #pullDeviceBody --}}
    </div>

    {{-- فلاتر البحث --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendance.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">التاريخ</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الموظف</label>
                    <select name="employee_id" class="form-select">
                        <option value="">-- كل الموظفين --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">-- كل الحالات --</option>
                        <option value="present"  {{ request('status') == 'present'  ? 'selected' : '' }}>حاضر</option>
                        <option value="absent"   {{ request('status') == 'absent'   ? 'selected' : '' }}>غائب</option>
                        <option value="late"     {{ request('status') == 'late'     ? 'selected' : '' }}>متأخر</option>
                        <option value="leave"    {{ request('status') == 'leave'    ? 'selected' : '' }}>إجازة</option>
                        <option value="holiday"  {{ request('status') == 'holiday'  ? 'selected' : '' }}>عطلة</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary flex-fill">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول الحضور --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i> سجلات الحضور والانصراف</span>
            <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> إضافة يدوية
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الموظف</th>
                        <th>التاريخ</th>
                        <th>وقت الدخول</th>
                        <th>وقت الخروج</th>
                        <th>ساعات العمل</th>
                        <th>أوفرتايم</th>
                        <th>الحالة</th>
                        <th>المصدر</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $rec)
                    <tr>
                        <td class="px-4 fw-semibold">{{ $rec->employee->name ?? '—' }}</td>
                        <td>{{ $rec->date->format('Y-m-d') }}</td>
                        <td>{{ $rec->check_in  ?? '—' }}</td>
                        <td>{{ $rec->check_out ?? '—' }}</td>
                        <td>{{ $rec->work_hours ?? '0' }} س</td>
                        <td>
                            @if($rec->overtime_hours > 0)
                                <span class="badge bg-warning text-dark">{{ $rec->overtime_hours }} س</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @php
                                $badges = [
                                    'present' => ['bg-success', 'حاضر'],
                                    'absent'  => ['bg-danger',  'غائب'],
                                    'late'    => ['bg-warning text-dark', 'متأخر'],
                                    'leave'   => ['bg-info text-dark',    'إجازة'],
                                    'holiday' => ['bg-secondary',         'عطلة'],
                                ];
                                $b = $badges[$rec->status] ?? ['bg-secondary', $rec->status];
                            @endphp
                            <span class="badge {{ $b[0] }}">{{ $b[1] }}</span>
                        </td>
                        <td>
                            @if($rec->is_manual)
                                <span class="badge bg-secondary"><i class="fas fa-pen me-1"></i>يدوي</span>
                            @else
                                <span class="badge bg-primary"><i class="fas fa-fingerprint me-1"></i>بصمة</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('attendance.edit', $rec->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('attendance.destroy', $rec->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">لا توجد سجلات بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
        <div class="card-footer">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</x-app-layout>