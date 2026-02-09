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
        :root { --sidebar-width: 260px; --header-height: 60px; --sidebar-bg: #1a1a1a; --sidebar-hover: #2d2d2d; }
        body { min-height: 100vh; background-color: #f5f5f5; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); z-index: 1000; overflow-y: auto; }
        .sidebar-header { height: var(--header-height); display: flex; align-items: center; padding: 0 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { color: #fff; font-weight: 600; font-size: 1.1rem; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-logo i { font-size: 1.5rem; }
        .sidebar-menu { padding: 1rem 0; }
        .menu-label { color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem; }
        .nav-item { margin: 2px 8px; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 0.6rem 1rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; text-decoration: none; font-size: 0.9rem; }
        .nav-link:hover, .nav-link.active { color: #fff; background: var(--sidebar-hover); }
        .nav-link.active { background: #333; }
        .nav-link i { font-size: 1rem; width: 20px; text-align: center; }
        .main-content { margin-left: var(--sidebar-width); }
        .main-header { height: var(--header-height); background: #fff; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; position: sticky; top: 0; z-index: 100; }
        .page-content { padding: 1.5rem; }
        .stat-card { border: none; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); background: #fff; }
        .stat-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .table-hover tbody tr:hover { background-color: #f9f9f9; }
        .btn-dark { background-color: #333; border-color: #333; }
        .btn-dark:hover { background-color: #1a1a1a; border-color: #1a1a1a; }
        .btn-outline-dark { color: #333; border-color: #333; }
        .btn-outline-dark:hover { background-color: #333; color: #fff; }
        .role-badge { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
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

                @if(Auth::user()->role === 'underwriter')
                <div class="menu-label">Underwriter</div>
                <li class="nav-item">
                    <a href="{{ route('data-query.index') }}" class="nav-link {{ request()->routeIs('data-query.*') ? 'active' : '' }}">
                        <i class="bi bi-database"></i>
                        <span>Data Query</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('uw-review.index') }}" class="nav-link {{ request()->routeIs('uw-review.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span>UW Review</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->role === 'marketing')
                <div class="menu-label">Marketing</div>
                <li class="nav-item">
                    <a href="{{ route('marketing-review.index') }}" class="nav-link {{ request()->routeIs('marketing-review.*') ? 'active' : '' }}">
                        <i class="bi bi-send-check"></i>
                        <span>Marketing Review</span>
                    </a>
                </li>
                @endif

                <div class="menu-label">Data</div>
                <li class="nav-item">
                    <a href="{{ route('telesales.renewal.index') }}" class="nav-link {{ request()->routeIs('telesales.renewal.*') ? 'active' : '' }}">
                        <i class="bi bi-table"></i>
                        <span>All Renewal Data</span>
                    </a>
                </li>

                <div class="menu-label">Setup</div>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') || request()->routeIs('events.packages.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('car-types.index') }}" class="nav-link {{ request()->routeIs('car-types.*') ? 'active' : '' }}">
                        <i class="bi bi-car-front"></i>
                        <span>Car Types</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('extensions.index') }}" class="nav-link {{ request()->routeIs('extensions.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-plus"></i>
                        <span>Extensions</span>
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
                    <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name ?? 'User' }}
                    <span class="badge bg-{{ Auth::user()->role === 'underwriter' ? 'dark' : 'secondary' }} role-badge ms-1">{{ strtoupper(Auth::user()->role) }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted">{{ Auth::user()->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))<div class="alert alert-dark alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('info'))<div class="alert alert-secondary alert-dismissible fade show"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
