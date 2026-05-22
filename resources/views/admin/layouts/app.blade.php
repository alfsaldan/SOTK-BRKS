<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SOTK') — PT Bank Riau Kepri</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Heroicons (via CDN) -->
    <script src="https://unpkg.com/@heroicons/v1/outline/index.js" defer></script>
    
    <!-- jQuery & DataTables (for Preview Pagination & Sorting) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        :root {
            --brks-blue:    #003DA5;
            --brks-blue-dk: #002d7a;
            --brks-blue-lt: #e8eef8;
            --brks-gold:    #C9A84C;
            --sidebar-w:    250px;
            --header-h:     64px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4fb;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ─────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--brks-blue);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,.15);
        }

        .sidebar-brand {
            padding: 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.12);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand .logo-box {
            width: 40px; height: 40px;
            background: white;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: var(--brks-blue);
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            color: white;
            line-height: 1.25;
        }

        .sidebar-brand .brand-text strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
        }

        .sidebar-brand .brand-text span {
            font-size: 10.5px;
            opacity: .7;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 10px;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.45);
            padding: 10px 10px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,.78);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all .18s;
            margin-bottom: 2px;
        }

        .nav-item:hover { background: rgba(255,255,255,.12); color: white; }

        .nav-item.active {
            background: rgba(255,255,255,.18);
            color: white;
            font-weight: 600;
        }

        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,.1);
            font-size: 11px;
            color: rgba(255,255,255,.4);
            line-height: 1.5;
        }

        /* ── Main wrapper ─────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ───────────────────────────────── */
        .topbar {
            height: var(--header-h);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 12px;
        }

        .topbar-breadcrumb {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
        }

        .topbar-breadcrumb a { color: var(--brks-blue); text-decoration: none; font-weight: 500; }
        .topbar-breadcrumb a:hover { text-decoration: underline; }
        .topbar-breadcrumb .sep { color: #cbd5e1; }
        .topbar-breadcrumb .current { color: #1e293b; font-weight: 600; }

        .topbar-right { display: flex; align-items: center; gap: 10px; }

        /* ── Content area ─────────────────────────── */
        .content {
            flex: 1;
            padding: 28px;
        }

        /* ── Cards ────────────────────────────────── */
        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-header h2 {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .card-body { padding: 22px; }

        /* ── Buttons ──────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all .18s;
            line-height: 1;
        }

        .btn-primary {
            background: var(--brks-blue);
            color: white;
        }
        .btn-primary:hover { background: var(--brks-blue-dk); }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover { background: #e2e8f0; }

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-danger:hover { background: #fecaca; }

        .btn-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .btn-success:hover { background: #bbf7d0; }

        .btn-sm { padding: 5px 11px; font-size: 12px; }

        .btn svg { width: 16px; height: 16px; }

        /* ── Alerts ───────────────────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-info    { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

        /* ── Tables ───────────────────────────────── */
        .table-wrap { 
            overflow-x: auto; 
            max-height: 500px; 
            overflow-y: auto; 
        }

        table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }

        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8fafc;
            padding: 10px 14px;
            text-align: left;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        tbody tr {
            transition: background .12s;
        }
        tbody tr:hover { background: #f8fafc; }

        tbody td {
            border-bottom: 1px solid #f1f5f9;
            padding: 11px 14px;
            color: #334155;
            vertical-align: middle;
        }

        /* ── Badges ───────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 500;
            white-space: nowrap;
        }
        .badge-blue   { background: var(--brks-blue-lt); color: var(--brks-blue); }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-orange { background: #fff7ed; color: #c2410c; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* ── Form elements ────────────────────────── */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 9px 13px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            font-family: inherit;
            color: #1e293b;
            transition: border-color .18s, box-shadow .18s;
            background: white;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--brks-blue);
            box-shadow: 0 0 0 3px rgba(0,61,165,.1);
        }

        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px; padding-right: 34px; }

        /* ── Pagination ───────────────────────────── */
        .pagination { display: flex; align-items: center; gap: 4px; }
        .pagination .page-link {
            display: inline-flex; align-items: center; justify-content: center;
            width: 34px; height: 34px;
            border-radius: 8px;
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all .15s;
        }
        .pagination .page-link:hover { background: var(--brks-blue-lt); color: var(--brks-blue); border-color: var(--brks-blue); }
        .pagination .page-link.active { background: var(--brks-blue); color: white; border-color: var(--brks-blue); font-weight: 600; }
        .pagination .page-link.disabled { opacity: .4; pointer-events: none; }

        /* ── Stat cards ───────────────────────────── */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px; }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon svg { width: 22px; height: 22px; }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 3px;
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
        }

        /* ── Misc ─────────────────────────────────── */
        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 20px 0; }

        .text-muted { color: #94a3b8; }
        .text-sm    { font-size: 12px; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 14px; display: block; opacity: .4; }
        .empty-state p { font-size: 14px; }

        /* ── Modal ─────────────────────────────────── */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            backdrop-filter: blur(2px);
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop.show { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            padding: 28px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .modal-box h3 { font-size: 17px; font-weight: 700; margin-bottom: 10px; }
        .modal-box p  { font-size: 14px; color: #475569; line-height: 1.6; }
        .modal-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; }

        /* ── Responsive Mobile ─────────────────────── */
        .mobile-menu-btn { display: none; background: none; border: none; cursor: pointer; color: #1e293b; padding: 4px; margin-right: 12px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 90; opacity: 0; transition: opacity 0.3s; }
        
        @media (max-width: 992px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; width: 100%; }
            .topbar { padding: 0 16px; }
            .content { padding: 16px; }
            .mobile-menu-btn { display: block; }
            .sidebar-overlay.show { display: block; opacity: 1; }
            
            .stat-grid { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .card-header div { width: 100%; display: flex; flex-wrap: wrap; }
            .card-header .btn { flex: 1; justify-content: center; }
            
            .topbar-right span { display: none; }
            
            .modal-box { width: 95%; padding: 20px; }
            .modal-actions { flex-direction: column; }
            .modal-actions .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- ── Sidebar ────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo-box">BRKS</div>
            <div class="brand-text">
                <strong>SOTK BRKS</strong>
                <span>PT Bank Riau Kepri</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-label">Menu Utama</p>
            <a href="{{ route('admin.sotk.index') }}"
               class="nav-item {{ request()->routeIs('admin.sotk.index') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                Data Master SOTK
            </a>
            <a href="{{ route('admin.sotk.upload.form') }}"
               class="nav-item {{ request()->routeIs('admin.sotk.upload.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload SOTK
            </a>
            <a href="{{ route('admin.orgchart.index') }}"
               class="nav-item {{ request()->routeIs('admin.orgchart.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                Lihat Struktur Organisasi
            </a>
        </nav>

        <div class="sidebar-footer">
            Sistem SOTK &copy; {{ date('Y') }}<br>
            PT Bank Riau Kepri
        </div>
    </aside>

    <!-- ── Main ───────────────────────────── -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-breadcrumb">
                <a href="{{ route('admin.sotk.index') }}">SOTK</a>
                @hasSection('breadcrumb')
                    <span class="sep">›</span>
                    @yield('breadcrumb')
                @endif
            </div>
            <div class="topbar-right">
                <span style="font-size:12px;color:#94a3b8;">{{ now()->format('d M Y') }}</span>
            </div>
        </header>

        <!-- Content -->
        <main class="content">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('open');
            if (sidebar.classList.contains('open')) {
                overlay.classList.add('show');
            } else {
                overlay.classList.remove('show');
            }
        }
    </script>
</body>
</html>
