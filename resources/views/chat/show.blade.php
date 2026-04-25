<x-app-layout>
<x-slot name="title">محادثة — {{ $employee->name }}</x-slot>

<style>
/* ===== Chat Layout ===== */
.chat-wrapper {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 120px);
}
.chat-header {
    background: #fff;
    border-radius: 14px 14px 0 0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #f0f0f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.chat-header .avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg,#1e3a5f,#2d6abf);
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.chat-header .info .name { font-weight: 700; font-size: 15px; }
.chat-header .info .sub  { font-size: 12px; color: #888; }
.typing-indicator { font-size: 12px; color: #2d6abf; display: none; }

/* ===== Messages Area ===== */
#chatBox {
    flex: 1;
    overflow-y: auto;
    padding: 20px 16px;
    background: #eef2f7;
    scroll-behavior: smooth;
}
#chatBox::-webkit-scrollbar { width: 5px; }
#chatBox::-webkit-scrollbar-thumb { background: #c0cfe0; border-radius: 4px; }

/* ===== Bubble ===== */
.msg-row {
    display: flex;
    margin-bottom: 14px;
    animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
.msg-row.admin  { flex-direction: row; }
.msg-row.emp    { flex-direction: row-reverse; }

.bubble-wrap { max-width: 65%; }
.bubble {
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
}
.msg-row.admin .bubble {
    background: #fff;
    color: #222;
    border-radius: 4px 18px 18px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.msg-row.emp .bubble {
    background: linear-gradient(135deg,#1e3a5f,#2563a8);
    color: #fff;
    border-radius: 18px 4px 18px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.bubble img {
    max-width: 260px; max-height: 200px;
    border-radius: 10px; display: block;
    cursor: zoom-in;
    transition: opacity .2s;
}
.bubble img:hover { opacity: .9; }
.doc-card {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none;
    background: rgba(255,255,255,.15);
    border-radius: 10px;
    padding: 8px 12px;
}
.msg-row.admin .doc-card { background: #f4f7fb; color: #1e3a5f; }
.msg-row.emp   .doc-card { color: #fff; }
.doc-card:hover { opacity: .85; }

.meta {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.msg-row.emp .meta { justify-content: flex-end; color: rgba(255,255,255,.6); }
.read-tick { font-size: 13px; }
.read-tick.read { color: #4fc3f7; }

/* زر الحذف على hover */
.msg-row:hover .btn-del { opacity: 1; }
.btn-del {
    opacity: 0;
    background: none; border: none;
    color: #e74c3c;
    font-size: 13px;
    padding: 0 6px;
    transition: opacity .15s;
    cursor: pointer;
    align-self: center;
}

/* ===== Sender Bar ===== */
.chat-footer {
    background: #fff;
    border-radius: 0 0 14px 14px;
    padding: 12px 14px;
    border-top: 1px solid #f0f0f0;
    box-shadow: 0 -1px 4px rgba(0,0,0,0.05);
}
.send-bar {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}
#msgInput {
    flex: 1;
    border: 1.5px solid #dde3ec;
    border-radius: 22px;
    padding: 9px 16px;
    font-size: 14px;
    resize: none;
    max-height: 120px;
    overflow-y: auto;
    outline: none;
    transition: border-color .2s;
    font-family: inherit;
    line-height: 1.5;
}
#msgInput:focus { border-color: #2563a8; }
.btn-attach {
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 1.5px solid #dde3ec;
    background: #fff;
    color: #666;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all .2s;
    flex-shrink: 0;
}
.btn-attach:hover { border-color: #2563a8; color: #2563a8; }
.btn-send {
    width: 42px; height: 42px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg,#1e3a5f,#2563a8);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: transform .15s, opacity .15s;
    flex-shrink: 0;
}
.btn-send:hover { transform: scale(1.08); }
.btn-send:disabled { opacity: .5; cursor: default; transform: none; }

/* معاينة المرفق */
#attachPreview {
    display: none;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #f4f7fb;
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 13px;
    color: #444;
}
#attachPreview img { height: 40px; width: 40px; object-fit: cover; border-radius: 6px; }

/* ===== Lightbox ===== */
#lightbox {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
#lightbox.show { display: flex; }
#lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
#lightbox .close-lb {
    position: absolute; top: 20px; left: 20px;
    color: #fff; font-size: 28px; cursor: pointer;
    background: none; border: none;
}

/* ===== Date separator ===== */
.date-sep {
    text-align: center;
    margin: 12px 0;
    font-size: 11px;
    color: #999;
    position: relative;
}
.date-sep::before, .date-sep::after {
    content:''; position: absolute; top: 50%;
    width: 35%; height: 1px; background: #dde3ec;
}
.date-sep::before { right: 0; }
.date-sep::after  { left: 0; }
</style>

<div class="chat-wrapper" dir="rtl">

    {{-- Header --}}
    <div class="chat-header">
        <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary me-1">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div class="avatar">{{ mb_substr($employee->name, 0, 1) }}</div>
        <div class="info flex-fill">
            <div class="name">{{ $employee->name }}</div>
            <div class="sub">
                {{ $employee->job_title ?? $employee->department?->name ?? '—' }}
                &nbsp;·&nbsp;
                <span class="typing-indicator" id="typingIndicator">
                    <i class="fas fa-ellipsis-h"></i> يكتب...
                </span>
            </div>
        </div>
    </div>

    {{-- Messages --}}
    <div id="chatBox">
        @forelse($messages as $msg)
        @php $isAdmin = $msg->sender_type === 'admin'; @endphp
        <div class="msg-row {{ $isAdmin ? 'emp' : 'admin' }}" data-id="{{ $msg->id }}">
            @if($isAdmin)
            <button class="btn-del" onclick="deleteMsg({{ $msg->id }}, this)" title="حذف">
                <i class="fas fa-trash-alt"></i>
            </button>
            @endif
            <div class="bubble-wrap {{ $isAdmin ? 'ms-2' : 'me-2' }}">
                <div class="bubble">
                    @if($msg->attachment_type === 'image' && $msg->attachment_path)
                        <img src="{{ Storage::url($msg->attachment_path) }}"
                             onclick="openLightbox(this.src)" alt="صورة">
                        @if($msg->message)<div class="mt-2">{{ $msg->message }}</div>@endif
                    @elseif($msg->attachment_type === 'document' && $msg->attachment_path)
                        <a class="doc-card" href="{{ Storage::url($msg->attachment_path) }}" target="_blank">
                            <i class="fas fa-file-alt fa-lg"></i>
                            <div>
                                <div style="font-size:13px;font-weight:600">{{ $msg->attachment_name ?? 'مستند' }}</div>
                                <div style="font-size:11px;opacity:.7">اضغط للتحميل</div>
                            </div>
                        </a>
                        @if($msg->message)<div class="mt-2">{{ $msg->message }}</div>@endif
                    @else
                        {{ $msg->message }}
                    @endif
                </div>
                <div class="meta">
                    <span>{{ $msg->created_at->format('h:i A') }}</span>
                    @if($isAdmin)
                    <span class="read-tick {{ $msg->is_read ? 'read' : '' }}">
                        <i class="fas fa-check-double"></i>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5" id="emptyState">
            <i class="fas fa-comments fa-3x mb-3 d-block" style="opacity:.3"></i>
            ابدأ المحادثة مع {{ $employee->name }}
        </div>
        @endforelse
    </div>

    {{-- Footer --}}
    <div class="chat-footer">
        {{-- معاينة المرفق --}}
        <div id="attachPreview">
            <span id="previewThumb"></span>
            <span id="previewName" class="flex-fill"></span>
            <button onclick="clearAttach()" style="background:none;border:none;color:#e74c3c;font-size:16px">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="sendForm" enctype="multipart/form-data">
            @csrf
            <input type="file" id="attachInput" name="attachment" class="d-none"
                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
            <div class="send-bar">
                <button type="button" class="btn-attach" onclick="document.getElementById('attachInput').click()" title="إرفاق">
                    <i class="fas fa-paperclip"></i>
                </button>
                <textarea id="msgInput" name="message" rows="1"
                          placeholder="اكتب رسالة..."></textarea>
                <button type="submit" class="btn-send" id="btnSend" title="إرسال">
                    <i class="fas fa-paper-plane" style="font-size:15px;margin-right:2px"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox()">
    <button class="close-lb" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="">
</div>

{{-- نغمة إشعار --}}
<audio id="notifSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAA..." type="audio/wav">
</audio>

<script>
const EMPLOYEE_ID  = {{ $employee->id }};
const POLL_URL     = '{{ route('chat.poll', $employee) }}';
const SEND_URL     = '{{ route('chat.send', $employee) }}';
const DELETE_BASE  = '{{ url('chat/message') }}';
const CSRF         = document.querySelector('meta[name=csrf-token]')?.content
                  || '{{ csrf_token() }}';

let lastId     = {{ $messages->max('id') ?? 0 }};
let pollTimer  = null;
let sending    = false;

/* ========== Scroll ========== */
const chatBox = document.getElementById('chatBox');
function scrollBottom(smooth = false) {
    chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: smooth ? 'smooth' : 'instant' });
}
scrollBottom();

/* ========== Auto-resize textarea ========== */
const msgInput = document.getElementById('msgInput');
msgInput.addEventListener('input', () => {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 120) + 'px';
});

/* ========== Enter to send ========== */
msgInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('sendForm').dispatchEvent(new Event('submit', {bubbles:true}));
    }
});

/* ========== Attach preview ========== */
document.getElementById('attachInput').addEventListener('change', function() {
    if (!this.files[0]) return;
    const file = this.files[0];
    const preview = document.getElementById('attachPreview');
    const thumb   = document.getElementById('previewThumb');
    const name    = document.getElementById('previewName');

    name.textContent = file.name;
    preview.style.display = 'flex';

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            thumb.innerHTML = `<img src="${e.target.result}">`;
        };
        reader.readAsDataURL(file);
    } else {
        thumb.innerHTML = '<i class="fas fa-file-alt fa-lg text-primary"></i>';
    }
});

