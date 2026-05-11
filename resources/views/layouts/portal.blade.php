<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'بوابة الموظف' }} — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; padding: 0; }

        /* ── Top Bar ── */
        .portal-topbar {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
            padding: 0 24px;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .portal-topbar .brand {
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .portal-topbar .brand .brand-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .portal-topbar .user-info {
            display: flex; align-items: center; gap: 12px;
        }
        .portal-topbar .user-avatar {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px;
        }
        .portal-topbar .user-name { color: #e8f0fe; font-size: 14px; }

        /* ── Nav Tabs ── */
        .portal-nav {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            display: flex;
            gap: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .portal-nav::-webkit-scrollbar { display: none; }
        .portal-nav a {
            display: flex; align-items: center; gap: 7px;
            padding: 14px 18px;
            color: #64748b;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .portal-nav a:hover { color: #1e3a5f; background: #f8fafc; }
        .portal-nav a.active { color: #1e3a5f; border-bottom-color: #1e3a5f; font-weight: 700; }
        .portal-nav a i { font-size: 15px; }

        /* ── Main Content ── */
        .portal-body { padding: 24px; max-width: 1200px; margin: 0 auto; }

        /* ── Cards ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 14px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }
        .btn-primary { background: #1e3a5f; border-color: #1e3a5f; }
        .btn-primary:hover { background: #2d5a8e; border-color: #2d5a8e; }
        thead.table-dark th, .table-dark th { background: #000 !important; color: #fff !important; }

        /* ── Stat Cards ── */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-value { font-size: 22px; font-weight: 800; line-height: 1; }
        .stat-label { font-size: 12px; color: #94a3b8; margin-top: 3px; }

        @media (max-width: 768px) {
            .portal-body { padding: 16px; }
            .portal-nav a { padding: 12px 12px; font-size: 12px; }
            .portal-nav a span.nav-label { display: none; }
        }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <div class="portal-topbar">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-building text-white"></i></div>
            <div>
                <div>{{ config('app.name') }}</div>
                <div style="font-size:11px; opacity:.6; font-weight:400;">بوابة الموظف</div>
            </div>
        </div>
        <div class="user-info">
            <div class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <span class="user-name d-none d-sm-inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm py-1 px-2" style="font-size:12px;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="d-none d-sm-inline ms-1">خروج</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="portal-nav">
        <a href="{{ route('portal.index') }}"
           class="{{ request()->routeIs('portal.index') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span class="nav-label">الرئيسية</span>
        </a>
        <a href="{{ route('portal.attendance') }}"
           class="{{ request()->routeIs('portal.attendance') ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            <span class="nav-label">الحضور</span>
        </a>
        <a href="{{ route('portal.payslips') }}"
           class="{{ request()->routeIs('portal.payslips') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i>
            <span class="nav-label">الرواتب</span>
        </a>
        <a href="{{ route('portal.leaves') }}"
           class="{{ request()->routeIs('portal.leaves') ? 'active' : '' }}">
            <i class="fas fa-umbrella-beach"></i>
            <span class="nav-label">الإجازات</span>
        </a>
        <a href="{{ route('portal.loans') }}"
           class="{{ request()->routeIs('portal.loans') ? 'active' : '' }}">
            <i class="fas fa-hand-holding-usd"></i>
            <span class="nav-label">السلف</span>
        </a>
        <a href="{{ route('portal.tasks') }}"
           class="{{ request()->routeIs('portal.tasks') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span class="nav-label">مهامي</span>
        </a>
    </div>

    {{-- Alerts --}}
    <div class="portal-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show mb-4">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
