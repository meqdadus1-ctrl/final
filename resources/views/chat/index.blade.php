<x-app-layout>
<x-slot name="title">محادثات الموظفين</x-slot>

<style>
.chat-card {
    border-radius: 14px;
    border: 2px solid transparent;
    transition: all .2s;
    cursor: pointer;
    text-decoration: none;
    display: block;
}
.chat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important; }
.chat-card.has-unread { border-color: #2563a8; }
.chat-avatar {
    width: 50px; height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg,#1e3a5f,#2d6abf);
    color: #fff;
    font-size: 19px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    position: relative;
}
.unread-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: #e74c3c;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px; height: 18px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px;
    border: 2px solid #fff;
}
.last-msg {
    font-size: 13px;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}
.last-msg.unread { color: #1e3a5f; font-weight: 600; }
.msg-time { font-size: 11px; color: #aaa; white-space: nowrap; }
</style>

<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-comments text-primary me-2"></i>
                محادثات الموظفين
            </h4>
            <small class="text-muted">تحديث تلقائي كل 5 ثوانٍ</small>
        </div>
        <span id="totalBadge" class="badge bg-danger fs-6 {{ $totalUnread > 0 ? '' : 'd-none' }}">
            {{ $totalUnread }} غير مقروءة
        </span>
    </div>

    @if($employees->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-comments fa-3x mb-3 d-block" style="opacity:.3"></i>
            لا يوجد موظفون نشطون
        </div>
    @else
    <div class="row g-3" id="empList">
        @foreach($employees as $emp)
        <div class="col-md-4 col-lg-3" id="emp-col-{{ $emp->id }}">
            <a href="{{ route('chat.show', $emp) }}"
               class="chat-card card shadow-sm h-100 {{ $emp->unread_count > 0 ? 'has-unread' : '' }}">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="chat-avatar">
                        {{ mb_substr($emp->name, 0, 1) }}
                        @if($emp->unread_count > 0)
                        <span class="unread-badge" id="badge-{{ $emp->id }}">{{ $emp->unread_count }}</span>
                        @else
                        <span class="unread-badge d-none" id="badge-{{ $emp->id }}">0</span>
                        @endif
                    </div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-bold text-dark" style="font-size:14px">{{ $emp->name }}</div>
                        <div class="small text-muted mb-1">{{ $emp->job_title ?? '—' }}</div>
                        @if($emp->last_message)
                        <div class="last-msg {{ $emp->unread_count > 0 ? 'unread' : '' }}" id="lastmsg-{{ $emp->id }}">
                            {{ $emp->last_message->sender_type === 'admin' ? '↩ أنت: ' : '' }}{{ $emp->last_message->message ?? '📎 مرفق' }}
                        </div>
                        @else
                        <div class="last-msg text-muted" id="lastmsg-{{ $emp->id }}">لا توجد رسائل بعد</div>
                        @endif
                    </div>
                    @if($emp->last_message)
                    <div class="msg-time" id="time-{{ $emp->id }}">
                        {{ $emp->last_message->created_at->diffForHumans() }}
                    </div>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif

</div>

<script>
const POLL_LIST_URL = '{{ route('chat.poll.list') }}';
const CSRF = '{{ csrf_token() }}';

async function pollList() {
    try {
        const res  = await fetch(POLL_LIST_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        // تحديث الـ badge الكلي
        const totalBadge = document.getElementById('totalBadge');
        if (data.total > 0) {
            totalBadge.textContent = data.total + ' غير مقروءة';
            totalBadge.classList.remove('d-none');
        } else {
            totalBadge.classList.add('d-none');
        }

        // تحديث كل موظف
        for (const [empId, cnt] of Object.entries(data.byEmp ?? {})) {
            const badge = document.getElementById(`badge-${empId}`);
            const card  = document.querySelector(`#emp-col-${empId} .chat-card`);
            const lastMsg = document.getElementById(`lastmsg-${empId}`);

            if (badge) {
                badge.textContent = cnt;
                badge.classList.remove('d-none');
            }
            if (card) card.classList.add('has-unread');
            if (lastMsg) lastMsg.classList.add('unread');
        }
    } catch(e) {}
}

// كل 5 ثوانٍ
setInterval(pollList, 5000);
</script>
</x-app-layout>