function clearAttach() {
    document.getElementById('attachInput').value = '';
    document.getElementById('attachPreview').style.display = 'none';
    document.getElementById('previewThumb').innerHTML = '';
    document.getElementById('previewName').textContent = '';
}

/* ========== Send ========== */
document.getElementById('sendForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (sending) return;

    const msg  = msgInput.value.trim();
    const file = document.getElementById('attachInput').files[0];
    if (!msg && !file) return;

    sending = true;
    const btn = document.getElementById('btnSend');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:14px"></i>';

    const fd = new FormData(this);
    fd.append('_method', 'POST');

    try {
        const res = await fetch(SEND_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });

        if (res.ok) {
            msgInput.value = '';
            msgInput.style.height = 'auto';
            clearAttach();
            // رسالة مؤقتة — ستظهر بعد الـ poll
            await pollMessages();
        }
    } catch(err) {
        console.error(err);
    } finally {
        sending = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane" style="font-size:15px;margin-right:2px"></i>';
    }
});

/* ========== Render bubble ========== */
function renderBubble(m) {
    const isAdmin = m.sender_type === 'admin';
    const rowClass = isAdmin ? 'emp' : 'admin';

    let content = '';
    if (m.attachment_type === 'image' && m.attachment_url) {
        content += `<img src="${m.attachment_url}" onclick="openLightbox(this.src)" alt="صورة">`;
        if (m.message) content += `<div class="mt-2">${escHtml(m.message)}</div>`;
    } else if (m.attachment_type === 'document' && m.attachment_url) {
        content += `<a class="doc-card" href="${m.attachment_url}" target="_blank">
            <i class="fas fa-file-alt fa-lg"></i>
            <div>
                <div style="font-size:13px;font-weight:600">${escHtml(m.attachment_name ?? 'مستند')}</div>
                <div style="font-size:11px;opacity:.7">اضغط للتحميل</div>
            </div>
        </a>`;
        if (m.message) content += `<div class="mt-2">${escHtml(m.message)}</div>`;
    } else {
        content = escHtml(m.message ?? '');
    }

    const delBtn = isAdmin
        ? `<button class="btn-del" onclick="deleteMsg(${m.id}, this)" title="حذف"><i class="fas fa-trash-alt"></i></button>`
        : '';

    const tick = isAdmin
        ? `<span class="read-tick ${m.is_read ? 'read' : ''}"><i class="fas fa-check-double"></i></span>`
        : '';

    return `
    <div class="msg-row ${rowClass}" data-id="${m.id}">
        ${delBtn}
        <div class="bubble-wrap ${isAdmin ? 'ms-2' : 'me-2'}">
            <div class="bubble">${content}</div>
            <div class="meta">
                <span>${m.time}</span>
                ${tick}
            </div>
        </div>
    </div>`;
}

