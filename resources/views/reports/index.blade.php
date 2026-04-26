<x-app-layout>
<x-slot name="title">التقارير</x-slot>

<div class="container-fluid" dir="rtl">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>تقارير الموظفين</h4>
            <small class="text-muted">اختر الموظفين والفترة والأقسام ثم اضغط إنشاء التقرير</small>
        </div>
    </div>

    <form id="reportForm" method="GET">

        <div class="row g-4">

            {{-- ===== COL RIGHT: الفلاتر ===== --}}
            <div class="col-lg-4">

                {{-- الفترة الزمنية --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header py-2 fw-semibold small">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i>الفترة الزمنية
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="small text-muted mb-1 d-block">من تاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="from" id="fromDate" class="form-control form-control-sm"
                                value="{{ now()->startOfMonth()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted mb-1 d-block">إلى تاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="to" id="toDate" class="form-control form-control-sm"
                                value="{{ now()->toDateString() }}" required>
                        </div>
                        {{-- اختصارات سريعة --}}
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="setRange('week')">أسبوع</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="setRange('month')">هذا الشهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="setRange('last_month')">الشهر الماضي</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="setRange('quarter')">ربع سنة</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small" onclick="setRange('year')">هذه السنة</button>
                        </div>
                    </div>
                </div>

                {{-- أقسام التقرير --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header py-2 fw-semibold small">
                        <i class="fas fa-list-check me-1 text-primary"></i>أقسام التقرير
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="attendance" id="sec_att" checked>
                            <label class="form-check-label small" for="sec_att">
                                <span class="badge bg-success me-1">①</span>الحضور والانصراف
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="salary" id="sec_sal" checked>
                            <label class="form-check-label small" for="sec_sal">
                                <span class="badge bg-primary me-1">②</span>كشف الراتب
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="loans" id="sec_loan" checked>
                            <label class="form-check-label small" for="sec_loan">
                                <span class="badge bg-danger me-1">③</span>السلف
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="leaves" id="sec_leave" checked>
                            <label class="form-check-label small" for="sec_leave">
                                <span class="badge bg-warning text-dark me-1">④</span>الإجازات
                            </label>
                        </div>
                    </div>
                </div>

                {{-- أزرار الإجراء --}}
                <div class="d-grid gap-2">
                    <button type="submit" formaction="{{ route('reports.generate') }}"
                        class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>معاينة التقرير
                    </button>
                    <button type="submit" formaction="{{ route('reports.pdf') }}" target="_blank"
                        class="btn btn-danger" onclick="this.form.target='_blank'">
                        <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                    </button>
                </div>

            </div>

            {{-- ===== COL LEFT: اختيار الموظفين ===== --}}
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small"><i class="fas fa-users me-1 text-primary"></i>اختيار الموظفين</span>
                        <div class="d-flex gap-2 align-items-center">
                            {{-- فلتر بالقسم --}}
                            <select id="deptFilter" class="form-select form-select-sm" style="width:150px">
                                <option value="">كل الأقسام</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">تحديد الكل</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">إلغاء الكل</button>
                        </div>
                    </div>
                    <div class="card-body p-2" style="max-height:480px; overflow-y:auto;">

                        {{-- بحث سريع --}}
                        <div class="mb-2 px-1">
                            <input type="text" id="empSearch" class="form-control form-control-sm"
                                placeholder="🔍 بحث باسم الموظف..." oninput="filterEmployees()">
                        </div>

                        <div id="empList">
                            @foreach($employees as $emp)
                            <div class="emp-item d-flex align-items-center gap-2 p-2 rounded hover-bg mb-1"
                                 data-dept="{{ $emp->department_id }}"
                                 data-name="{{ strtolower($emp->name) }}"
                                 style="cursor:pointer; transition:background 0.15s"
                                 onmouseover="this.style.background='#f0f4ff'"
                                 onmouseout="this.style.background=''"
                                 onclick="toggleEmp(this)">
                                <input type="checkbox" name="employees[]" value="{{ $emp->id }}"
                                       class="form-check-input emp-check mt-0" onclick="event.stopPropagation()">
                                <div class="flex-fill">
                                    <div class="small fw-semibold">{{ $emp->name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $emp->department->name ?? '—' }}</div>
                                </div>
                                <span class="badge bg-light text-dark border" style="font-size:10px">
                                    {{ $emp->salary_type === 'hourly' ? 'بالساعة' : 'ثابت' }}
                                </span>
                            </div>
                            @endforeach
                        </div>

                        @if($employees->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                                لا يوجد موظفون نشطون
                            </div>
                        @endif
                    </div>
                    <div class="card-footer py-2 small text-muted" id="selectionCount">
                        لم يتم اختيار أي موظف
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
// ===== اختصارات التاريخ =====
function setRange(range) {
    const today = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    let from = new Date(today), to = new Date(today);

    if (range === 'week') {
        from.setDate(today.getDate() - 6);
    } else if (range === 'month') {
        from = new Date(today.getFullYear(), today.getMonth(), 1);
    } else if (range === 'last_month') {
        from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        to   = new Date(today.getFullYear(), today.getMonth(), 0);
    } else if (range === 'quarter') {
        from.setMonth(today.getMonth() - 3);
    } else if (range === 'year') {
        from = new Date(today.getFullYear(), 0, 1);
    }

    document.getElementById('fromDate').value = fmt(from);
    document.getElementById('toDate').value   = fmt(to);
}

// ===== تحديد / إلغاء الكل =====
function selectAll() {
    document.querySelectorAll('.emp-item:not([style*="none"]) .emp-check').forEach(c => c.checked = true);
    updateCount();
}
function deselectAll() {
    document.querySelectorAll('.emp-check').forEach(c => c.checked = false);
    updateCount();
}
function toggleEmp(row) {
    const cb = row.querySelector('.emp-check');
    cb.checked = !cb.checked;
    updateCount();
}

// ===== فلتر بالقسم =====
document.getElementById('deptFilter').addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('.emp-item').forEach(item => {
        item.style.display = (!val || item.dataset.dept == val) ? '' : 'none';
    });
    updateCount();
});

// ===== بحث =====
function filterEmployees() {
    const q = document.getElementById('empSearch').value.toLowerCase();
    const dept = document.getElementById('deptFilter').value;
    document.querySelectorAll('.emp-item').forEach(item => {
        const nameMatch = item.dataset.name.includes(q);
        const deptMatch = !dept || item.dataset.dept == dept;
        item.style.display = (nameMatch && deptMatch) ? '' : 'none';
    });
}

// ===== عداد التحديد =====
function updateCount() {
    const n = document.querySelectorAll('.emp-check:checked').length;
    document.getElementById('selectionCount').textContent =
        n === 0 ? 'لم يتم اختيار أي موظف' : `تم تحديد ${n} موظف`;
}
document.querySelectorAll('.emp-check').forEach(cb => {
    cb.addEventListener('change', updateCount);
});

// ===== التحقق قبل الإرسال =====
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.emp-check:checked').length;
    const sections = document.querySelectorAll('input[name="sections[]"]:checked').length;
    if (checked === 0) {
        e.preventDefault();
        alert('يرجى اختيار موظف واحد على الأقل');
        return;
    }
    if (sections === 0) {
        e.preventDefault();
        alert('يرجى اختيار قسم واحد على الأقل من التقرير');
    }
});
</script>
</x-app-layout>
