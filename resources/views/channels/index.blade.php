<x-app-layout>
<x-slot name="title">قنوات الأقسام</x-slot>

<style>
.channel-card {
    border-radius: 14px;
    border: 2px solid transparent;
    transition: all .2s;
    cursor: pointer;
    text-decoration: none;
    display: block;
}
.channel-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important; }
.channel-card.has-unread { border-color: #198754; }
.channel-avatar {
    width: 50px; height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg,#198754,#20c997);
    color: #fff;
    font-size: 22px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    position: relative;
}
.unread-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: #dc3545;
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
.last-msg.unread { color: #198754; font-weight: 600; }
</style>

<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-broadcast-tower text-success me-2"></i>
                قنوات الأقسام
            </h4>
            <small class="text-muted">رسائل القسم تصل لجميع أعضائه — تحديث كل 5 ثوانٍ</small>
        </div>
        <span id="totalBadge" class="badge bg-danger fs-6 {{ $totalUnread > 0 ? '' : 'd-none' }}">
            {{ $totalUnread }} غير مقروءة
        </span>
    </div>

    @if($departments->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-building fa-3x mb-3 d-block" style="opacity:.3"></i>
            لا توجد أقسام بعد
        </div>
    @else
    <div class="row g-3" id="deptList">
        @foreach($departments as $dept)
        <div class="col-md-4 col-lg-3" id="dept-col-{{ $dept->id }}">
            <a href="{{ route('channels.show', $dept) }}"
               class="channel-card card shadow-sm h-100 {{ $dept->unread_count > 0 ? 'has-unread' : '' }}">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="channel-avatar">
                        <i class="fas fa-hashtag"></i>
                        @if($dept->unread_count > 0)
                        <span class="unread-badge" id="badge-{{ $dept->id }}">{{ $dept->unread_count }}</span>
                        @else
                        <span class="unread-badge d-none" id="badge-{{ $dept->id }}">0</span>
                        @endif
                    </div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-bold text-dark"># {{ $dept->name }}</div>
                        <div class="small text-muted mb-1">{{ $dept->employees_count }} موظف</div>
                        @if($dept->last_message)
                        <div class="last-msg {{ $dept->unread_count > 0 ? 'unread' : '' }}" id="lastmsg-{{ $dept->id }}">
                            {{ $dept->last_message->message ?? '📎 مرفق' }}
                        </div>
                        @else
                        <div class="last-msg text-muted" id="lastmsg-{{ $dept->id }}">لا توجد رسائل بعد</div>
                        @endif
                    </div>
                    @if($dept->last_message)
                    <div class="small text-muted" style="white-space:nowrap">
                        {{ $dept->last_message->created_at->diffForHumans() }}
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
const POLL_LIST_URL = '{{ route('channels.poll.list') }}';
const CSRF = '{{ csrf_token() }}';

async function pollList() {
    try {
        const res  = await fetch(POLL_LIST_URL, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();

        const totalBadge = document.getElementById('totalBadge');
        if (data.total > 0) {
            totalBadge.textContent = data.total + ' غير مقروءة';
            totalBadge.classList.remove('d-none');
        } else {
            totalBadge.classList.add('d-none');
        }

        for (const [deptId, cnt] of Object.entries(data.byDept ?? {})) {
            const badge = document.getElementById(`badge-${deptId}`);
            const card  = document.querySelector(`#dept-col-${deptId} .channel-card`);
            if (badge) { badge.textContent = cnt; badge.classList.remove('d-none'); }
            if (card)  { card.classList.add('has-unread'); }
        }
    } catch(e) {}
}

setInterval(pollList, 5000);
</script>
</x-app-layout>
