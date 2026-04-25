<x-app-layout>
<x-slot name="title">لوحة تحكم التطبيق</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">📱 لوحة تحكم التطبيق</h4>
            <small class="text-muted">إدارة طلبات الموظفين من التطبيق</small>
        </div>
        @if($totalPending > 0)
        <span class="badge bg-danger fs-6">{{ $totalPending }} طلب معلّق</span>
        @else
        <span class="badge bg-success fs-6">✅ لا توجد طلبات معلّقة</span>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ===== 1. طلبات تعديل البنك ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">🏦 طلبات تعديل البنك</span>
            <span class="badge bg-{{ $pendingBanks->count() > 0 ? 'warning text-dark' : 'secondary' }}">
                {{ $pendingBanks->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @forelse($pendingBanks as $emp)
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="fw-semibold">{{ $emp->name }}</div>
                        <small class="text-muted">{{ $emp->department?->name }}</small>
                    </div>
                    <div class="col-md-5">
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="text-muted">البنك</div>
                                <div class="fw-semibold">
                                    @match($emp->pending_bank_type)
                                        'bank_of_palestine' => '🏦 بنك فلسطين',
                                        'pal_pay'           => '💳 Pal Pay',
                                        'jawwal_pay'        => '📱 جوال',
                                        default             => $emp->pending_bank_type
                                    @endmatch
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">اسم صاحب الحساب</div>
                                <div class="fw-semibold">{{ $emp->pending_account_name }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">رقم الحساب</div>
                                <div class="fw-semibold font-monospace">{{ $emp->pending_bank_account }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <form action="{{ route('mobile.bank.approve', $emp) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" onclick="return confirm('اعتماد بيانات البنك؟')">
                                <i class="fas fa-check me-1"></i> اعتماد
                            </button>
                        </form>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#rejectBankModal{{ $emp->id }}">
                            <i class="fas fa-times me-1"></i> رفض
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal رفض البنك --}}
            <div class="modal fade" id="rejectBankModal{{ $emp->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">رفض طلب بنك — {{ $emp->name }}</h5></div>
                        <form action="{{ route('mobile.bank.reject', $emp) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <label class="form-label">سبب الرفض (اختياري)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="مثال: رقم الحساب غير صحيح..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger">رفض الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">لا توجد طلبات معلّقة</div>
            @endforelse
        </div>
    </div>

    {{-- ===== 2. طلبات السلف ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">💳 طلبات السلف</span>
            <span class="badge bg-{{ $pendingLoans->count() > 0 ? 'warning text-dark' : 'secondary' }}">
                {{ $pendingLoans->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @forelse($pendingLoans as $loan)
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="fw-semibold">{{ $loan->employee->name }}</div>
                        <small class="text-muted">{{ $loan->employee->department?->name }}</small>
                    </div>
                    <div class="col-md-5">
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="text-muted">المبلغ</div>
                                <div class="fw-bold text-primary">{{ number_format($loan->total_amount, 2) }} ₪</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">الأقساط</div>
                                <div>{{ $loan->installments_total }} قسط</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">القسط</div>
                                <div>{{ number_format($loan->installment_amount, 2) }} ₪</div>
                            </div>
                        </div>
                        @if($loan->description)
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-comment me-1"></i>{{ $loan->description }}
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <form action="{{ route('mobile.loans.approve', $loan) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" onclick="return confirm('اعتماد السلفة؟')">
                                <i class="fas fa-check me-1"></i> اعتماد
                            </button>
                        </form>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#rejectLoanModal{{ $loan->id }}">
                            <i class="fas fa-times me-1"></i> رفض
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal رفض السلفة --}}
            <div class="modal fade" id="rejectLoanModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">رفض طلب سلفة — {{ $loan->employee->name }}</h5></div>
                        <form action="{{ route('mobile.loans.reject', $loan) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <label class="form-label fw-semibold">سبب الرفض <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="مثال: الرصيد غير كافٍ..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger">رفض الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">لا توجد طلبات معلّقة</div>
            @endforelse
        </div>
    </div>

    {{-- ===== 3. طلبات الإجازات ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">🌴 طلبات الإجازات</span>
            <span class="badge bg-{{ $pendingLeaves->count() > 0 ? 'warning text-dark' : 'secondary' }}">
                {{ $pendingLeaves->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @forelse($pendingLeaves as $leave)
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="fw-semibold">{{ $leave->employee->name }}</div>
                        <small class="text-muted">{{ $leave->employee->department?->name }}</small>
                    </div>
                    <div class="col-md-5">
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="text-muted">النوع</div>
                                <div class="fw-semibold">{{ $leave->leaveType?->name }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">الفترة</div>
                                <div>{{ $leave->start_date->format('d/m') }} — {{ $leave->end_date->format('d/m/Y') }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">الأيام</div>
                                <div class="fw-bold">{{ $leave->total_days }} يوم</div>
                            </div>
                        </div>
                        @if($leave->reason)
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-comment me-1"></i>{{ $leave->reason }}
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <form action="{{ route('mobile.leaves.approve', $leave) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" onclick="return confirm('اعتماد الإجازة؟')">
                                <i class="fas fa-check me-1"></i> اعتماد
                            </button>
                        </form>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#rejectLeaveModal{{ $leave->id }}">
                            <i class="fas fa-times me-1"></i> رفض
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal رفض الإجازة --}}
            <div class="modal fade" id="rejectLeaveModal{{ $leave->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">رفض إجازة — {{ $leave->employee->name }}</h5></div>
                        <form action="{{ route('mobile.leaves.reject', $leave) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <label class="form-label fw-semibold">سبب الرفض <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="مثال: ضغط العمل في هذه الفترة..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger">رفض الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">لا توجد طلبات معلّقة</div>
            @endforelse
        </div>
    </div>

    {{-- ===== 4. طلبات كشف الحساب ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">📄 طلبات كشف الحساب</span>
            <span class="badge bg-{{ $pendingStatements->count() > 0 ? 'warning text-dark' : 'secondary' }}">
                {{ $pendingStatements->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @forelse($pendingStatements as $salary)
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="fw-semibold">{{ $salary->employee->name }}</div>
                        <small class="text-muted">{{ $salary->fiscal_period }}</small>
                    </div>
                    <div class="col-md-5 small">
                        <span class="text-muted">الفترة: </span>
                        {{ $salary->week_start?->format('d/m/Y') }} — {{ $salary->week_end?->format('d/m/Y') }}
                        <span class="ms-3 text-muted">الصافي: </span>
                        <span class="fw-bold text-success">{{ number_format($salary->net_salary, 2) }} ₪</span>
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <a href="{{ route('salary.thermal', $salary) }}" target="_blank"
                           class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-print me-1"></i> طباعة
                        </a>
                        <form action="{{ route('mobile.salary.statement', $salary) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" onclick="return confirm('اعتماد وإرسال كشف الحساب؟')">
                                <i class="fas fa-paper-plane me-1"></i> اعتماد وإرسال
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">لا توجد طلبات معلّقة</div>
            @endforelse
        </div>
    </div>

    {{-- ===== 5. إدارة قفل بيانات البنك ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">🔒 إدارة قفل بيانات البنك</span>
            <small class="text-muted">تحكم بصلاحية تعديل البنك لكل موظف</small>
        </div>
        <div class="card-body p-0">
            @forelse($allEmployees as $emp)
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="fw-semibold">{{ $emp->name }}</div>
                        <small class="text-muted">{{ $emp->department?->name }}</small>
                    </div>
                    <div class="col-md-4">
                        <div class="small">
                            @if($emp->bank_account)
                                <span class="text-muted">الحساب: </span>
                                <span class="font-monospace">{{ $emp->bank_account }}</span>
                            @else
                                <span class="text-muted">لا يوجد حساب مسجّل</span>
                            @endif
                        </div>
                        @if($emp->bank_info_pending)
                            <span class="badge bg-warning text-dark mt-1">طلب تعديل معلّق</span>
                        @endif
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        @if($emp->bank_info_locked)
                            {{-- مقفول → زر فتح --}}
                            <form action="{{ route('mobile.bank.unlock', $emp) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-success btn-sm"
                                    onclick="return confirm('السماح للموظف بتعديل بيانات البنك؟')">
                                    <i class="fas fa-lock-open me-1"></i> فتح التعديل
                                </button>
                            </form>
                        @else
                            {{-- مفتوح → زر قفل --}}
                            <form action="{{ route('mobile.bank.lock', $emp) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('قفل بيانات البنك لهذا الموظف؟')">
                                    <i class="fas fa-lock me-1"></i> قفل التعديل
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">لا يوجد موظفون</div>
            @endforelse
        </div>
    </div>

</div>
</x-app-layout>
