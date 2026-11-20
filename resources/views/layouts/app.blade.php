<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HRMS') | Ministry of Youth and Sports – Liberia</title>
    <meta name="description" content="Human Resource Management System for the Ministry of Youth and Sports, Republic of Liberia">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#12355b">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css">

    <style>
        :root {
            --navy:       #000a60;
            --navy-dark:  #000030;
            --navy-light: #001280;
            --accent:       #4e60ff;
            --accent-light: #6c7cff;
            --accent-dark:  #3547d5;
            --sidebar-w:  270px;
            --topbar-h:   64px;
            --bg:         #f8fafc;
            --card-bg:    #ffffff;
            --text:       #1a1d23;
            --text-muted: #475569;
            --border:     #e5e7eb;
            --success:    #10b981;
            --warning:    #f59e0b;
            --danger:     #ef4444;
            --info:       #3b82f6;
            --radius:     12px;
            --radius-sm:  8px;
            --shadow:     0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
            --shadow-md:  0 4px 24px rgba(15,31,61,.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            overflow-x: hidden;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, #123F7A 0%, rgba(18, 63, 122, 1) 100%);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1040;
            transition: transform .3s ease, width .3s ease;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.1) transparent;
        }
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }
        .sidebar-brand-logo {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, rgba(18, 63, 122, 1), hsl(214, 74%, 27%));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(18,63,122,.35);
        }
        .sidebar-brand-text { flex: 1; min-width: 0; }
        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.2;
        }
        .sidebar-brand-text .title {
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            line-height: 1.25;
            display: block;
        }
        .sidebar-brand-text .subtitle {
            color: rgba(18, 63, 122, 0.75);
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            margin-top: 3px;
            display: block;
        }

        .sidebar-section-title {
            color: rgba(255,255,255,.4);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 20px 20px 6px;
        }

        .sidebar-nav { list-style: none; padding: 6px 12px; }
        .sidebar-nav .nav-item { margin-bottom: 2px; }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: rgba(255,255,255,.75);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all .2s ease;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar-nav .nav-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: linear-gradient(135deg, #123F7A 0%, rgba(18, 63, 122, 1) 100%);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(18, 63, 122, 0.35);
        }
        .sidebar-nav .nav-link.active i { color: #ffffff; }

        /* Submenu */
        .sidebar-submenu {
            list-style: none;
            padding: 4px 0 4px 42px;
            display: none;
        }
        .sidebar-submenu.show { display: block; }
        .sidebar-submenu .nav-link {
            padding: 7px 10px;
            font-size: 13px;
            color: rgba(255,255,255,.6);
        }
        .sidebar-submenu .nav-link.active {
            background: rgba(18, 63, 122, 0.18);
            color: #123F7A;
            font-weight: 600;
        }
        .sidebar-submenu .nav-link:hover { color: rgba(255,255,255,.85); }

        .nav-arrow {
            margin-left: auto;
            font-size: 11px;
            transition: transform .2s;
        }
        .nav-link[aria-expanded="true"] .nav-arrow { transform: rotate(90deg); }

        /* Sidebar badge */
        .sidebar-badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--navy-dark);
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.08);
            margin-top: auto;
        }

        /* ── Topbar ──────────────────────────────────────────── */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--topbar-h);
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 1030;
            gap: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            transition: left .3s ease;
        }

        .topbar-toggle {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: background .2s;
            display: none;
        }
        .topbar-toggle:hover { background: var(--bg); }

        .topbar-breadcrumb {
            flex: 1;
            font-size: 14px;
            color: var(--text-muted);
        }
        .topbar-breadcrumb .page-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            display: block;
            line-height: 1;
        }
        .topbar-breadcrumb .breadcrumb-path {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Topbar icons */
        .topbar-icon-btn {
            position: relative;
            width: 38px; height: 38px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            font-size: 16px;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .topbar-icon-btn:hover { background: var(--navy); color: #fff; border-color: var(--navy); }

        .notif-badge {
            position: absolute;
            top: -4px; right: -4px;
            background: var(--danger);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            width: 16px; height: 16px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--card-bg);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border: 1px solid var(--border);
            border-radius: 40px;
            cursor: pointer;
            transition: all .2s;
            background: var(--bg);
            text-decoration: none;
        }
        .topbar-user:hover { background: var(--card-bg); box-shadow: var(--shadow); }
        .topbar-user-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--navy);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-weight: 700;
            font-size: 12px;
            overflow: hidden;
        }
        .topbar-user-info .name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            line-height: 1.2;
        }
        .topbar-user-info .role {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ── Main Content ─────────────────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            min-height: calc(100vh - var(--topbar-h));
            padding: 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: margin-left .3s ease;
        }

        /* ── Global SVG & Pagination Safeguard ──────────────── */
        svg {
            max-width: 100%;
            max-height: 100%;
        }
        .pagination svg, nav svg {
            width: 14px !important;
            height: 14px !important;
            display: inline-block;
        }

        /* ── Cards ───────────────────────────────────────────── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: box-shadow .2s;
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header {
            background: none;
            border-bottom: 1px solid var(--border);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .card-header-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-header-title i { color: var(--accent); }
        .card-body { padding: 20px; }

        /* ── KPI Cards ───────────────────────────────────────── */
        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow);
            transition: all .25s ease;
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: var(--kpi-color, var(--accent));
            border-radius: 4px 0 0 4px;
        }
        .kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .kpi-icon {
            width: 52px; height: 52px;
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .kpi-info .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            font-family: 'DM Sans', sans-serif;
        }
        .kpi-info .kpi-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 4px;
        }
        .kpi-info .kpi-change {
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
        }
        .kpi-change.up { color: var(--success); }
        .kpi-change.down { color: var(--danger); }

        /* ── Buttons ─────────────────────────────────────────── */
        .btn-navy {
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: all .2s;
        }
        .btn-navy:hover { background: var(--navy-light); color: #fff; box-shadow: 0 4px 12px rgba(15,31,61,.3); }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: #fff;
            border: none;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all .2s;
        }
        .btn-accent:hover { box-shadow: 0 4px 12px rgba(78,96,255,.4); transform: translateY(-1px); color: #fff; }

        .btn-outline-navy {
            border: 1.5px solid var(--navy);
            color: var(--navy);
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: all .2s;
        }
        .btn-outline-navy:hover { background: var(--navy); color: #fff; }

        /* ── Tables ──────────────────────────────────────────── */
        .table th {
            background: #f8f9fc;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
            font-size: 13.5px;
            border-bottom: 1px solid #f0f2f5;
        }
        .table tbody tr:hover td { background: #f8f9fc; }

        /* Employee Avatar */
        .emp-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }
        .emp-avatar-placeholder {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy), var(--navy-light));
            color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            font-weight: 700;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        /* ── Forms ───────────────────────────────────────────── */
        .form-control, .form-select {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-size: 13.5px;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(15,31,61,.1);
        }
        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        /* ── Badges ──────────────────────────────────────────── */
        .badge { font-size: 11px; font-weight: 600; border-radius: 6px; padding: 3px 8px; }

        /* ── Tabs (multi-step form) ──────────────────────────── */
        .form-steps {
            display: flex;
            gap: 0;
            margin-bottom: 28px;
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .form-step {
            flex: 1;
            padding: 14px 10px;
            text-align: center;
            background: var(--bg);
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            border-right: 1px solid var(--border);
            cursor: pointer;
            transition: all .2s;
        }
        .form-step:last-child { border-right: none; }
        .form-step.active {
            background: var(--navy);
            color: #fff;
        }
        .form-step.completed {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--navy-dark);
        }
        .form-step i { display: block; font-size: 18px; margin-bottom: 4px; }

        /* ── Alert / Toast ───────────────────────────────────── */
        .toast-container { z-index: 9999; }

        /* ── Page header ─────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .page-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
        }
        .page-header p { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
            .topbar-toggle { display: flex !important; }
        }
        @media (max-width: 575.98px) {
            .card-body.p-4 { padding: 1rem !important; }
            .display-6 { font-size: 1.5rem; }
            .notif-dropdown { width: calc(100vw - 24px); max-width: 340px; }
            .table-responsive { -webkit-overflow-scrolling: touch; }
            .btn-sm { padding: .4rem .7rem; }
            img, video, canvas { max-width: 100%; }
        }

        /* ── Sidebar overlay (mobile) ─────────────────────────── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1039;
        }
        #sidebar-overlay.show { display: block; }

        /* ── DataTables override ─────────────────────────────── */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            padding: 6px 12px;
            font-size: 13px;
        }
        div.dt-buttons button {
            border-radius: var(--radius-sm) !important;
            font-size: 12px !important;
        }

        /* ── Chart containers ────────────────────────────────── */
        .chart-wrapper { position: relative; height: 260px; }

        /* ── Notification dropdown ────────────────────────────── */
        .notif-dropdown { width: 340px; }
        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 12px;
            transition: background .15s;
        }
        .notif-item:hover { background: var(--bg); }
        .notif-item.unread { background: rgba(78,96,255,.05); }
        .notif-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            margin-top: 5px;
            flex-shrink: 0;
        }

        /* ── Scrollable sidebar animation ─────────────────────── */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeIn .3s ease forwards; }

        /* ── Print styles ────────────────────────────────────── */
        @media print {
            #sidebar, #topbar { display: none !important; }
            #main-content { margin: 0 !important; padding: 0 !important; }
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>
<body>
@php
    $authUser = auth()->user()?->fresh() ?? auth()->user();
@endphp

<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay"></div>

<!-- ══════════════════════════════════════════════════════ SIDEBAR -->
<nav id="sidebar">
    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="MYS Logo" style="width: 44px; height: 44px; object-fit: contain; flex-shrink: 0;">
        @else
            <div class="sidebar-brand-logo">MYS</div>
        @endif
        <div class="sidebar-brand-text">
            <span class="title">Ministry of Youth &amp; Sports</span>
            <span class="subtitle">Republic of Liberia</span>
        </div>
    </a>

    <!-- Main Navigation -->
    <p class="sidebar-section-title">Main Menu</p>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>
    </ul>

    <p class="sidebar-section-title">Human Resources</p>
    <ul class="sidebar-nav">
        <!-- Employees -->
        <li class="nav-item">
            <a href="#menuEmployees" class="nav-link {{ request()->is('employees*') ? 'active' : '' }}"
               data-bs-toggle="collapse" aria-expanded="{{ request()->is('employees*') ? 'true' : 'false' }}">
                <i class="bi bi-people"></i> Employees
                <i class="bi bi-chevron-right nav-arrow"></i>
            </a>
            <ul class="sidebar-submenu collapse {{ request()->is('employees*') || request()->is('contractors*') ? 'show' : '' }}" id="menuEmployees">
                <li><a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}">All Employees</a></li>
                <li><a href="{{ route('contractors.index') }}" class="nav-link {{ request()->routeIs('contractors.index') || request()->routeIs('contractors.show') ? 'active' : '' }}">Contractors</a></li>
                @can('create employees')
                <li><a href="{{ route('employees.create') }}" class="nav-link {{ request()->routeIs('employees.create') ? 'active' : '' }}">Add Employee</a></li>
                <li><a href="{{ route('employees.import') }}" class="nav-link {{ request()->routeIs('employees.import') ? 'active' : '' }}">Bulk Import (CSV)</a></li>
                @endcan
            </ul>
        </li>

        <!-- Departments -->
        @can('view departments')
        <li class="nav-item">
            <a href="{{ route('departments.index') }}" class="nav-link {{ request()->is('departments*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Departments
            </a>
        </li>
        @endcan

        <!-- Recruitment -->
        @canany(['view vacancies', 'view applications'])
        <li class="nav-item">
            <a href="#menuRecruitment" class="nav-link {{ request()->is('recruitment*') ? 'active' : '' }}"
               data-bs-toggle="collapse" aria-expanded="{{ request()->is('recruitment*') ? 'true' : 'false' }}">
                <i class="bi bi-briefcase"></i> Recruitment
                <i class="bi bi-chevron-right nav-arrow"></i>
            </a>
            <ul class="sidebar-submenu collapse {{ request()->is('recruitment*') ? 'show' : '' }}" id="menuRecruitment">
                @can('view vacancies')<li><a href="{{ route('recruitment.vacancies') }}" class="nav-link {{ request()->routeIs('recruitment.vacancies') ? 'active' : '' }}">Vacancies</a></li>@endcan
                @can('view applications')<li><a href="{{ route('recruitment.applications') }}" class="nav-link {{ request()->routeIs('recruitment.applications') ? 'active' : '' }}">Applications</a></li>@endcan
            </ul>
        </li>
        @endcan
        @can('view internal vacancies')
        <li class="nav-item">
            <a href="{{ route('employee.recruitment') }}" class="nav-link {{ request()->routeIs('employee.recruitment') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i> Internal Opportunities
            </a>
        </li>
        @endcan
        @can('manage email communications')
        <li class="nav-item"><a href="{{ route('communications.email') }}" class="nav-link {{ request()->routeIs('communications.email') ? 'active' : '' }}"><i class="bi bi-envelope-paper"></i> HR Email Communications</a></li>
        @endcan
        @can('manage department communications')<li class="nav-item"><a href="{{ route('department-communications.index') }}" class="nav-link {{ request()->routeIs('department-communications.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Department Communications</a></li>@endcan
        @can('view directory')<li class="nav-item"><a href="{{ route('directory.index') }}" class="nav-link"><i class="bi bi-people"></i> Employee Directory</a></li>@endcan
        @can('view assets')<li class="nav-item"><a href="{{ route('assets.index') }}" class="nav-link"><i class="bi bi-box-seam"></i> Asset Management</a></li>@endcan
        @can('view staff movements')<li class="nav-item"><a href="{{ route('staff-movements.index') }}" class="nav-link"><i class="bi bi-arrow-left-right"></i> Staff Movement</a></li>@endcan
        @can('view contracts')<li class="nav-item"><a href="{{ route('contracts.index') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i> Contract Management</a></li>@endcan
        @can('manage contract renewals')<li class="nav-item"><a href="{{ route('contracts.renewals') }}" class="nav-link"><i class="bi bi-arrow-repeat"></i> Contract Renewals</a></li>@endcan
        @can('view grievances')<li class="nav-item"><a href="{{ route('grievances.index') }}" class="nav-link"><i class="bi bi-shield-exclamation"></i> Grievances</a></li>@endcan

        <!-- Attendance -->
        @can('view attendance')
        <li class="nav-item">
            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Attendance Dashboard
            </a>
        </li>
        @endcan
        <!-- Leave -->
        @canany(['view leave', 'apply leave'])
        <li class="nav-item">
            <a href="#menuLeave" class="nav-link {{ request()->is('leave*') ? 'active' : '' }}"
               data-bs-toggle="collapse" aria-expanded="{{ request()->is('leave*') ? 'true' : 'false' }}">
                <i class="bi bi-calendar2-x"></i> Leave Management
                <i class="bi bi-chevron-right nav-arrow"></i>
            </a>
            <ul class="sidebar-submenu collapse {{ request()->is('leave*') ? 'show' : '' }}" id="menuLeave">
                @can('apply leave')<li><a href="{{ route('leave.apply') }}" class="nav-link {{ request()->routeIs('leave.apply') ? 'active' : '' }}">Apply Leave</a></li>@endcan
                @can('view leave')<li><a href="{{ route('leave.approvals') }}" class="nav-link {{ request()->routeIs('leave.approvals') ? 'active' : '' }}">Approvals</a></li>@endcan
                @can('manage leave types')<li><a href="{{ route('leave.types') }}" class="nav-link {{ request()->routeIs('leave.types') ? 'active' : '' }}">Leave Types</a></li>@endcan
                @canany(['submit excuse', 'view excuses'])<li><a href="{{ route('excuses.index') }}" class="nav-link {{ request()->routeIs('excuses.*') ? 'active' : '' }}">Excuse Management</a></li>@endcanany
            </ul>
        </li>
        @endcan
    </ul>

    <p class="sidebar-section-title">Finance & Payroll</p>
    <ul class="sidebar-nav">
        @can('view payroll')
        <li class="nav-item">
            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Finance Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="#menuPayroll" class="nav-link {{ request()->is('payroll*') ? 'active' : '' }}"
               data-bs-toggle="collapse" aria-expanded="{{ request()->is('payroll*') ? 'true' : 'false' }}">
                <i class="bi bi-cash-stack"></i> Payroll
                <i class="bi bi-chevron-right nav-arrow"></i>
            </a>
            <ul class="sidebar-submenu collapse {{ request()->is('payroll*') ? 'show' : '' }}" id="menuPayroll">
                <li><a href="{{ route('payroll.index') }}" class="nav-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}">Payroll Runs</a></li>
            </ul>
        </li>
        @endcan
    </ul>

    <p class="sidebar-section-title">Development</p>
    <ul class="sidebar-nav">
        @can('view performance')
        <li class="nav-item">
            <a href="{{ route('performance.index') }}" class="nav-link {{ request()->routeIs('performance.index') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Performance
            </a>
        </li>
        @endcan
        @can('view training')
        <li class="nav-item">
            <a href="{{ route('training.index') }}" class="nav-link {{ request()->routeIs('training.index') ? 'active' : '' }}">
                <i class="bi bi-mortarboard"></i> Training
            </a>
        </li>
        @endcan
    </ul>

    <p class="sidebar-section-title">System</p>
    <ul class="sidebar-nav">
        @can('view reports')
        <li class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('analytics.index') }}" class="nav-link {{ request()->routeIs('analytics.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Advanced Analytics
            </a>
        </li>
        @endcan
        @can('view users')
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-shield-person"></i> Users & Roles
            </a>
        </li>
        @endcan
        @can('view audit logs')
        <li class="nav-item">
            <a href="{{ route('audit.index') }}" class="nav-link {{ request()->is('audit*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Audit Logs
            </a>
        </li>
        @endcan
        @can('manage system settings')
        <li class="nav-item">
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </li>
        @endcan
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer mt-auto" style="padding: 14px; border-top: 1px solid rgba(255,255,255,.08);">
        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                @if($authUser?->employee?->photo_path)
                <img src="{{ $authUser->employee->photo_url }}" class="rounded-circle border border-warning" style="width:34px; height:34px; object-fit:cover;">
                @else
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:34px; height:34px; font-size:11px; background: var(--accent); color: var(--navy-dark);">
                    {{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}
                </div>
                @endif
                <div class="text-truncate">
                    <div style="color:#fff; font-size:12px; font-weight:600;" class="text-truncate">
                        {{ $authUser->name ?? 'User' }}
                    </div>
                    <small style="color: rgba(255,255,255,0.6); font-size:10.5px;" class="d-block text-truncate">
                        {{ $authUser->primary_role ?? 'Staff' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-2">
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="btn btn-sm text-white-50 hover-white p-1 border-0" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-6 text-warning"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════ TOPBAR -->
<header id="topbar">
    <button class="topbar-toggle" id="sidebarToggle" style="margin-right: 15px;">
        <i class="bi bi-list"></i>
    </button>

    <!-- Breadcrumbs -->
    <div class="d-none d-lg-flex align-items-center gap-2 text-muted small">
        <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none hover-primary">HRMIS</a>
        <i class="bi bi-chevron-right style-xs"></i>
        <span class="fw-semibold text-navy">@yield('page-title', 'Dashboard')</span>
    </div>

    <!-- Right side of topbar: Notifications & User info -->
    <div class="d-flex align-items-center gap-3 ms-auto">

        <!-- Notifications Bell Center -->
        @php
            $sysNotifications = \App\Models\SystemNotification::latest()->take(6)->get();
            if ($sysNotifications->isEmpty()) {
                \App\Models\SystemNotification::notify('System SaaS Upgrade Completed', 'Enterprise HRMIS dashboard & module views upgraded successfully.', 'bi-check-circle-fill', 'success', route('dashboard'));
                \App\Models\SystemNotification::notify('Biometric Verification Active', 'Staff check-in and check-out verification logs are operational.', 'bi-fingerprint', 'primary', route('attendance.index'));
                \App\Models\SystemNotification::notify('Civil Service Retirement Alert', 'Staff members approaching 60-year statutory retirement threshold.', 'bi-exclamation-triangle-fill', 'warning', route('analytics.index'));
                $sysNotifications = \App\Models\SystemNotification::latest()->take(6)->get();
            }
            $unreadCount = $sysNotifications->where('is_read', false)->count();
        @endphp
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center justify-content-center bg-white text-navy rounded-circle border shadow-2xs hover-shadow transition-all" data-bs-toggle="dropdown" style="width: 40px; height: 40px; font-size: 17px; text-decoration: none; position: relative; border-color: #E2E8F0 !important;" id="notifDropdownTrigger" aria-expanded="false">
                <i class="bi bi-bell text-navy"></i>
                <span id="notifBadge" class="position-absolute d-flex align-items-center justify-content-center bg-danger text-white border border-white font-weight-700 {{ $unreadCount > 0 ? '' : 'd-none' }}" style="top: -2px; right: -2px; min-width: 18px; height: 18px; padding: 0 4px; font-size: 10px; border-radius: 9999px;">
                    {{ $unreadCount }}
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0 mt-2 border-0 shadow-lg" style="width: 400px; max-width: 92vw; border-radius: 16px; overflow: hidden; border: 1px solid #E2E8F0 !important;">
                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between px-4 py-3 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-bell-fill text-accent" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-white" style="font-size: 14px; letter-spacing: -0.2px;">Notifications</span>
                                @if($unreadCount > 0)
                                <span class="badge rounded-pill px-2 py-0.5" style="background: rgba(16, 185, 129, 0.25); color: #34d399; font-size: 10.5px; font-weight: 600;">{{ $unreadCount }} unread</span>
                                @endif
                            </div>
                            <div class="text-white-50" style="font-size: 11px;">Ministry system updates &amp; activity log</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" onclick="playNotificationSound()" title="Test Notification Sound" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center opacity-85 hover-opacity-100 me-1" style="width:28px; height:28px;">
                            <i class="bi bi-volume-up-fill" style="font-size:12px;"></i>
                        </button>
                        <button type="button" onclick="markAllNotificationsRead()" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1 text-white border-white-50 font-weight-600 hover-bg-white-10" style="font-size: 11px;">
                            <i class="bi bi-check2-all me-1"></i>Mark read
                        </button>
                    </div>
                </div>

                <!-- List Body -->
                <div style="max-height: 340px; overflow-y: auto; overflow-x: hidden;">
                    @if($authUser?->employee)
                        @php($userNotifications = $authUser->notifications()->latest()->take(6)->get())
                        @forelse($userNotifications as $notification)
                        <a href="{{ route('employee.notifications.read', $notification->id) }}" class="d-flex align-items-start gap-3 px-4 py-3 border-bottom text-decoration-none">
                            <i class="bi bi-info-circle text-primary fs-5"></i><div><strong>{{ $notification->title }}</strong><p class="mb-0 small text-muted">{{ $notification->message }}</p></div>
                        </a>
                        @empty
                        @endforelse
                    @endif
                    @forelse($sysNotifications as $notif)
                    <a href="{{ route('notifications.read', $notif->id) }}" class="d-flex align-items-start gap-3 px-4 py-3 border-bottom text-decoration-none transition-all" style="border-color: #F1F5F9 !important; {{ $notif->is_read ? 'background: #ffffff; border-left: 3px solid transparent;' : 'background: #F8FAFC; border-left: 3px solid #10b981;' }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 36px; height: 36px; font-size: 15px; {{ $notif->badge_color == 'success' ? 'background: #ECFDF5; color: #059669;' : ($notif->badge_color == 'warning' ? 'background: #FFFBEB; color: #D97706;' : 'background: #EFF6FF; color: #2563EB;') }}">
                            <i class="bi {{ $notif->icon }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <span style="font-size: 13px; font-weight: 600; color: #0F172A; line-height: 1.35;">{{ $notif->title }}</span>
                                <span class="flex-shrink-0" style="font-size: 11px; color: #94A3B8; font-weight: 500;">{{ $notif->created_at->diffForHumans(null, true) }}</span>
                            </div>
                            <p class="mb-0" style="font-size: 12px; color: #475569; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $notif->message }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-5 text-muted" style="font-size:13px;">
                        <i class="bi bi-bell-slash fs-3 d-block mb-2 text-muted opacity-50"></i>
                        <span>No notifications to display</span>
                    </div>
                    @endforelse
                </div>

                <!-- Footer -->
                <div class="text-center py-3 bg-light border-top" style="background: #F8FAFC !important; border-color: #E2E8F0 !important;">
                    <a href="{{ route('audit.index') }}" class="small text-navy text-decoration-none font-weight-600 d-inline-flex align-items-center gap-1.5 hover-text-primary transition-all" style="font-size:12.5px;">
                        <span>View Full System Audit Trail</span>
                        <i class="bi bi-arrow-right text-primary"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- User profile dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                @if($authUser?->employee?->photo_path)
                    <img src="{{ $authUser->employee->photo_url }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 50%;" class="border" alt="Avatar">
                @else
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold border" style="width: 36px; height: 36px; font-size: 12px;">
                        {{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}
                    </div>
                @endif
                <div class="d-none d-xl-block text-start">
                    <div class="fw-bold text-navy lh-1" style="font-size: 13px;">{{ $authUser->name }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ $authUser->primary_role ?? 'Administrator' }}</small>
                </div>
                <i class="bi bi-chevron-down text-muted small d-none d-xl-block"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm border-0" style="min-width:210px; border-radius: 12px; font-size: 13px;">
                <li>
                    <div class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-navy" style="font-size:13px;">{{ $authUser->name }}</div>
                        <div class="text-muted small" style="font-size:11px;">{{ $authUser->email }}</div>
                    </div>
                </li>
                @if($authUser?->employee)
                <li><a class="dropdown-item py-2" href="{{ route('employee.profile') }}"><i class="bi bi-person me-2 text-primary"></i>My Profile</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('employee.payslips') }}"><i class="bi bi-wallet2 me-2 text-success"></i>My Payslips</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('employee.notifications') }}"><i class="bi bi-bell me-2 text-warning"></i>My Notifications</a></li>
                @else
                <li><a class="dropdown-item py-2" href="{{ route('users.index') }}"><i class="bi bi-person me-2 text-primary"></i>My Account Profile</a></li>
                @endif
                <li><a class="dropdown-item py-2" href="{{ route('users.index') }}"><i class="bi bi-gear me-2 text-secondary"></i>System Users &amp; Roles</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph me-2 text-info"></i>System Reports</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger py-2">
                            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- ══════════════════════════════════════════════════════ MAIN CONTENT -->
<main id="main-content">
    <!-- Flash messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')

    <!-- ══════════════════════════════════════════════════════ EXECUTIVE FOOTER -->
    <footer class="mt-5 pt-4 pb-4 border-top text-center" style="border-color: #e2e8f0 !important;">
        <div class="d-flex flex-column align-items-center justify-content-center gap-2">
            @if(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="MYS Official Logo" style="height: 48px; width: auto; object-fit: contain;" class="mb-1">
            @endif
            <div class="fw-bold text-navy" style="font-size: 14px; letter-spacing: 0.3px;">
                Ministry of Youth & Sports &bull; Republic of Liberia
            </div>
            <small class="text-muted" style="font-size: 12px;">
                Human Resource Management Information System (HRMIS) &copy; {{ date('Y') }}. All rights reserved.
            </small>
        </div>
    </footer>
</main>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

<!-- ══════════════════════════════════════════════════════ SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
// Global AJAX CSRF setup
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
});

// Sidebar toggle
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const topbar         = document.getElementById('topbar');
const mainContent    = document.getElementById('main-content');
const sidebarToggle  = document.getElementById('sidebarToggle');

sidebarToggle?.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    sidebarOverlay.classList.toggle('show');
});
sidebarOverlay?.addEventListener('click', () => {
    sidebar.classList.remove('show');
    sidebarOverlay.classList.remove('show');
});

// Web Audio API Enterprise Notification Chime Synthesizer
function playNotificationSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        // Tone 1: C5 (523.25 Hz)
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(523.25, ctx.currentTime);
        gain1.gain.setValueAtTime(0.12, ctx.currentTime);
        gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(ctx.currentTime);
        osc1.stop(ctx.currentTime + 0.3);

        // Tone 2: E5 (659.25 Hz)
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(659.25, ctx.currentTime + 0.12);
        gain2.gain.setValueAtTime(0.15, ctx.currentTime + 0.12);
        gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(ctx.currentTime + 0.12);
        osc2.stop(ctx.currentTime + 0.5);
    } catch (e) {
        console.log('Audio chime error:', e);
    }
}

