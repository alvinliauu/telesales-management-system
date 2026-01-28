<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Insurance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --header-height: 60px; --sidebar-bg: #212529; --sidebar-hover: #343a40; }
        body { min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); z-index: 1000; overflow-y: auto; }
        .sidebar-header { height: var(--header-height); display: flex; align-items: center; padding: 0 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { color: #fff; font-weight: 600; font-size: 1.2rem; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-logo i { font-size: 1.5rem; }
        .sidebar-menu { padding: 1rem 0; }
        .menu-label { color: rgba(255,255,255,0.5); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem; }
        .nav-item { margin: 2px 8px; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 0.65rem 1rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; text-decoration: none; }
        .nav-link:hover, .nav-link.active { color: #fff; background: var(--sidebar-hover); }
        .nav-link.active { background: #0d6efd; }
        .nav-link i { font-size: 1.1rem; width: 24px; text-align: center; }
        .nav-submenu { padding-left: 2.5rem; }
        .nav-submenu .nav-link { padding: 0.5rem 1rem; font-size: 0.9rem; }
        .main-content { margin-left: var(--sidebar-width); }
        .main-header { height: var(--header-height); background: #fff; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; position: sticky; top: 0; z-index: 100; }
        .page-content { padding: 1.5rem; }
        .stat-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.05); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
                <span>Insurance System</span>
            </a>
        </div>
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <div class="menu-label">Telesales</div>
                <li class="nav-item">
                    <a href="{{ route('telesales.renewal.index') }}" class="nav-link {{ request()->routeIs('telesales.renewal.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Renewal</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('telesales.upgrade.index') }}" class="nav-link {{ request()->routeIs('telesales.upgrade.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-up-circle"></i>
                        <span>Upgrade</span>
                    </a>
                </li>
                <div class="menu-label">Configuration</div>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Event Setup</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="main-content">
        <header class="main-header">
            <nav aria-label="breadcrumb">@yield('breadcrumb')</nav>
            <div class="dropdown">
                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name ?? 'Admin' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('info'))<div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
