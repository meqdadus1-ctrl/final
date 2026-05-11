<x-app-layout>
    <x-slot name="title">التقويم</x-slot>

    <style>
        #calendar { direction: ltr; }
        .fc { font-family: 'Segoe UI', Tahoma, sans-serif; }
        .fc .fc-toolbar-title { font-size: 1.2rem; font-weight: 700; color: #1e3a5f; }
        .fc .fc-button-primary { background: #1e3a5f; border-color: #1e3a5f; }
        .fc .fc-button-primary:hover { background: #2d5a8e; border-color: #2d5a8e; }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active { background: #2d5a8e; border-color: #2d5a8e; }
        .fc-event { cursor: pointer; font-size: 12px; border-radius: 4px; }
        .fc-daygrid-event { padding: 2px 4px; }
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    </style>

    {{-- Top Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge" style="background:#6c757d;">
                <span class="legend-dot me-1" style="background:#6c757d;"></span> مهام انتظار
            </span>
            <span class="badge" style="background:#1e3a5f;">
                <span class="legend-dot me-1" style="background:#1e3a5f;"></span> مهام جارية
            </span>
            <span class="badge bg-success">
                <span class="legend-dot me-1" style="background:#198754;"></span> مهام مكتملة
            </span>
            <span class="badge bg-danger">
                <span class="legend-dot me-1" style="background:#dc3545;"></span> متأخرة
            </span>
        </div>
        <div class="d-flex gap-2">
            @can('tasks.create')
            <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-tasks me-1"></i> مهمة جديدة
            </a>
            <a href="{{ route('events.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-calendar-plus me-1"></i> حدث جديد
            </a>
            @endcan
        </div>
    </div>

    {{-- Calendar --}}
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div id="calendar"></div>
        </div>
    </div>

    {{-- Event Detail Modal --}}
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">—</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody"></div>
                </div>
                <div class="modal-footer" id="modalFooter"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const canEdit   = {{ auth()->user()->can('tasks.edit') ? 'true' : 'false' }};

        const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            locale: 'ar',
            direction: 'rtl',
            height: 'auto',
            headerToolbar: {
                start:  'prev,next today',
                center: 'title',
                end:    'dayGridMonth,timeGridWeek,timeGridDay',
            },
            buttonText: {
                today: 'اليوم',
                month: 'شهر',
                week:  'أسبوع',
                day:   'يوم',
            },
            editable: canEdit,
            droppable: false,
            events: {
                url: '{{ route("calendar.feed") }}',
                method: 'GET',
                failure: function () {
                    console.error('فشل تحميل بيانات التقويم');
                },
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                const props = info.event.extendedProps;
                const id    = info.event.id;

                document.getElementById('modalTitle').textContent = info.event.title.replace(/^📋\s*/, '');

                let body   = '';
                let footer = '';

                if (props.type === 'task') {
                    body += `<p class="mb-1"><strong>الأولوية:</strong> ${props.priority}</p>`;
                    body += `<p class="mb-1"><strong>الحالة:</strong> ${props.status}</p>`;
                    body += `<p class="mb-1"><strong>تاريخ الاستحقاق:</strong> ${info.event.startStr}</p>`;
                    footer = `<a href="/tasks/${props.task_id}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> عرض المهمة
                              </a>`;
                } else {
                    if (info.event.start) {
                        body += `<p class="mb-1"><strong>يبدأ:</strong> ${info.event.startStr}</p>`;
                    }
                    if (info.event.end) {
                        body += `<p class="mb-1"><strong>ينتهي:</strong> ${info.event.endStr}</p>`;
                    }
                    if (canEdit) {
                        footer = `<a href="/events/${props.event_id}/edit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i> تعديل
                                  </a>
                                  <button onclick="deleteEvent(${props.event_id})" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i> حذف
                                  </button>`;
                    }
                }

                document.getElementById('modalBody').innerHTML   = body   || '<p class="text-muted">لا توجد تفاصيل</p>';
                document.getElementById('modalFooter').innerHTML = footer;

                new bootstrap.Modal(document.getElementById('eventModal')).show();
            },
            eventDrop: function (info) {
                if (!canEdit) { info.revert(); return; }

                const id  = info.event.id;
                const start = info.event.startStr;

                if (id.startsWith('task-')) {
                    const taskId = id.replace('task-', '');
                    fetch(`/tasks/${taskId}/due-date`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ due_date: start.split('T')[0] }),
                    }).catch(() => info.revert());

                } else if (id.startsWith('event-')) {
                    const eventId = id.replace('event-', '');
                    fetch(`/events/${eventId}/dates`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            start_date: start,
                            end_date:   info.event.endStr || null,
                        }),
                    }).catch(() => info.revert());
                }
            },
            eventResize: function (info) {
                if (!canEdit || !info.event.id.startsWith('event-')) {
                    info.revert();
                    return;
                }
                const eventId = info.event.id.replace('event-', '');
                fetch(`/events/${eventId}/dates`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        start_date: info.event.startStr,
                        end_date:   info.event.endStr,
                    }),
                }).catch(() => info.revert());
            },
        });

        calendar.render();

        window.deleteEvent = function (eventId) {
            if (!confirm('هل أنت متأكد من حذف هذا الحدث؟')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/events/${eventId}`;
            form.innerHTML = `<input name="_token" value="${csrfToken}">
                              <input name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        };
    });
    </script>
</x-app-layout>