// Toast helper
function showToast(message, type = 'success') {
    playNotificationSound();
    const icons = { success: 'check-circle-fill', danger: 'exclamation-triangle-fill', info: 'info-circle-fill', warning: 'exclamation-circle-fill' };
    const id = 'toast-' + Date.now();
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-${icons[type] || 'info-circle-fill'}"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`);
    new bootstrap.Toast(document.getElementById(id), { delay: 4000 }).show();
}

// Confirm delete helper
function confirmDelete(form, name = 'this record') {
    if (confirm(`Are you sure you want to delete ${name}? This action cannot be undone.`)) form.submit();
}

// Ctrl+K Global Search shortcut
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        const input = document.getElementById('globalSearchInput');
        if (input) input.focus();
    }
});
// Mark All Notifications as Read JS helper
function markAllNotificationsRead() {
    fetch('{{ route("notifications.read-all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const badge = document.getElementById('notifBadge');
            if(badge) badge.classList.add('d-none');
            document.querySelectorAll('.notif-dropdown a.bg-light-subtle').forEach(el => {
                el.classList.remove('bg-light-subtle', 'fw-semibold');
                el.classList.add('bg-white');
            });
            if (typeof showToast === 'function') {
                showToast('All notifications marked as read', 'success');
            }
        }
    });
}
</script>

@stack('scripts')
@livewireScripts
</body>
</html>
