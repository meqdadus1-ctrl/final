<x-app-layout>
    <x-slot name="title">سحب بيانات البصمة</x-slot>

    <div class="container-fluid py-4" dir="rtl">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">🖐️ سحب بيانات جهاز البصمة</h4>
                <small class="text-muted">استيراد سجلات الحضور من جهاز ZKTeco</small>
            </div>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> العودة للحضور
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-8">

                {{-- إعدادات الجهاز --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="fas fa-network-wired me-2"></i> إعدادات الاتصال بالجهاز
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('attendance.pull') }}" id="pullForm">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">عنوان IP الجهاز <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="ip"
                                           id="deviceIp"
                                           class="form-control @error('ip') is-invalid @enderror"
                                           placeholder="192.168.1.201"
                                           value="{{ old('ip', '192.168.1.201') }}"
                                           required>
                                    @error('ip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">البورت <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="port"
                                           id="devicePort"
                                           class="form-control @error('port') is-invalid @enderror"
                                           placeholder="4370"
                                           value="{{ old('port', '4370') }}"
                                           min="1" max="65535"
                                           required>
                                    @error('port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">البورت الافتراضي لـ ZKTeco هو 4370</small>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    {{-- زر اختبار الاتصال --}}
                                    <form method="POST" action="{{ route('attendance.ping') }}" id="pingForm" class="w-100">
                                        @csrf
                                        <input type="hidden" name="ip"   id="pingIp">
                                        <input type="hidden" name="port" id="pingPort">
                                        <button type="button" class="btn btn-outline-info w-100" onclick="submitPing()" id="pingBtn">
                                            <i class="fas fa-satellite-dish me-1"></i> اختبار الاتصال
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">من تاريخ <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="date_from"
                                           class="form-control @error('date_from') is-invalid @enderror"
                                           value="{{ old('date_from', \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::THURSDAY)->toDateString()) }}"
                                           {{-- الخميس الأقرب --}}
                                           required>
                                    @error('date_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">إلى تاريخ <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="date_to"
                                           class="form-control @error('date_to') is-invalid @enderror"
                                           value="{{ old('date_to', \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::THURSDAY)->addDays(6)->toDateString()) }}"
                                           {{-- الأربعاء = الخميس + 6 أيام --}}
                                           required>
                                    @error('date_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- أزرار الأسبوع السريع --}}
                            <div class="mt-3">
                                <label class="form-label fw-semibold text-muted small">اختيار سريع:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setCurrentWeek()">
                                        📅 الأسبوع الحالي
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setLastWeek()">
                                        ⬅️ الأسبوع الماضي
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCurrentMonth()">
                                        📆 الشهر الحالي
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    الجمعة تُحسب إجازة وتُتجاهل سجلاتها تلقائياً.
                                    السجلات اليدوية لن تُستبدَل.
                                </div>
                                <button type="submit" class="btn btn-success px-4" id="submitBtn">
                                    <i class="fas fa-sync me-2"></i> سحب البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- معلومات وتوضيحات --}}
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> ملاحظات مهمة</h6>
                        <ul class="mb-0 text-muted small">
                            <li class="mb-1">الأسبوع يبدأ <strong>الخميس</strong> وينتهي <strong>الأربعاء</strong></li>
                            <li class="mb-1">يوم <strong>الجمعة</strong> إجازة أسبوعية — يُتجاهل تلقائياً</li>
                            <li class="mb-1">الجهاز يُرسل نوع السجل (دخول/خروج) وسيُستخدم تلقائياً</li>
                            <li class="mb-1">إذا أُدخل الحضور يدوياً مسبقاً، لن يُستبدَل</li>
                            <li class="mb-1">الموظفون غير المُسجّل لهم رقم بصمة سيُتجاهلون</li>
                            <li>التأخير يُحتسب إذا تجاوز دخول الموظف وقت الوردية بأكثر من 5 دقائق</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // الأسبوع يبدأ الخميس (4) وينتهي الأربعاء (3)
        function getWeekBounds(offsetWeeks = 0) {
            const today = new Date();
            const day   = today.getDay(); // 0=Sunday ... 4=Thursday

            // عدد الأيام للرجوع لأقرب خميس سابق
            // Thursday = 4, اذا اليوم خميس offset=0
            const daysToLastThursday = (day >= 4) ? (day - 4) : (day + 3);
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - daysToLastThursday + (offsetWeeks * 7));

            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 5); // الأربعاء = خميس + 6 أيام - 1 (بدون الجمعة)
            // خميس، سبت، أحد، اثنين، ثلاثاء، أربعاء = 6 أيام (الجمعة محذوفة)
            weekEnd.setDate(weekStart.getDate() + 6);

            return {
                from: formatDate(weekStart),
                to:   formatDate(weekEnd)
            };
        }

        function formatDate(d) {
            return d.getFullYear() + '-'
                + String(d.getMonth() + 1).padStart(2, '0') + '-'
                + String(d.getDate()).padStart(2, '0');
        }

        function setCurrentWeek() {
            const w = getWeekBounds(0);
            document.querySelector('[name=date_from]').value = w.from;
            document.querySelector('[name=date_to]').value   = w.to;
        }

        function setLastWeek() {
            const w = getWeekBounds(-1);
            document.querySelector('[name=date_from]').value = w.from;
            document.querySelector('[name=date_to]').value   = w.to;
        }

        function setCurrentMonth() {
            const today = new Date();
            const from  = new Date(today.getFullYear(), today.getMonth(), 1);
            const to    = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            document.querySelector('[name=date_from]').value = formatDate(from);
            document.querySelector('[name=date_to]').value   = formatDate(to);
        }

        // تعطيل الزر أثناء الإرسال لمنع الضغط المزدوج
        document.getElementById('pullForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري السحب...';
        });

        // زر اختبار الاتصال — يأخذ IP/Port من الـ pullForm ويرسلها لـ pingForm
        function submitPing() {
            const ip   = document.getElementById('deviceIp').value.trim();
            const port = document.getElementById('devicePort').value.trim();
            if (!ip || !port) {
                alert('يرجى إدخال IP والبورت أولاً');
                return;
            }
            document.getElementById('pingIp').value   = ip;
            document.getElementById('pingPort').value = port;

            const btn = document.getElementById('pingBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الاتصال...';
            document.getElementById('pingForm').submit();
        }
    </script>
</x-app-layout>
