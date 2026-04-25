<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - نظام الموارد البشرية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .sidebar { width: 250px; min-height: 100vh; background: #1e3a5f; position: fixed; right: 0; top: 0; }
        .sidebar .logo { padding: 20px; text-align: center; border-bottom: 1px solid #2d5a8e; }
        .sidebar .logo h5 { color: #fff; margin: 0; font-size: 16px; }
        .sidebar .nav-link { color: #a8c4e0; padding: 12px 20px; display: flex; align-items: center; gap: 10px; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #2d5a8e; color: #fff; }
        .sidebar .nav-link i { width: 20px; text-align: center; }
        .sidebar .nav-section { color: #5a8ab0; font-size: 11px; padding: 15px 20px 5px; text-transform: uppercase; letter-spacing: 1px; }
        .main-content { margin-right: 250px; padding: 20px; }
        .topbar { background: #fff; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0 !important; }
        .btn-primary { background: #1e3a5f; border-color: #1e3a5f; }
        .btn-primary:hover { background: #2d5a8e; border-color: #2d5a8e; }
        .table th { background: #f8f9fa; font-weight: 600; font-size: 13px; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-building fa-2x text-white mb-2"></i>
            <h5>نظام الموارد البشرية</h5>
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
            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i> الحضور والانصراف
            </a>
            <a href="{{ route('salary.index') }}" class="nav-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i> الرواتب
            </a>
            @if(Route::has('payslips.index'))
            <a href="{{ route('payslips.index') }}" class="nav-link {{ request()->routeIs('payslips.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i> كشوف الرواتب
            </a>
            @endif
            <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i> السلف
            </a>
            <a href="{{ route('leaves.index') }}" class="nav-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                <i class="fas fa-umbrella-beach"></i> الإجازات
            </a>

            <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i> طلبات التوظيف
            </a>

            <div class="nav-section">بوابة الموظف</div>
            <a href="{{ route('portal.index') }}" class="nav-link {{ request()->routeIs('portal.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> بوابتي
            </a>

            @auth
                @if(auth()->user()->hasRole('admin'))
                <div class="nav-section">الإدارة</div>
                @if(Route::has('roles.index'))
                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i> الأدوار والصلاحيات
                </a>
                @endif
                @endif
            @endauth

            <div class="nav-section">الحساب</div>
            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i> الملف الشخصي
            </a>
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
            <span class="fw-bold">{{ $title ?? 'لوحة التحكم' }}</span>
            <span class="text-muted me-auto ms-3">مرحباً، {{ auth()->user()->name }}</span>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>