/* ========== Polling ========== */
async function pollMessages() {
    try {
        const res  = await fetch(`${POLL_URL}?after=${lastId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        if (data.messages && data.messages.length > 0) {
            const empty = document.getElementById('emptyState');
            if (empty) empty.remove();

            // هل المستخدم في الأسفل؟
            const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 60;

            data.messages.forEach(m => {
                // تجنب تكرار الرسائل
                if (document.querySelector(`.msg-row[data-id="${m.id}"]`)) return;
                chatBox.insertAdjacentHTML('beforeend', renderBubble(m));
                lastId = Math.max(lastId, m.id);
            });

            // إشعار صوتي إذا الرسالة من الموظف
            const hasNew = data.messages.some(m => m.sender_type !== 'admin');
            if (hasNew) playNotif();

            if (atBottom) scrollBottom(true);
        }

        // تحديث علامة القراءة للرسائل القديمة
        updateReadTicks();

    } catch(err) {
        // تجاهل أخطاء الشبكة
    }
}

function updateReadTicks() {
    document.querySelectorAll('.msg-row.emp .read-tick').forEach(el => {
        el.classList.add('read');
    });
}

/* بدء الـ polling كل 3 ثواني */
pollTimer = setInterval(pollMessages, 3000);

/* ========== Delete ========== */
async function deleteMsg(id, btn) {
    if (!confirm('حذف هذه الرسالة؟')) return;
    try {
        const res = await fetch(`${DELETE_BASE}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        if (res.ok) {
            btn.closest('.msg-row').style.transition = 'opacity .2s';
            btn.closest('.msg-row').style.opacity = '0';
            setTimeout(() => btn.closest('.msg-row').remove(), 200);
        }
    } catch(err) {}
}

/* ========== Lightbox ========== */
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('show');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('show');
    document.getElementById('lightboxImg').src = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

/* ========== Sound ========== */
function playNotif() {
    try {
        // نغمة بسيطة عبر Web Audio API
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

/* ========== Helper ========== */
function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;').replace(/\n/g,'<br>');
}
</script>
</x-app-layout>
