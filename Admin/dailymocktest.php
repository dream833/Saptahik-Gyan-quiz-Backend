<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Mock Test - WB Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            display: flex;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .sidebar-brand .brand-icon svg { width: 22px; height: 22px; fill: #fff; }
        .sidebar-brand .brand-text { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
        .sidebar-brand .brand-text span { color: #818cf8; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .sidebar-nav .nav-label {
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1.2px; color: rgba(255,255,255,0.35);
            padding: 12px 12px 8px;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; color: rgba(255,255,255,0.65);
            text-decoration: none; border-radius: 10px;
            font-size: 14px; font-weight: 500;
            transition: all 0.2s ease; margin-bottom: 2px;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-nav a.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; box-shadow: 0 4px 15px rgba(99,102,241,0.3);
        }
        .sidebar-nav a .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sidebar-nav a .nav-icon svg { width: 20px; height: 20px; fill: currentColor; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; color: rgba(255,255,255,0.65);
            text-decoration: none; border-radius: 10px;
            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
        }
        .sidebar-footer a:hover { background: rgba(255,50,50,0.12); color: #ff6b6b; }
        .sidebar-footer a .nav-icon svg { width: 20px; height: 20px; fill: currentColor; }
        /* ===== MAIN ===== */
        .main-content { margin-left: 270px; flex: 1; min-height: 100vh; }
        .topbar {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 999;
        }
        .topbar .menu-toggle { display: none; background: none; border: none; cursor: pointer; padding: 8px; color: #0f172a; }
        .topbar .menu-toggle svg { width: 24px; height: 24px; fill: currentColor; }
        .topbar .page-title { font-size: 20px; font-weight: 700; color: #0f172a; }
        .topbar .topbar-right { display: flex; align-items: center; gap: 20px; }
        .topbar .topbar-right .admin-info { display: flex; align-items: center; gap: 10px; }
        .topbar .topbar-right .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: 14px;
            box-shadow: 0 2px 8px rgba(99,102,241,0.25);
        }
        .page-content { padding: 32px; max-width: 1400px; margin: 0 auto; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .page-header p { color: #64748b; font-size: 15px; margin-top: 4px; }
        /* ===== FORM CARD ===== */
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.04);
            padding: 28px;
            margin-bottom: 28px;
        }
        .form-card h2 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card h2 .form-icon {
            width: 32px; height: 32px;
            background: #eef2ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-card h2 .form-icon svg { width: 16px; height: 16px; fill: #6366f1; }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .form-group label .required { color: #ef4444; }
        .form-group select,
        .form-group input,
        .form-group textarea {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .form-group select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group select:disabled { background-color: #f1f5f9; cursor: not-allowed; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99,102,241,0.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.35); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger {
            background: #fef2f2;
            color: #ef4444;
        }
        .btn-danger:hover { background: #fee2e2; }
        .btn-success {
            background: #f0fdf4;
            color: #22c55e;
        }
        .btn-success:hover { background: #dcfce7; }
        .btn-warning {
            background: #fffbeb;
            color: #f59e0b;
        }
        .btn-warning:hover { background: #fef3c7; }
        .btn-info {
            background: #eef2ff;
            color: #6366f1;
        }
        .btn-info:hover { background: #e0e7ff; }
        .btn-sm { padding: 7px 14px; font-size: 12px; border-radius: 8px; }
        .btn-icon { padding: 8px; border-radius: 8px; }
        .form-actions { display: flex; gap: 10px; margin-top: 8px; grid-column: 1 / -1; }
        /* ===== TABLE ===== */
        .table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        thead th {
            padding: 16px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr { transition: all 0.15s ease; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }
        tbody td { padding: 16px 20px; font-size: 14px; color: #334155; vertical-align: middle; }
        .class-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .subject-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #f0fdf4;
            color: #16a34a;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .q-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px; height: 32px;
            background: #f1f5f9;
            border-radius: 50%;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }
        .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state .empty-icon {
            width: 48px; height: 48px; margin: 0 auto 12px;
            background: #f1f5f9; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #e2e8f0;
        }
        .empty-state .empty-icon svg { width: 24px; height: 24px; fill: #94a3b8; }
        .empty-state h3 { font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 4px; }
        .empty-state p { font-size: 14px; color: #94a3b8; }
        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15,23,42,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 80px rgba(0,0,0,0.2);
            animation: modalIn 0.25s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-header {
            padding: 24px 28px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-close {
            width: 32px; height: 32px;
            border: none; background: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s;
        }
        .modal-close:hover { background: #e2e8f0; color: #0f172a; }
        .modal-close svg { width: 18px; height: 18px; fill: currentColor; }
        .modal-body { padding: 24px 28px; }
        .modal-footer {
            padding: 16px 28px 24px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #f1f5f9;
        }
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .option-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .option-group label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .option-group input {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            transition: all 0.2s;
            outline: none;
        }
        .option-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .correct-answer-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .correct-answer-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            padding: 8px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .correct-answer-group label:hover { border-color: #c7d2fe; }
        .correct-answer-group input[type="radio"] { accent-color: #6366f1; }
        .correct-answer-group label.selected {
            border-color: #6366f1;
            background: #eef2ff;
            color: #4f46e5;
        }
        /* Question list inside modal */
        .question-item {
            padding: 16px;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.15s;
        }
        .question-item:hover { border-color: #e2e8f0; background: #fafafa; }
        .question-item .q-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .question-item .q-text {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.5;
            flex: 1;
        }
        .question-item .q-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-top: 10px;
        }
        .question-item .q-option {
            font-size: 13px;
            color: #475569;
            padding: 4px 10px;
            background: #f8fafc;
            border-radius: 6px;
        }
        .question-item .q-option.correct {
            background: #f0fdf4;
            color: #16a34a;
            font-weight: 600;
        }
        .question-item .q-actions {
            display: flex;
            gap: 6px;
            margin-top: 10px;
        }
        /* ===== OVERLAY ===== */
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .sidebar-overlay.active { display: block; }
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 14px 20px; }
            .page-content { padding: 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-card { padding: 20px; }
            .options-grid { grid-template-columns: 1fr; }
            .question-item .q-options { grid-template-columns: 1fr; }
            .correct-answer-group { flex-wrap: wrap; }
            .actions-cell { flex-direction: column; }
            thead th, tbody td { padding: 12px 14px; }
            .modal { max-width: 100%; margin: 10px; border-radius: 16px; }
        }
        @media (max-width: 480px) {
            .page-content { padding: 16px; }
            .form-card { padding: 16px; }
            .page-header h1 { font-size: 22px; }
            .topbar .page-title { font-size: 17px; }
            thead th, tbody td { padding: 10px 12px; font-size: 13px; }
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }
        .modal::-webkit-scrollbar { width: 6px; }
        .modal::-webkit-scrollbar-track { background: transparent; }
        .modal::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
            <div class="brand-text">WB<span>Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="Dashboard.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>Dashboard</a>
            <a href="Users.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></span>Users</a>
            <a href="dailymocktest.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></span>Daily Mock Test</a>
            <a href="allmocktestscreen.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg></span>All Mock Test Screen</a>
            <a href="solutions.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>All Solution</a>
            <div class="nav-label">Results</div>
            <a href="dailymockresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></span>Daily Mock Test Result</a>
            <a href="allmocktestresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>All Mock Test Result</a>
        </nav>
        <div class="sidebar-footer">
            <a href="login.php" onclick="return confirm('Are you sure you want to logout?')">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span>Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <h1 class="page-title">Daily Mock Test</h1>
            <div class="topbar-right">
                <div class="admin-info">
                    <div class="admin-avatar">A</div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#0f172a;">Admin</div>
                        <div style="font-size:12px;color:#64748b;">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Daily Mock Test</h1>
                <p>Create and manage daily mock tests with questions</p>
            </div>

            <!-- Add Mock Test Form -->
            <div class="form-card">
                <h2>
                    <span class="form-icon"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></span>
                    Add New Mock Test
                </h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Class <span class="required">*</span></label>
                        <div style="display:flex;gap:8px;">
                            <select id="classSelect" style="flex:1;">
                                <option value="">Select Class</option>
                            </select>
                            <button class="btn btn-secondary btn-sm" onclick="showAddClass()" type="button" title="Add New Class" style="padding:10px 12px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            </button>
                        </div>
                        <div id="addClassRow" style="display:none;margin-top:8px;">
                            <div style="display:flex;gap:8px;">
                                <input type="text" id="newClassName" placeholder="Enter class name (e.g. Class 11)" style="flex:1;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:'Inter',sans-serif;outline:none;">
                                <button class="btn btn-primary btn-sm" onclick="addNewClass()" type="button" style="padding:8px 14px;font-size:12px;">Add</button>
                                <button class="btn btn-secondary btn-sm" onclick="hideAddClass()" type="button" style="padding:8px 14px;font-size:12px;">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject <span class="required">*</span></label>
                        <div style="display:flex;gap:8px;">
                            <select id="subjectSelect" disabled style="flex:1;">
                                <option value="">Select Class First</option>
                            </select>
                            <button class="btn btn-secondary btn-sm" onclick="showAddSubject()" type="button" title="Add New Subject" style="padding:10px 12px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            </button>
                        </div>
                        <div id="addSubjectRow" style="display:none;margin-top:8px;">
                            <div style="display:flex;gap:8px;">
                                <input type="text" id="newSubjectName" placeholder="Enter subject name" style="flex:1;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:'Inter',sans-serif;outline:none;">
                                <button class="btn btn-primary btn-sm" onclick="addNewSubject()" type="button" style="padding:8px 14px;font-size:12px;">Add</button>
                                <button class="btn btn-secondary btn-sm" onclick="hideAddSubject()" type="button" style="padding:8px 14px;font-size:12px;">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Test Name <span class="required">*</span></label>
                        <input type="text" id="testName" placeholder="e.g. Weekly Math Test">
                    </div>
                    <div class="form-group">
                        <label>Date <span class="required">*</span></label>
                        <input type="date" id="testDate">
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" id="testDuration" placeholder="e.g. 30" min="1">
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea id="testDesc" placeholder="Enter a brief description of the mock test..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" id="submitTestBtn" onclick="addMockTest()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            <span id="submitBtnText">Add Mock Test</span>
                        </button>
                        <button class="btn btn-warning" onclick="setUpcomingMode()" id="upcomingBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg>
                            Upcoming Test
                        </button>
                        <button class="btn btn-secondary" onclick="resetForm()" id="resetBtn">Reset</button>
                    </div>
                </div>
            </div>

            <!-- Mock Tests Table -->
            <div class="table-container">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Test Name</th>
                                <th>Duration</th>
                                <th>Questions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mockTestBody">
                            <tr id="emptyRow">
                                <td colspan="8">
                                    <div class="empty-state" id="emptyState">
                                        <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg></div>
                                        <h3>No mock tests yet</h3>
                                        <p>Add your first mock test using the form above.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Tests -->
            <div class="form-card" style="margin-top:28px;border:1px solid #fef3c7;background:#fffbeb;">
                <h2>
                    <span class="form-icon" style="background:#fef3c7;"><svg viewBox="0 0 24 24" fill="#f59e0b"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>
                    Upcoming Tests
                </h2>
                <p style="font-size:13px;color:#92400e;margin-bottom:16px;margin-top:-12px;">Tests scheduled for future dates</p>
                <div class="table-container">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Test Name</th>
                                    <th>Duration</th>
                                    <th>Questions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="upcomingTestBody">
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></div>
                                            <h3>No upcoming tests</h3>
                                            <p>Schedule a test for a future date to see it here.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Old Mock Test Data -->
            <div class="form-card" style="margin-top:28px;border:1px solid #f1f5f9;background:#fafafa;">
                <h2>
                    <span class="form-icon" style="background:#fef2f2;"><svg viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>
                    Old Mock Test Data
                </h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Select Date</label>
                        <input type="date" id="oldTestDate" onchange="loadOldTests()">
                    </div>
                    <div class="form-group" style="justify-content:flex-end;">
                        <div style="display:flex;gap:8px;align-items:flex-end;height:100%;padding-bottom:2px;">
                            <button class="btn btn-primary btn-sm" onclick="loadOldTests()" style="padding:10px 20px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5z"/></svg>
                                Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-container" style="margin-top:16px;">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Test Name</th>
                                    <th>Duration</th>
                                    <th>Questions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="oldTestBody">
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></div>
                                            <h3>Select a date</h3>
                                            <p>Choose a date above to view old mock tests.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Question Modal -->
    <div class="modal-overlay" id="questionModal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="questionModalTitle">Add Question</h2>
                <button class="modal-close" onclick="closeQuestionModal()">
                    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Question <span class="required">*</span></label>
                    <textarea id="questionText" placeholder="Enter the question..." style="min-height:60px;"></textarea>
                </div>
                <div class="options-grid">
                    <div class="option-group">
                        <label>Option A <span class="required">*</span></label>
                        <input type="text" id="optA" placeholder="Option A">
                    </div>
                    <div class="option-group">
                        <label>Option B <span class="required">*</span></label>
                        <input type="text" id="optB" placeholder="Option B">
                    </div>
                    <div class="option-group">
                        <label>Option C <span class="required">*</span></label>
                        <input type="text" id="optC" placeholder="Option C">
                    </div>
                    <div class="option-group">
                        <label>Option D <span class="required">*</span></label>
                        <input type="text" id="optD" placeholder="Option D">
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:8px;">Correct Answer <span class="required">*</span></label>
                    <div class="correct-answer-group" id="correctAnswerGroup">
                        <label><input type="radio" name="correctAns" value="A"> A</label>
                        <label><input type="radio" name="correctAns" value="B"> B</label>
                        <label><input type="radio" name="correctAns" value="C"> C</label>
                        <label><input type="radio" name="correctAns" value="D"> D</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeQuestionModal()">Cancel</button>
                <button class="btn btn-primary" id="saveQuestionBtn" onclick="saveQuestion()">Save Question</button>
            </div>
        </div>
    </div>

    <!-- View Questions Modal -->
    <div class="modal-overlay" id="viewQuestionsModal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="viewQuestionsTitle">Questions</h2>
                <button class="modal-close" onclick="closeViewQuestionsModal()">
                    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
            <div class="modal-body" id="questionsList">
                <p style="color:#94a3b8;text-align:center;padding:20px;">No questions added yet.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeViewQuestionsModal()">Close</button>
                <button class="btn btn-primary" onclick="closeViewQuestionsModal(); openAddQuestionForCurrent();">+ Add Question</button>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); }
        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // ===== SAMPLE DATA =====
        const classesData = [
            { id: 1, name: 'Class 6' },
            { id: 2, name: 'Class 7' },
            { id: 3, name: 'Class 8' },
            { id: 4, name: 'Class 9' },
            { id: 5, name: 'Class 10' }
        ];

        const subjectsData = {
            1: [
                { id: 1, name: 'Mathematics' },
                { id: 2, name: 'Science' },
                { id: 3, name: 'English' },
                { id: 4, name: 'Social Studies' },
                { id: 5, name: 'Hindi' }
            ],
            2: [
                { id: 6, name: 'Mathematics' },
                { id: 7, name: 'Science' },
                { id: 8, name: 'English' },
                { id: 9, name: 'Social Studies' },
                { id: 10, name: 'Sanskrit' }
            ],
            3: [
                { id: 11, name: 'Mathematics' },
                { id: 12, name: 'Physics' },
                { id: 13, name: 'Chemistry' },
                { id: 14, name: 'Biology' },
                { id: 15, name: 'English' }
            ],
            4: [
                { id: 16, name: 'Mathematics' },
                { id: 17, name: 'Physics' },
                { id: 18, name: 'Chemistry' },
                { id: 19, name: 'Biology' },
                { id: 20, name: 'English' }
            ],
            5: [
                { id: 21, name: 'Mathematics' },
                { id: 22, name: 'Physics' },
                { id: 23, name: 'Chemistry' },
                { id: 24, name: 'Biology' },
                { id: 25, name: 'English' }
            ]
        };

        function getSubjectName(classId, subjectId) {
            const subs = subjectsData[classId] || [];
            const sub = subs.find(s => s.id === subjectId);
            return sub ? sub.name : 'Unknown';
        }

        function getClassName(classId) {
            const cls = classesData.find(c => c.id === classId);
            return cls ? cls.name : 'Unknown';
        }

        // ===== STATE =====
        let mockTests = [];
        let testIdCounter = 1;
        let questionIdCounter = 1;

        // Editing state
        let editingTestId = null;       // which test we're adding question to
        let editingQuestionId = null;   // which question we're editing (null = new)

        // ===== INIT =====
        function init() {
            populateClasses();
            setupClassSubjectBinding();
        }

        function populateClasses() {
            const sel = document.getElementById('classSelect');
            const currentVal = sel.value;
            sel.innerHTML = '<option value="">Select Class</option>';
            classesData.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
            if (currentVal) sel.value = currentVal;
        }

        function populateSubjects(classId) {
            const subjectSel = document.getElementById('subjectSelect');
            subjectSel.innerHTML = '';
            if (!classId) {
                subjectSel.innerHTML = '<option value="">Select Class First</option>';
                subjectSel.disabled = true;
                return;
            }
            subjectSel.disabled = false;
            const subs = subjectsData[classId] || [];
            subjectSel.innerHTML = '<option value="">Select Subject</option>';
            subs.forEach(s => {
                subjectSel.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        }

        function setupClassSubjectBinding() {
            const classSel = document.getElementById('classSelect');
            const subjectSel = document.getElementById('subjectSelect');
            classSel.addEventListener('change', function() {
                const classId = parseInt(this.value);
                populateSubjects(classId);
            });
        }

        // ===== ADD CLASS =====
        let classIdCounter = 6;
        let subjectIdCounter = 26;

        function showAddClass() {
            document.getElementById('addClassRow').style.display = 'block';
            document.getElementById('newClassName').focus();
        }

        function hideAddClass() {
            document.getElementById('addClassRow').style.display = 'none';
            document.getElementById('newClassName').value = '';
        }

        function addNewClass() {
            const name = document.getElementById('newClassName').value.trim();
            if (!name) { alert('Please enter a class name.'); return; }

            // Check if class already exists
            const exists = classesData.some(c => c.name.toLowerCase() === name.toLowerCase());
            if (exists) { alert('This class already exists.'); return; }

            const newId = classIdCounter++;
            classesData.push({ id: newId, name });
            subjectsData[newId] = [];

            hideAddClass();
            populateClasses();

            // Auto-select the new class
            document.getElementById('classSelect').value = newId;
            populateSubjects(newId);
        }

        // ===== ADD SUBJECT =====
        function showAddSubject() {
            const classId = parseInt(document.getElementById('classSelect').value);
            if (!classId) { alert('Please select a class first.'); return; }
            document.getElementById('addSubjectRow').style.display = 'block';
            document.getElementById('newSubjectName').focus();
        }

        function hideAddSubject() {
            document.getElementById('addSubjectRow').style.display = 'none';
            document.getElementById('newSubjectName').value = '';
        }

        function addNewSubject() {
            const classId = parseInt(document.getElementById('classSelect').value);
            if (!classId) { alert('Please select a class first.'); return; }

            const name = document.getElementById('newSubjectName').value.trim();
            if (!name) { alert('Please enter a subject name.'); return; }

            const subs = subjectsData[classId] || [];
            const exists = subs.some(s => s.name.toLowerCase() === name.toLowerCase());
            if (exists) { alert('This subject already exists for the selected class.'); return; }

            const newId = subjectIdCounter++;
            subs.push({ id: newId, name });

            hideAddSubject();
            populateSubjects(classId);

            // Auto-select the new subject
            document.getElementById('subjectSelect').value = newId;
        }

        // ===== STATE for editing =====
        let editingMockTestId = null;  // null = adding new, number = editing existing

        // ===== ADD / UPDATE MOCK TEST =====
        function addMockTest() {
            const classId = parseInt(document.getElementById('classSelect').value);
            const subjectId = parseInt(document.getElementById('subjectSelect').value);
            const testName = document.getElementById('testName').value.trim();
            const testDesc = document.getElementById('testDesc').value.trim();
            const duration = document.getElementById('testDuration').value.trim();

            if (!classId || !subjectId || !testName) {
                alert('Please select a class, subject, and enter a test name.');
                return;
            }

            const dateInput = document.getElementById('testDate');
            const testDate = dateInput.value || new Date().toISOString().split('T')[0];

            if (editingMockTestId) {
                // UPDATE existing test
                const test = mockTests.find(t => t.id === editingMockTestId);
                if (test) {
                    test.date = testDate;
                    test.classId = classId;
                    test.subjectId = subjectId;
                    test.className = getClassName(classId);
                    test.subjectName = getSubjectName(classId, subjectId);
                    test.name = testName;
                    test.description = testDesc || '';
                    test.duration = duration || '—';
                }
                editingMockTestId = null;
                document.getElementById('submitBtnText').textContent = 'Add Mock Test';
                document.getElementById('submitTestBtn').className = 'btn btn-primary';
            } else {
                // CREATE new test
                const test = {
                    id: testIdCounter++,
                    date: testDate,
                    classId,
                    subjectId,
                    className: getClassName(classId),
                    subjectName: getSubjectName(classId, subjectId),
                    name: testName,
                    description: testDesc || '',
                    duration: duration || '—',
                    questions: []
                };
                mockTests.push(test);
            }

            resetForm();
            renderTable();
            renderUpcoming();
        }

        function resetForm() {
            document.getElementById('classSelect').value = '';
            document.getElementById('classSelect').dispatchEvent(new Event('change'));
            document.getElementById('testName').value = '';
            document.getElementById('testDesc').value = '';
            document.getElementById('testDuration').value = '';
            document.getElementById('testDate').value = new Date().toISOString().split('T')[0];
            // Reset editing state
            editingMockTestId = null;
            document.getElementById('submitBtnText').textContent = 'Add Mock Test';
            document.getElementById('submitTestBtn').className = 'btn btn-primary';
        }

        // ===== SET UPCOMING MODE =====
        function setUpcomingMode() {
            // Set date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dateStr = tomorrow.toISOString().split('T')[0];
            document.getElementById('testDate').value = dateStr;
            // Scroll to form
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('testName').focus();
            // Reset any editing state
            editingMockTestId = null;
            document.getElementById('submitBtnText').textContent = 'Add Mock Test';
            document.getElementById('submitTestBtn').className = 'btn btn-primary';
        }

        // ===== EDIT MOCK TEST (populate form) =====
        function editMockTest(testId) {
            const test = mockTests.find(t => t.id === testId);
            if (!test) return;

            editingMockTestId = testId;

            // Populate form fields
            document.getElementById('classSelect').value = test.classId;
            document.getElementById('classSelect').dispatchEvent(new Event('change'));
            // Subjects populate synchronously via dispatchEvent, so set immediately
            document.getElementById('subjectSelect').value = test.subjectId;
            document.getElementById('testName').value = test.name;
            document.getElementById('testDate').value = test.date;
            document.getElementById('testDuration').value = test.duration === '—' ? '' : test.duration;
            document.getElementById('testDesc').value = test.description;

            // Change button to update mode
            document.getElementById('submitBtnText').textContent = 'Update Test';
            document.getElementById('submitTestBtn').className = 'btn btn-warning';

            // Scroll to form
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ===== DELETE MOCK TEST =====
        function deleteMockTest(testId) {
            if (!confirm('Are you sure you want to delete this mock test? All questions will also be removed.')) return;
            mockTests = mockTests.filter(t => t.id !== testId);
            renderTable();
            renderUpcoming();
        }

        // ===== RENDER TABLE (today's tests only — old tests go to Old section) =====
        function renderTable() {
            const today = new Date().toISOString().split('T')[0];
            const todaysTests = mockTests.filter(t => t.date === today);
            const tbody = document.getElementById('mockTestBody');

            if (todaysTests.length === 0) {
                tbody.innerHTML = `
                    <tr id="emptyRow">
                        <td colspan="8">
                            <div class="empty-state" id="emptyState">
                                <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg></div>
                                <h3>No tests for today</h3>
                                <p>Create a new test above or repost an old one from the section below.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            todaysTests.forEach((test, idx) => {
                const qCount = test.questions.length;
                html += `
                    <tr>
                        <td style="font-weight:600;color:#64748b;">${idx + 1}</td>
                        <td style="color:#64748b;font-size:13px;white-space:nowrap;">Today</td>
                        <td><span class="class-badge">${test.className}</span></td>
                        <td><span class="subject-badge">${test.subjectName}</span></td>
                        <td style="font-weight:600;">${test.name}</td>
                        <td>${test.duration} min</td>
                        <td><span class="q-count">${qCount}</span></td>
                        <td>
                            <div class="actions-cell">
                                <button class="btn btn-info btn-sm" onclick="openAddQuestion(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                    Add Q
                                </button>
                                <button class="btn btn-success btn-sm" onclick="viewQuestions(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                    ${qCount} Q
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteMockTest(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // ===== QUESTION MODAL =====
        function openAddQuestion(testId) {
            editingTestId = testId;
            editingQuestionId = null;
            document.getElementById('questionModalTitle').textContent = 'Add Question';
            document.getElementById('saveQuestionBtn').textContent = 'Save Question';
            clearQuestionForm();
            document.getElementById('questionModal').classList.add('active');
        }

        function openAddQuestionForCurrent() {
            if (editingTestId) {
                openAddQuestion(editingTestId);
            }
        }

        function editQuestion(testId, qId) {
            const test = mockTests.find(t => t.id === testId);
            if (!test) return;
            const q = test.questions.find(q => q.id === qId);
            if (!q) return;

            editingTestId = testId;
            editingQuestionId = qId;

            document.getElementById('questionModalTitle').textContent = 'Edit Question';
            document.getElementById('saveQuestionBtn').textContent = 'Update Question';
            document.getElementById('questionText').value = q.text;
            document.getElementById('optA').value = q.options[0];
            document.getElementById('optB').value = q.options[1];
            document.getElementById('optC').value = q.options[2];
            document.getElementById('optD').value = q.options[3];

            const radios = document.getElementsByName('correctAns');
            radios.forEach(r => {
                r.checked = r.value === q.correct;
                r.parentElement.classList.toggle('selected', r.value === q.correct);
            });

            document.getElementById('questionModal').classList.add('active');
            closeViewQuestionsModal();
        }

        function saveQuestion() {
            const text = document.getElementById('questionText').value.trim();
            const optA = document.getElementById('optA').value.trim();
            const optB = document.getElementById('optB').value.trim();
            const optC = document.getElementById('optC').value.trim();
            const optD = document.getElementById('optD').value.trim();
            const correctRadio = document.querySelector('input[name="correctAns"]:checked');
            const correct = correctRadio ? correctRadio.value : '';

            if (!text || !optA || !optB || !optC || !optD || !correct) {
                alert('Please fill in all fields and select the correct answer.');
                return;
            }

            const test = mockTests.find(t => t.id === editingTestId);
            if (!test) return;

            if (editingQuestionId) {
                // Edit existing
                const q = test.questions.find(q => q.id === editingQuestionId);
                if (q) {
                    q.text = text;
                    q.options = [optA, optB, optC, optD];
                    q.correct = correct;
                }
            } else {
                // Add new
                test.questions.push({
                    id: questionIdCounter++,
                    text,
                    options: [optA, optB, optC, optD],
                    correct
                });
            }

            clearQuestionForm();
            document.getElementById('questionModal').classList.remove('active');
            renderTable();

            // If view modal is open, refresh it
            if (document.getElementById('viewQuestionsModal').classList.contains('active')) {
                viewQuestions(editingTestId);
            }
        }

        function deleteQuestion(testId, qId) {
            if (!confirm('Delete this question?')) return;
            const test = mockTests.find(t => t.id === testId);
            if (!test) return;
            test.questions = test.questions.filter(q => q.id !== qId);
            renderTable();
            viewQuestions(testId); // refresh view
        }

        function clearQuestionForm() {
            document.getElementById('questionText').value = '';
            document.getElementById('optA').value = '';
            document.getElementById('optB').value = '';
            document.getElementById('optC').value = '';
            document.getElementById('optD').value = '';
            const radios = document.getElementsByName('correctAns');
            radios.forEach(r => {
                r.checked = false;
                r.parentElement.classList.remove('selected');
            });
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').classList.remove('active');
            editingQuestionId = null;
        }

        // ===== VIEW QUESTIONS MODAL =====
        function viewQuestions(testId) {
            const test = mockTests.find(t => t.id === testId);
            if (!test) return;

            editingTestId = testId;
            document.getElementById('viewQuestionsTitle').textContent = `Questions - ${test.name}`;
            const list = document.getElementById('questionsList');

            if (test.questions.length === 0) {
                list.innerHTML = '<p style="color:#94a3b8;text-align:center;padding:20px;">No questions added yet.</p>';
            } else {
                let html = '';
                test.questions.forEach((q, idx) => {
                    const labels = ['A', 'B', 'C', 'D'];
                    html += `
                        <div class="question-item">
                            <div class="q-header">
                                <div class="q-text">${idx + 1}. ${q.text}</div>
                            </div>
                            <div class="q-options">
                                ${q.options.map((opt, i) => `
                                    <div class="q-option ${labels[i] === q.correct ? 'correct' : ''}">
                                        ${labels[i]}. ${opt} ${labels[i] === q.correct ? '✓' : ''}
                                    </div>
                                `).join('')}
                            </div>
                            <div class="q-actions">
                                <button class="btn btn-warning btn-sm" onclick="editQuestion(${testId}, ${q.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteQuestion(${testId}, ${q.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    `;
                });
                list.innerHTML = html;
            }

            document.getElementById('viewQuestionsModal').classList.add('active');
        }

        function closeViewQuestionsModal() {
            document.getElementById('viewQuestionsModal').classList.remove('active');
        }

        // Close modals on overlay click
        document.getElementById('questionModal').addEventListener('click', function(e) {
            if (e.target === this) closeQuestionModal();
        });
        document.getElementById('viewQuestionsModal').addEventListener('click', function(e) {
            if (e.target === this) closeViewQuestionsModal();
        });

        // Toggle selected class on correct answer radio labels
        document.querySelectorAll('.correct-answer-group input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.correct-answer-group label').forEach(l => l.classList.remove('selected'));
                if (this.checked) this.parentElement.classList.add('selected');
            });
        });        // ===== FORMAT DATE =====
        function formatDate(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        // ===== OLD MOCK TESTS =====
        function loadOldTests() {
            const dateInput = document.getElementById('oldTestDate');
            const selectedDate = dateInput.value;
            const tbody = document.getElementById('oldTestBody');

            if (!selectedDate) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></div>
                                            <h3>Select a date</h3>
                                            <p>Choose a date above to view old mock tests.</p>
                                        </div>
                                    </td>
                                </tr>
                            `;
                return;
            }

            const filtered = mockTests.filter(t => t.date === selectedDate);

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5z"/></svg></div>
                                <h3>No tests found</h3>
                                <p>No mock tests were created on ${formatDate(selectedDate)}.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            filtered.forEach((test, idx) => {
                const qCount = test.questions.length;
                html += `
                    <tr>
                        <td style="font-weight:600;color:#64748b;">${idx + 1}</td>
                        <td><span class="class-badge">${test.className}</span></td>
                        <td><span class="subject-badge">${test.subjectName}</span></td>
                        <td style="font-weight:600;">${test.name}</td>
                        <td>${test.duration} min</td>
                        <td><span class="q-count">${qCount}</span></td>
                        <td>
                            <div class="actions-cell">
                                <button class="btn btn-warning btn-sm" onclick="repostTest(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35A7.958 7.958 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                                    Repost
                                </button>
                                <button class="btn btn-success btn-sm" onclick="viewQuestions(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                    ${qCount} Q
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteMockTest(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function repostTest(testId) {
            const test = mockTests.find(t => t.id === testId);
            if (!test) return;

            const today = new Date().toISOString().split('T')[0];

            const newTest = {
                id: testIdCounter++,
                date: today,
                classId: test.classId,
                subjectId: test.subjectId,
                className: test.className,
                subjectName: test.subjectName,
                name: test.name + ' (Repost)',
                description: test.description,
                duration: test.duration,
                questions: test.questions.map(q => ({
                    id: questionIdCounter++,
                    text: q.text,
                    options: [...q.options],
                    correct: q.correct
                }))
            };

            mockTests.push(newTest);
            renderTable();
            renderUpcoming();

            // Refresh old tests view if same date is selected
            const oldDateInput = document.getElementById('oldTestDate');
            if (oldDateInput.value) {
                loadOldTests();
            }

            alert(`Test reposted successfully for ${formatDate(today)}!`);
        }

        // ===== RENDER UPCOMING TESTS (future dates) =====
        function renderUpcoming() {
            const today = new Date().toISOString().split('T')[0];
            const upcoming = mockTests.filter(t => t.date > today).sort((a, b) => a.date.localeCompare(b.date));
            const tbody = document.getElementById('upcomingTestBody');

            if (upcoming.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></div>
                                <h3>No upcoming tests</h3>
                                <p>Schedule a test for a future date to see it here.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            upcoming.forEach((test, idx) => {
                const qCount = test.questions.length;
                const displayDate = test.date ? formatDate(test.date) : '';
                html += `
                    <tr>
                        <td style="font-weight:600;color:#64748b;">${idx + 1}</td>
                        <td style="color:#92400e;font-size:13px;font-weight:600;white-space:nowrap;">${displayDate}</td>
                        <td><span class="class-badge">${test.className}</span></td>
                        <td><span class="subject-badge">${test.subjectName}</span></td>
                        <td style="font-weight:600;">${test.name}</td>
                        <td>${test.duration} min</td>
                        <td><span class="q-count">${qCount}</span></td>
                        <td>
                            <div class="actions-cell">
                                <button class="btn btn-info btn-sm" onclick="openAddQuestion(${test.id})" title="Add Questions">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                    Add Q
                                </button>
                                <button class="btn btn-success btn-sm" onclick="viewQuestions(${test.id})" title="View Questions">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                    ${qCount} Q
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="editMockTest(${test.id})" title="Edit Test Details" style="background:#fef3c7;color:#92400e;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteMockTest(${test.id})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // Init (sets today's date and populates dropdowns)
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('testDate').value = today;
        init();
        renderUpcoming();
    </script>
</body>
</html>
