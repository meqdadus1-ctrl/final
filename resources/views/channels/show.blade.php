<x-app-layout>
<x-slot name="title">قناة {{ $department->name }}</x-slot>

<style>
html, body { height: 100%; }
.chat-wrap { display: flex; flex-direction: column; height: calc(100vh - 70px); }
.chat-body  { flex: 1; overflow-y: auto; padding: 16px; background: #f8f9fa; }
.chat-footer { background: #fff; border-top: 1px solid #dee2e6; padding: 10px 16px; }

.bubble-wrap { display: flex; margin-bottom: 10px; }
.bubble-wrap.mine  { justify-content: flex-end; }
.bubble-wrap.other { justify-content: flex-start; }

.bubble {
    max-width: 70%;
    padding: 8px 14px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
}
.bubble.mine  { background: #198754; color: #fff; border-bottom-right-radius: 4px; }
.bubble.other { background: #fff; color: #212529; border-bottom-left-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }

.sender-name { font-size: 11px; font-weight: 700; margin-bottom: 2px; opacity: .75; }
.bubble.mine .sender-name { text-align: right; }

.msg-time { font-size: 10px; opacity: .6; margin-top: 3px; display: block; }
.bubble.mine .msg-time { text-align: right; }

.bubble img { max-width: 200px; border-radius: 10px; display: block; margin-top: 4px; cursor: zoom-in; }
.doc-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; }
.doc-link i { font-size: 18px; }
.bubble.mine  .doc-link { color: rgba(255,255,255,.9); }
.bubble.other .doc-link { color: #0d6efd; }

.delete-btn { opacity: 0; transition: opacity .2s; background: none; border: none; padding: 2px 4px; }
.bubble-wrap:hover .delete-btn { opacity: 1; }

.channel-sidebar { width: 220px; flex-shrink: 0; border-left: 1px solid #dee2e6; background: #fff; overflow-y: auto; }
@media(max-width:768px){ .channel-sidebar { display: none; } }
</style>

<div class="d-flex chat-wrap" dir="rtl">

    {{-- ===== Sidebar: قائمة القنوات ===== --}}
    <div class="channel-sidebar p-0">
        <div class="p-3 border-bottom bg-success text-white">
            <div class="fw-bold small"><i class="fas fa-broadcast-tower me-1"></i>القنوات</div>
        </div>
        @foreach($departments as $d)
        <a href="{{ route('channels.show', $d) }}"
           class="d-block px-3 py-2 text-decoration-none border-bottom {{ $d->id === $department->id ? 'bg-success bg-opacity-10 fw-bold text-success' : 'text-dark' }}"
           style="font-size:13px">
            <i class="fas fa-hashtag me-1 opacity-50"></i>{{ $d->name }}
        </a>
        @endforeach
    </div>

    {{-- ===== Main ===== --}}
    <div class="flex-fill d-flex flex-column overflow-hidden">

        {{-- Header --}}
        <div class="d-flex align-items-center gap-3 px-4 py-2 bg-white border-bottom shadow-sm">
            <a href="{{ route('channels.index') }}" class="btn btn-sm btn-outline-secondary py-0">
                <i class="fas fa-arrow-right"></i>
            </a>
            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                 style="width:36px;height:36px;font-size:16px">
                <i class="fas fa-hashtag"></i>
            </div>
            <div>
                <div class="fw-bold"># {{ $department->name }}</div>
                <div class="small text-muted" id="memberCount">
                    {{ $department->employees()->count() }} عضو
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-body" id="chatBody">

            @if($messages->isEmpty())
            <div class="text-center text-muted py-5" id="emptyState">
                <i class="fas fa-comments fa-3x mb-3 d-block opacity-25"></i>
                لا توجد رسائل بعد — ابدأ المحادثة
            </div>
            @endif

            @foreach($messages as $msg)
            @php $mine = $msg->sender_id === auth()->id(); @endphp
            <div class="bubble-wrap {{ $mine ? 'mine' : 'other' }}" id="msg-{{ $msg->id }}">
                <div>
                    @if(!$mine)
                    <div class="sender-name text-success">{{ $msg->sender?->name ?? '؟' }}</div>
                    @endif
                    <div class="bubble {{ $mine ? 'mine' : 'other' }}">
                        @if($msg->message)
                            {{ $msg->message }}
                        @endif
                        @if($msg->attachment_type === 'image')
                            <img src="{{ $msg->attachment_url }}" alt="صورة"
                                 onclick="window.open(this.src,'_blank')">
                        @elseif($msg->attachment_type === 'document')
                            <a href="{{ $msg->attachment_url }}" target="_blank" class="doc-link">
                                <i class="fas fa-file-alt"></i>
                                {{ $msg->attachment_name }}
                            </a>
                        @endif
                        <span class="msg-time">{{ $msg->created_at->format('h:i A') }}</span>
                    </div>
                    @if($mine)
                    <div class="text-end mt-1">
                        <button class="delete-btn text-danger" onclick="deleteMsg({{ $msg->id }})" title="حذف">
                            <i class="fas fa-trash fa-xs"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Input --}}
        <div class="chat-footer">
            @if(session('error'))
            <div class="alert alert-danger py-1 small mb-2">{{ session('error') }}</div>
            @endif
            <form id="sendForm" action="{{ route('channels.send', $department) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-fill">
                        <textarea name="message" id="msgInput" rows="1"
                            class="form-control form-control-sm"
                            placeholder="اكتب رسالة لقناة #{{ $department->name }}..."
                            style="resize:none;border-radius:20px;padding:8px 14px"
                            onkeydown="handleKey(event)"></textarea>
                        <div id="filePreview" class="mt-1 small text-muted d-none"></div>
                    </div>
                    <label class="btn btn-outline-secondary btn-sm mb-0" title="إرفاق ملف">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="attachment" id="fileInput" class="d-none"
                               onchange="previewFile(this)">
                    </label>
                    <button type="submit" class="btn btn-success btn-sm" title="إرسال">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

{{-- Image lightbox --}}
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <img id="imgModalSrc" src="" class="img-fluid rounded">
        </div>
    </div>
</div>

<script>
const POLL_URL  = '{{ route('channels.poll', $department) }}';
const DEL_BASE  = '{{ url('channels/message') }}';
const CSRF      = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
let lastId      = {{ $messages->max('id') ?? 0 }};

/* ===== Scroll to bottom ===== */
function scrollBottom() {
    const body = document.getElementById('chatBody');
    body.scrollTop = body.scrollHeight;
}
scrollBottom();

/* ===== Append bubble ===== */
function appendMsg(m) {
    const empty = document.getElementById('emptyState');
    if (empty) empty.remove();

    const wrap = document.createElement('div');
    wrap.id = `msg-${m.id}`;
    wrap.className = `bubble-wrap ${m.is_mine ? 'mine' : 'other'}`;

    let inner = '';
    if (!m.is_mine) inner += `<div class="sender-name text-success">${m.sender_name}</div>`;
    inner += `<div class="bubble ${m.is_mine ? 'mine' : 'other'}">`;
    if (m.message)                               inner += `<span>${escHtml(m.message)}</span>`;
    if (m.attachment_type === 'image')           inner += `<img src="${m.attachment_url}" onclick="window.open(this.src,'_blank')" style="max-width:200px;border-radius:10px;display:block;margin-top:4px">`;
    if (m.attachment_type === 'document')        inner += `<a href="${m.attachment_url}" target="_blank" class="doc-link"><i class="fas fa-file-alt"></i>${escHtml(m.attachment_name)}</a>`;
    inner += `<span class="msg-time">${m.time}</span></div>`;
    if (m.is_mine) inner += `<div class="text-end mt-1"><button class="delete-btn text-danger" onclick="deleteMsg(${m.id})" title="حذف"><i class="fas fa-trash fa-xs"></i></button></div>`;

    wrap.innerHTML = inner;
    document.getElementById('chatBody').appendChild(wrap);
    scrollBottom();
}

/* ===== Poll ===== */
async function poll() {
    try {
        const res  = await fetch(`${POLL_URL}?after=${lastId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();
        (data.messages || []).forEach(m => { appendMsg(m); lastId = m.id; });
    } catch(e) {}
}
setInterval(poll, 4000);

/* ===== Delete ===== */
async function deleteMsg(id) {
    if (!confirm('حذف هذه الرسالة؟')) return;
    await fetch(`${DEL_BASE}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    document.getElementById(`msg-${id}`)?.remove();
}

/* ===== Helpers ===== */
function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('sendForm').requestSubmit();
    }
}

function previewFile(input) {
    const preview = document.getElementById('filePreview');
    if (input.files[0]) {
        preview.textContent = '📎 ' + input.files[0].name;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</x-app-layout>
