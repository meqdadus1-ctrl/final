<x-app-layout>
<x-slot name="title">سجل التدقيق</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-history text-secondary me-2"></i>سجل التدقيق</h4>
            <small class="text-muted">كل عملية إنشاء أو تعديل أو حذف في النظام</small>
        </div>
        <span class="badge bg-secondary fs-6">{{ number_format($logs->total()) }} سجل</span>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-semibold mb-1">الإجراء</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="created"  {{ request('action') === 'created'  ? 'selected' : '' }}>إنشاء</option>
                        <option value="updated"  {{ request('action') === 'updated'  ? 'selected' : '' }}>تعديل</option>
                        <option value="deleted"  {{ request('action') === 'deleted'  ? 'selected' : '' }}>حذف</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-semibold mb-1">النموذج</label>
                    <select name="model_type" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($modelTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('model_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-semibold mb-1">المستخدم</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-semibold mb-1">من تاريخ</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="small fw-semibold mb-1">إلى تاريخ</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">بحث</button>
                    <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>
                    لا توجد سجلات مطابقة
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">الوقت</th>
                            <th>المستخدم</th>
                            <th>الإجراء</th>
                            <th>النموذج</th>
                            <th>المعرّف</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td class="px-3 text-muted" style="white-space:nowrap">
                                {{ $log->created_at->format('d/m/Y') }}<br>
                                <small>{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    <span class="fw-semibold">{{ $log->user->name }}</span>
                                @else
                                    <span class="text-muted">النظام</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->action_color }}">{{ $log->action_label }}</span>
                            </td>
                            <td>{{ $log->model_name }}</td>
                            <td class="text-muted">
                                #{{ $log->model_id }}
                                @if($log->model_label)
                                    <br><small class="text-dark">{{ $log->model_label }}</small>
                                @endif
                            </td>
                            <td>
                                @if($log->action === 'updated' && $log->old_values)
                                    <button class="btn btn-xs btn-outline-secondary py-0 px-2 small"
                                        data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}">
                                        <i class="fas fa-chevron-down fa-xs"></i> تفاصيل
                                    </button>
                                    <div class="collapse mt-1" id="details-{{ $log->id }}">
                                        <div class="p-2 bg-light rounded border small">
                                            @foreach($log->old_values as $field => $oldVal)
                                                @php $newVal = $log->new_values[$field] ?? '—'; @endphp
                                                <div class="d-flex gap-2 mb-1">
                                                    <span class="text-muted fw-semibold" style="min-width:120px">{{ $field }}</span>
                                                    <span class="text-danger text-decoration-line-through">{{ $oldVal }}</span>
                                                    <span class="text-muted">→</span>
                                                    <span class="text-success">{{ $newVal }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($log->action === 'created' && $log->new_values)
                                    <span class="text-muted small">{{ count($log->new_values) }} حقل</span>
                                @elseif($log->action === 'deleted' && $log->old_values)
                                    <button class="btn btn-xs btn-outline-danger py-0 px-2 small"
                                        data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}">
                                        <i class="fas fa-eye fa-xs"></i> عرض
                                    </button>
                                    <div class="collapse mt-1" id="details-{{ $log->id }}">
                                        <div class="p-2 bg-light rounded border small">
                                            @foreach($log->old_values as $field => $val)
                                                <div><span class="text-muted fw-semibold">{{ $field }}:</span> {{ $val }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
