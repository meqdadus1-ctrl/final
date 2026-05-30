<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - نظام الموارد البشرية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --fox-brown:       #3b1a08;
            --fox-brown-mid:   #5c2d0e;
            --fox-orange:      #f47c20;
            --fox-orange-dark: #c85f0a;
        }

        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; }

        /* ── Sidebar ── */
        .sidebar {
            width: 250px; height: 100vh;
            background: var(--fox-brown);
            position: fixed; right: 0; top: 0;
            overflow-y: auto; overflow-x: hidden;
            display: flex; flex-direction: column;
        }

        .sidebar-logo {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; justify-content: center;
        }

        .sidebar-logo img {
            width: 150px;
            height: auto;
            display: block;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,.4));
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.6);
            padding: 11px 20px;
            display: flex; align-items: center; gap: 10px;
            transition: background .18s, color .18s;
            font-size: 14px;
        }

        .sidebar .nav-link:hover {
            background: rgba(244,124,32,.15);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--fox-orange);
            color: #fff;
        }

        .sidebar .nav-link i { width: 18px; text-align: center; flex-shrink: 0; }

        .sidebar .nav-section {
            color: rgba(255,255,255,.3);
            font-size: 10.5px;
            padding: 14px 20px 4px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        /* ── Main ── */
        .main-content { margin-right: 250px; padding: 20px; min-height: 100vh; }

        .topbar {
            background: #fff; padding: 12px 20px; border-radius: 10px;
            margin-bottom: 20px; display: flex;
            justify-content: space-between; align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            border-right: 3px solid var(--fox-orange);
        }

        .topbar .fw-bold { color: var(--fox-brown); }

        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0 !important; }

        .btn-primary { background: var(--fox-orange); border-color: var(--fox-orange); }
        .btn-primary:hover, .btn-primary:focus { background: var(--fox-orange-dark); border-color: var(--fox-orange-dark); }

        .table th { background: #f8f9fa; font-weight: 600; font-size: 13px; }
        thead.table-dark th, .table-dark th { background: var(--fox-brown) !important; color: #fff !important; }

        /* ── Sidebar Overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1040;
        }
        .sidebar-overlay.show { display: block; }

        /* ── Mobile Toggle Button ── */
        .sidebar-toggle {
            display: none;
            background: none; border: none;
            color: var(--fox-brown); font-size: 22px;
            padding: 0 8px 0 0; cursor: pointer;
            line-height: 1;
        }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
                transition: transform .28s ease;
                z-index: 1045;
            }
            .sidebar.sidebar-open { transform: translateX(0); }
            .main-content { margin-right: 0 !important; }
            .sidebar-toggle { display: inline-flex; align-items: center; }
            /* Make card-body tables scrollable */
            .card-body { overflow-x: auto; }
        }

        /* Smooth sidebar on desktop too */
        @media (min-width: 992px) {
            .sidebar { transition: none; }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="mainSidebar">
        <div class="sidebar-logo">
            <a href="{{ route('dashboard') }}">
                <img src="/fox.png" alt="Fox Plus HRM" />
            </a>
        </div>
        <nav class="mt-2">
            <div class="nav-section">الرئيسية</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> لوحة التحكم
            </a>

            <div class="nav-section">الموظفون</div>
            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> الموظفون
            </a>
            <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i> الأقسام
            </a>
            <a href="{{ route('banks.index') }}" class="nav-link {{ request()->routeIs('banks.*') ? 'active' : '' }}">
                <i class="fas fa-university"></i> البنوك
            </a>

            <div class="nav-section">الحضور والرواتب</div>
            <a href="{{ route('attendance.index') }}"
                class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i> الحضور والانصراف
            </a>
            <a href="{{ route('salary.index') }}" class="nav-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i> كشف الرواتب
            </a>
            <a href="{{ route('ledger.index') }}" class="nav-link {{ request()->routeIs('ledger.index') || (request()->routeIs('ledger.*') && !request()->routeIs('ledger.journal')) ? 'active' : '' }}">
                <i class="fas fa-book-open"></i> كشف الحسابات
            </a>
            <a href="{{ route('ledger.journal') }}" class="nav-link {{ request()->routeIs('ledger.journal') ? 'active' : '' }}">
                <i class="fas fa-list-alt"></i> القيود المحاسبية
            </a>
            <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i> السلف
            </a>
            <a href="{{ route('leaves.index') }}" class="nav-link {{ request()->routeIs('leave.*') ? 'active' : '' }}">
                <i class="fas fa-umbrella-beach"></i> الإجازات
            </a>
<a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
    <i class="fas fa-briefcase"></i> طلبات التوظيف
</a>

            <div class="nav-section">إدارة المهام</div>
            <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> المهام
            </a>
            <a href="{{ route('tasks.kanban') }}" class="nav-link {{ request()->routeIs('tasks.kanban') ? 'active' : '' }}">
                <i class="fas fa-columns"></i> لوحة كانبان
            </a>
            <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> التقويم
            </a>

            <div class="nav-section">الأداء والتقارير</div>
            <a href="{{ route('performance.index') }}" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i> تقييم الأداء
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> تقارير الموظفين
            </a>

<a href="{{ route('roles.index') }}" class="nav-link">
    <i class="fas fa-user-shield"></i> الصلاحيات
</a>
<a href="{{ route('mobile.index') }}" class="nav-link {{ request()->routeIs('mobile.*') ? 'active' : '' }}">
    <i class="fas fa-mobile-alt"></i> لوحة تحكم التطبيق
</a>
<a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> محادثات الموظفين
    @php
        $chatUnread = \App\Models\Chat::where('sender_type','employee')->where('is_read',false)->count();
    @endphp
    @if($chatUnread > 0)
        <span class="badge bg-danger ms-auto">{{ $chatUnread }}</span>
    @endif
</a>
<a href="{{ route('channels.index') }}" class="nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}">
    <i class="fas fa-broadcast-tower"></i> قنوات الأقسام
    @php
        $userId = auth()->id();
        $reads  = \Illuminate\Support\Facades\DB::table('department_message_reads')->where('user_id',$userId)->pluck('last_read_id','department_id');
        $channelUnread = \App\Models\DepartmentMessage::where(function($q) use ($reads) {
            foreach($reads as $d => $l) { $q->orWhere(fn($s) => $s->where('department_id',$d)->where('id','>',$l)); }
            $q->orWhereNotIn('department_id', array_keys($reads->toArray()));
        })->count();
    @endphp
    @if($channelUnread > 0)
        <span class="badge bg-success ms-auto">{{ $channelUnread }}</span>
    @endif
</a>
            @role('admin')
            <div class="nav-section">النظام</div>
            <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i> سجل التدقيق
            </a>
            <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                <i class="fas fa-database"></i> النسخ الاحتياطية
            </a>
            @endrole

            <div class="nav-section">الحساب</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" class="nav-link" onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </form>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="القائمة">
                <i class="fas fa-bars"></i>
            </button>
            <span class="fw-bold">{{ $title ?? 'لوحة التحكم' }}</span>
            <span class="text-muted me-auto ms-3">مرحباً، {{ auth()->user()->name }}</span>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {!! session('info') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {!! session('warning') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="container-fluid px-0">
            {{ $slot }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var sidebar  = document.getElementById('mainSidebar');
            var overlay  = document.getElementById('sidebarOverlay');
            var toggle   = document.getElementById('sidebarToggle');

            function openSidebar()  { sidebar.classList.add('sidebar-open');    overlay.classList.add('show'); }
            function closeSidebar() { sidebar.classList.remove('sidebar-open'); overlay.classList.remove('show'); }

            toggle.addEventListener('click', function () {
                sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            });
            overlay.addEventListener('click', closeSidebar);

            // Close sidebar on nav-link click (mobile)
            sidebar.querySelectorAll('.nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) closeSidebar();
                });
            });
        })();
    </script>
</body>
</html>
