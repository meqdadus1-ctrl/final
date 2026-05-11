<x-app-layout>
<x-slot name="title">إعدادات الرسائل (SMS / WhatsApp)</x-slot>
<div class="container-fluid py-4" dir="rtl" style="max-width:860px">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">⚙️ إعدادات الرسائل</h4>
            <small class="text-muted">تهيئة مزود الإرسال لإشعارات الرواتب (SMS أو WhatsApp)</small>
        </div>
        <a href="{{ route('salary.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('settings.messenger.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ===== تفعيل / تعطيل ===== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">تفعيل خاصية الإرسال</div>
                    <small class="text-muted">عند التعطيل لن تُرسَل أي رسائل حتى لو ضغطت "إرسال" في صفحة الراتب</small>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                        id="is_active" name="is_active" value="1"
                        {{ $settings->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">
                        {{ $settings->is_active ? 'مفعّل' : 'معطّل' }}
                    </label>
                </div>
            </div>
        </div>

        {{-- ===== اختيار المزود ===== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-2">
                <i class="fas fa-plug me-2"></i>مزود الإرسال
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check border rounded p-3 h-100 driver-option {{ $settings->driver === 'bulksms' ? 'border-primary bg-light' : '' }}"
                             style="cursor:pointer" onclick="selectDriver('bulksms')">
                            <input class="form-check-input" type="radio" name="driver" id="driver_sms"
                                value="bulksms" {{ $settings->driver === 'bulksms' ? 'checked' : '' }}
                                onchange="switchDriver()">
                            <label class="form-check-label w-100" for="driver_sms" style="cursor:pointer">
                                <div class="fw-bold mb-1">📨 SMS API</div>
                                <small class="text-muted">إرسال رسائل SMS عبر مزودين مثل BulkSMS أو أي API مشابه</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check border rounded p-3 h-100 driver-option {{ $settings->driver === 'whatsapp_cloud' ? 'border-success bg-light' : '' }}"
                             style="cursor:pointer" onclick="selectDriver('whatsapp_cloud')">
                            <input class="form-check-input" type="radio" name="driver" id="driver_wa"
                                value="whatsapp_cloud" {{ $settings->driver === 'whatsapp_cloud' ? 'checked' : '' }}
                                onchange="switchDriver()">
                            <label class="form-check-label w-100" for="driver_wa" style="cursor:pointer">
                                <div class="fw-bold mb-1">💬 WhatsApp Cloud API</div>
                                <small class="text-muted">إرسال رسائل WhatsApp عبر Meta Business API (مجاني حتى 1000 رسالة/شهر)</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== إعدادات SMS API ===== --}}
        <div id="section_bulksms" class="{{ $settings->driver === 'whatsapp_cloud' ? 'd-none' : '' }}">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-2 fw-semibold">
                    <i class="fas fa-server me-2 text-secondary"></i>إعدادات SMS API
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">رابط الـ API <span class="text-danger">*</span></label>
                            <input type="url" name="sms_api_url" class="form-control"
                                value="{{ old('sms_api_url', $settings->sms_api_url) }}"
                                placeholder="https://api.bulksms.com/v1/messages">
                            <div class="form-text">رابط endpoint الخاص بمزود SMS</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">مفتاح المصادقة (Basic Auth)</label>
                            <div class="input-group">
                                <input type="password" name="sms_api_auth" id="sms_api_auth" class="form-control"
                                    placeholder="{{ $settings->sms_api_auth ? '••••••••••••• (محفوظ)' : 'Base64 encoded username:password' }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('sms_api_auth')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">اتركه فارغاً للإبقاء على القيمة المحفوظة</div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>BulkSMS:</strong> من لوحة التحكم اذهب إلى <code>Settings → API Keys</code> وانسخ الـ Basic Auth token.
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== إعدادات WhatsApp ===== --}}
        <div id="section_whatsapp" class="{{ $settings->driver === 'bulksms' ? 'd-none' : '' }}">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-2 fw-semibold">
                    <i class="fab fa-whatsapp me-2 text-success"></i>إعدادات WhatsApp Cloud API
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number ID <span class="text-danger">*</span></label>
                            <input type="text" name="wa_phone_number_id" class="form-control"
                                value="{{ old('wa_phone_number_id', $settings->wa_phone_number_id) }}"
                                placeholder="123456789012345">
                            <div class="form-text">من Meta Developer Console → WhatsApp → Phone Numbers</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Access Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="wa_access_token" id="wa_access_token" class="form-control"
                                    placeholder="{{ $settings->wa_access_token ? '••••••••••••• (محفوظ)' : 'EAAxxxxxxxxxxxxxxx...' }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('wa_access_token')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                اتركه فارغاً للإبقاء على القيمة المحفوظة.
                                للحصول عليه: Meta Developer Console → WhatsApp → API Setup → Temporary/Permanent Token
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>ملاحظة:</strong> الـ Temporary Token صالح 24 ساعة فقط. للإنتاج استخدم <strong>System User Access Token</strong> دائم الصلاحية.
                        <a href="https://developers.facebook.com/docs/whatsapp/business-management-api/get-started" target="_blank" class="alert-link">دليل الإعداد</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== الإعدادات العامة ===== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-globe me-2 text-secondary"></i>إعدادات عامة
            </div>
            <div class="card-body">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">كود الدولة الافتراضي</label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="text" name="country_code" class="form-control"
                            value="{{ old('country_code', $settings->country_code ?? '970') }}"
                            placeholder="970" maxlength="5">
                    </div>
                    <div class="form-text">يُطبَّق على الأرقام التي تبدأ بـ 0 أو بدون كود</div>
                </div>
            </div>
        </div>

        {{-- ===== قالب الرسالة ===== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-comment-alt me-2 text-secondary"></i>قالب رسالة الراتب
            </div>
            <div class="card-body">
                <label class="form-label fw-semibold">نص الرسالة الافتراضي</label>
                <textarea name="salary_template" class="form-control" rows="3"
                    maxlength="500">{{ old('salary_template', $settings->salary_template) }}</textarea>
                <div class="form-text mt-2">
                    المتغيرات المتاحة:
                    <code onclick="insertVar('{name}')"    class="badge bg-secondary me-1" style="cursor:pointer">{name}</code> اسم الموظف
                    <code onclick="insertVar('{amount}')"  class="badge bg-secondary me-1" style="cursor:pointer">{amount}</code> الصافي
                    <code onclick="insertVar('{period}')"  class="badge bg-secondary me-1" style="cursor:pointer">{period}</code> الفترة
                    — اضغط على الـ badge لإدراجه
                </div>
                <div class="mt-2 p-2 rounded bg-light small text-muted" id="template_preview">
                    <strong>معاينة:</strong> <span id="preview_text"></span>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i>حفظ الإعدادات
            </button>
        </div>
    </form>

    {{-- ===== اختبار الإرسال ===== --}}
    <div class="card shadow-sm border-warning mb-5">
        <div class="card-header bg-warning py-2 fw-semibold">
            <i class="fas fa-vial me-2"></i>اختبار الإرسال
        </div>
        <div class="card-body">
            <form action="{{ route('settings.messenger.test') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">رقم الهاتف للاختبار</label>
                        <input type="text" name="test_phone" class="form-control"
                            placeholder="0599123456" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">نص الرسالة التجريبية</label>
                        <input type="text" name="test_message" class="form-control"
                            value="رسالة تجريبية من نظام HR" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100"
                            {{ $settings->is_active ? '' : 'disabled' }}
                            title="{{ $settings->is_active ? '' : 'فعّل الإرسال أولاً' }}">
                            <i class="fas fa-paper-plane me-1"></i>إرسال
                        </button>
                    </div>
                </div>
                @if(!$settings->is_active)
                    <div class="small text-danger mt-2">
                        <i class="fas fa-lock me-1"></i>الإرسال معطّل — فعّله من الأعلى ثم احفظ الإعدادات.
                    </div>
                @endif
            </form>
        </div>
    </div>

</div>

<script>
function switchDriver() {
    const isSms = document.getElementById('driver_sms').checked;
    document.getElementById('section_bulksms').classList.toggle('d-none', !isSms);
    document.getElementById('section_whatsapp').classList.toggle('d-none', isSms);

    document.querySelectorAll('.driver-option').forEach(el => {
        el.classList.remove('border-primary', 'border-success', 'bg-light');
    });
    const active = document.querySelector('.driver-option:has(input:checked)');
    if (active) {
        active.classList.add(isSms ? 'border-primary' : 'border-success', 'bg-light');
    }
}

function selectDriver(val) {
    document.getElementById(val === 'bulksms' ? 'driver_sms' : 'driver_wa').checked = true;
    switchDriver();
}

function togglePass(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

// معاينة القالب
const tmplArea = document.querySelector('[name="salary_template"]');
function updatePreview() {
    const t = tmplArea.value
        .replace('{name}',   'محمد أحمد')
        .replace('{amount}', '1850')
        .replace('{period}', '2026-W18');
    document.getElementById('preview_text').textContent = t;
}
tmplArea.addEventListener('input', updatePreview);
updatePreview();

function insertVar(v) {
    const pos = tmplArea.selectionStart;
    tmplArea.value = tmplArea.value.slice(0, pos) + v + tmplArea.value.slice(pos);
    tmplArea.focus();
    updatePreview();
}

// تغيير label الـ switch
document.getElementById('is_active').addEventListener('change', function() {
    this.nextElementSibling.textContent = this.checked ? 'مفعّل' : 'معطّل';
});
</script>
</x-app-layout>
