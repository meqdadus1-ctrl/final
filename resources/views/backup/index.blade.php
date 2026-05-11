<x-app-layout>
<x-slot name="title">النسخ الاحتياطية</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-database text-danger me-2"></i>النسخ الاحتياطية</h4>
            <small class="text-muted">نسخ احتياطية يومية تلقائية لقاعدة البيانات</small>
        </div>
        <form method="POST" action="{{ route('backup.run') }}">
            @csrf
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('إنشاء نسخة احتياطية الآن؟')">
                <i class="fas fa-plus me-1"></i>إنشاء نسخة الآن
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if(empty($files))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-database fa-3x mb-3 d-block opacity-25"></i>
                    <p>لا توجد نسخ احتياطية بعد.</p>
                    <p class="small">اضغط "إنشاء نسخة الآن" لبدء أول نسخة، أو انتظر النسخ التلقائي اليومي.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">اسم الملف</th>
                            <th>الحجم</th>
                            <th>التاريخ</th>
                            <th class="text-end px-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td class="px-3 fw-semibold">
                                <i class="fas fa-file-archive text-secondary me-2"></i>
                                {{ $file['name'] }}
                            </td>
                            <td class="text-muted">
                                @php
                                    $kb = $file['size'] / 1024;
                                    $mb = $kb / 1024;
                                @endphp
                                {{ $mb >= 1 ? number_format($mb, 1) . ' MB' : number_format($kb, 0) . ' KB' }}
                            </td>
                            <td class="text-muted">
                                {{ \Carbon\Carbon::createFromTimestamp($file['modified'])->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('backup.download', urlencode($file['name'])) }}"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-download me-1"></i>تنزيل
                                </a>
                                <form method="POST" action="{{ route('backup.destroy', urlencode($file['name'])) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('حذف هذه النسخة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-3 p-3 bg-light rounded border small text-muted">
        <i class="fas fa-info-circle me-1"></i>
        النسخ الاحتياطية مخزنة في: <code>storage/app/backups/</code> — يتم إنشاء نسخة تلقائية كل يوم عند منتصف الليل.
    </div>
</div>
</x-app-layout>
