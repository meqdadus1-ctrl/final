<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'بوابة الموظف' }} — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .portal-topbar {
            background: #1e3a5f;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .portal-topbar .brand { color: #fff; font-weight: 700; font-size: 16px; }
        .portal-topbar .brand small { color: #a8c4e0; font-weight: 400; font-size: 13px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0 !important; }
        .btn-primary { background: #1e3a5f; border-color: #1e3a5f; }
        .btn-primary:hover { background: #2d5a8e; border-color: #2d5a8e; }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <div class="portal-topbar mb-4">
        <div class="brand">
            <i class="fas fa-building me-2"></i>
            {{ config('app.name') }}
            <small class="ms-2">— بوابة الموظف</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">
                <i class="fas fa-user-circle me-1"></i>
                {{ auth()->user()->name }}
            </span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>خروج
                </button>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    <div class="container-fluid px-4">
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

        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
