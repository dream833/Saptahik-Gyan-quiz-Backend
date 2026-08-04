<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once "../utils/api_config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Solution - WB Admin</title>
    <style>
        /* ===== RESET & BASE ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-icon svg { width: 22px; height: 22px; fill: #fff; }
        .sidebar-brand .brand-text { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
        .sidebar-brand .brand-text span { color: #667eea; }
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; box-shadow: 0 4px 15px rgba(102,126,234,0.3);
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
        .main-content { margin-left: 270px; flex: 1; min-height: 100vh; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }

        /* ===== TOPBAR ===== */
        .topbar {
            background: #fff; padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            position: sticky; top: 0; z-index: 999;
        }
        .topbar .menu-toggle { display: none; background: none; border: none; cursor: pointer; padding: 8px; color: #374151; }
        .topbar .menu-toggle svg { width: 24px; height: 24px; fill: currentColor; }
        .topbar .page-title { font-size: 20px; font-weight: 700; color: #1a1a2e; }
        .topbar .topbar-right { display: flex; align-items: center; gap: 20px; }
        .topbar .topbar-right .admin-info { display: flex; align-items: center; gap: 10px; }
        .topbar .topbar-right .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: 14px;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content { padding: 32px; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 24px; font-weight: 700; color: #1a1a2e; }
        .page-header p { color: #6b7280; font-size: 14px; margin-top: 4px; }

        /* ===== TABS ===== */
        .tabs-container {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .tabs-nav {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            overflow-x: auto;
        }
        .tabs-nav button {
            flex: 1;
            min-width: 160px;
            padding: 16px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            position: relative;
            transition: all 0.2s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .tabs-nav button:hover { color: #374151; background: rgba(0,0,0,0.02); }
        .tabs-nav button.active {
            color: #4f46e5;
            background: #fff;
        }
        .tabs-nav button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px 3px 0 0;
        }
        .tabs-nav button svg { width: 18px; height: 18px; fill: currentColor; flex-shrink: 0; }
        .tab-panel { display: none; padding: 28px; }
        .tab-panel.active { display: block; }

        /* ===== SELECT ROW ===== */
        .select-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        .select-row .form-group {
            flex: 1;
            min-width: 150px;
        }
        .select-row .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .select-row select, .select-row input, .select-row textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }
        .select-row select:focus, .select-row input:focus, .select-row textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .select-row select:disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        /* ===== CARDS ===== */
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .card-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }
        .card-header .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            background: #eef2ff;
            color: #4f46e5;
        }

        /* ===== TABLE ===== */
        .table-wrap {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            vertical-align: top;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }
        .btn-primary:hover { box-shadow: 0 4px 12px rgba(102,126,234,0.4); transform: translateY(-1px); }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-success {
            background: #059669;
            color: #fff;
        }
        .btn-success:hover { background: #047857; }
        .btn-danger {
            background: #ef4444;
            color: #fff;
        }
        .btn-danger-sm {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-danger-sm:hover { background: #fee2e2; }
        .btn-warning-sm {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .btn-warning-sm:hover { background: #fef3c7; }
        .btn-icon {
            width: 32px; height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }
        .btn-loading { pointer-events: none; opacity: 0.7; }

        /* ===== FORM FIELDS ===== */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #1f2937;
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .form-row .form-group { flex: 1; min-width: 200px; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
        }
        .empty-state svg {
            width: 48px; height: 48px;
            fill: #d1d5db;
            margin-bottom: 12px;
        }
        .empty-state h4 { font-size: 16px; color: #6b7280; margin-bottom: 4px; }
        .empty-state p { font-size: 13px; }

        /* ===== TOAST / NOTIFICATION ===== */
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 99999;
            display: flex; flex-direction: column; gap: 8px;
            max-width: 360px; width: calc(100% - 40px);
        }
        .toast {
            padding: 14px 18px; border-radius: 12px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            display: flex; align-items: center; gap: 10px;
            animation: toastIn 0.3s ease; cursor: pointer;
        }
        .toast-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .toast-error { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
        .toast-info { background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ===== SPINNER ===== */
        .spinner {
            display: inline-block; width: 18px; height: 18px;
            border: 2.5px solid #e2e8f0; border-top-color: #6366f1;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== CATEGORY LIST ===== */
        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .category-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .category-chip:hover { background: #eef2ff; border-color: #667eea; }
        .category-chip.active { background: #eef2ff; border-color: #667eea; color: #4f46e5; }
        .category-chip .chip-del {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: #e5e7eb;
            font-size: 12px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s ease;
            border: none;
        }
        .category-chip .chip-del:hover { background: #fecaca; color: #dc2626; }

        /* ===== ANSWER BOX ===== */
        .answer-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 8px;
            font-size: 14px;
            color: #166534;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .answer-box strong { color: #15803d; }

        /* ===== PDF LINK ===== */
        .pdf-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #fef2f2;
            color: #dc2626;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .pdf-link:hover { background: #fee2e2; }
        .pdf-link svg { width: 16px; height: 16px; fill: currentColor; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 12px 16px; }
            .topbar .page-title { font-size: 17px; }
            .page-content { padding: 16px; }
            .tab-panel { padding: 16px; }
            .tabs-nav button { min-width: 100px; padding: 12px 16px; font-size: 13px; }
            .select-row { flex-direction: column; }
            .select-row .form-group { min-width: 100%; }
        }
        @media (max-width: 480px) {
            .page-content { padding: 12px; }
            .tab-panel { padding: 12px; }
            .tabs-nav button { min-width: 80px; padding: 10px 12px; font-size: 12px; }
            .tabs-nav button span.tab-label { display: none; }
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }
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
            <a href="dailymocktest.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></span>Daily Mock Test</a>
            <a href="allmocktestscreen.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg></span>All Mock Test Screen</a>
            <a href="solutions.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>All Solution</a>
            <a href="notifications.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></span>Notifications</a>
            <div class="nav-label">Results</div>
            <a href="dailymockresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></span>Daily Mock Test Result</a>
            <a href="allmocktestresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>All Mock Test Result</a>
        </nav>
        <div class="sidebar-footer">
            <a href="login.php" onclick="return confirm('Are you sure you want to logout?')">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span>Logout
            </a>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <h1 class="page-title">All Solution</h1>
            <div class="topbar-right">
                <div class="admin-info">
                    <div class="admin-avatar">A</div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1f2937;">Admin</div>
                        <div style="font-size:12px;color:#6b7280;">Administrator</div>
                    </div>
                </div>
            </div>
        </header>
        <div class="page-content">
            <div class="page-header">
                <h1>All Solution</h1>
                <p>Manage questions & answers, suggestions, and previous year question papers</p>
            </div>

            <div class="tabs-container">
                <!-- Tab Navigation -->
                <nav class="tabs-nav" role="tablist">
                    <button class="active" data-tab="tabQA" role="tab" aria-selected="true">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <span class="tab-label">Q &amp; A</span>
                    </button>
                    <button data-tab="tabSuggestions" role="tab" aria-selected="false">
                        <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12zM7 9h2v2H7V9zm4 0h2v2h-2V9zm4 0h2v2h-2V9z"/></svg>
                        <span class="tab-label">Suggestions</span>
                    </button>
                    <button data-tab="tabPYQ" role="tab" aria-selected="false">
                        <svg viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/></svg>
                        <span class="tab-label">Previous Year</span>
                    </button>
                </nav>

                <!-- ======================== TAB 1: QUESTIONS & ANSWERS ======================== -->
                <div class="tab-panel active" id="tabQA" role="tabpanel">
                    <!-- Cascade Selects -->
                    <div class="select-row">
                        <div class="form-group">
                            <label>Class</label>
                            <select id="qaClass" onchange="qaLoadSubjects()">
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <select id="qaSubject" disabled onchange="qaLoadChapters()">
                                <option value="">Select Class First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Chapter</label>
                            <select id="qaChapter" disabled onchange="qaLoadQuestionTypes()">
                                <option value="">Select Subject First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Question Type</label>
                            <select id="qaType" disabled onchange="qaLoadQuestions()">
                                <option value="">Select Chapter First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Questions in this Section</h3>
                            <button class="btn btn-primary btn-sm" onclick="qaShowAddForm()">+ Add Question</button>
                        </div>
                        <div id="qaQuestionsContainer">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                <h4>Select Class, Subject, Chapter &amp; Question Type</h4>
                                <p>Questions will appear here once you make your selection.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Add / Edit Question Form -->
                    <div id="qaForm" style="display:none;">
                        <div class="card">
                            <div class="card-header">
                                <h3 id="qaFormTitle">Add New Question</h3>
                                <button class="btn btn-secondary btn-sm" onclick="qaHideForm()">Cancel</button>
                            </div>
                            <div class="form-group">
                                <label>Question</label>
                                <textarea id="qaQuestionText" rows="3" placeholder="Enter the question..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Answer</label>
                                <textarea id="qaAnswerText" rows="5" placeholder="Enter the answer..."></textarea>
                            </div>
                            <button class="btn btn-success" id="qaSaveBtn" onclick="qaSaveQuestion()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                Save Question
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ======================== TAB 2: SUGGESTIONS ======================== -->
                <div class="tab-panel" id="tabSuggestions" role="tabpanel">
                    <!-- Cascade Selects -->
                    <div class="select-row">
                        <div class="form-group">
                            <label>Class</label>
                            <select id="sugClass" onchange="sugLoadSubjects()">
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <select id="sugSubject" disabled onchange="sugLoadSuggestions()">
                                <option value="">Select Class First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Suggestions List -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Suggestions</h3>
                            <button class="btn btn-primary btn-sm" onclick="sugShowAddForm()">+ Add Suggestion</button>
                        </div>
                        <div id="sugContainer">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                <h4>Select a Class &amp; Subject</h4>
                                <p>Suggestions will appear here.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Add / Edit Suggestion Form -->
                    <div id="sugForm" style="display:none;">
                        <div class="card">
                            <div class="card-header">
                                <h3 id="sugFormTitle">Add New Suggestion</h3>
                                <button class="btn btn-secondary btn-sm" onclick="sugHideForm()">Cancel</button>
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" id="sugTitle" placeholder="Enter the suggestion title...">
                            </div>
                            <div class="form-group">
                                <label>Description (optional)</label>
                                <textarea id="sugDescription" rows="2" placeholder="Brief description..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Answer</label>
                                <textarea id="sugAnswerText" rows="6" placeholder="Enter the full answer..."></textarea>
                            </div>
                            <button class="btn btn-success" id="sugSaveBtn" onclick="sugSave()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                Save Suggestion
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ======================== TAB 3: PREVIOUS YEAR QUESTIONS ======================== -->
                <div class="tab-panel" id="tabPYQ" role="tabpanel">
                    <!-- Section: Manage Exam Categories -->
                    <div class="card" style="margin-bottom:24px;">
                        <div class="card-header">
                            <h3>Exam Categories</h3>
                            <button class="btn btn-primary btn-sm" onclick="pyqShowAddCategory()">+ Add Category</button>
                        </div>
                        <!-- Category Input Form -->
                        <div id="pyqCategoryForm" style="display:none; margin-bottom:16px; padding:16px; background:#f9fafb; border-radius:10px;">
                            <div class="form-row">
                                <div class="form-group" style="flex:2;">
                                    <label>Category Title</label>
                                    <input type="text" id="pyqCatTitle" placeholder="e.g. UPSC, JEE, NEET, WBJEE...">
                                </div>
                                <div class="form-group" style="flex:3;">
                                    <label>Description</label>
                                    <input type="text" id="pyqCatDesc" placeholder="Brief description...">
                                </div>
                                <div class="form-group" style="flex:0 0 auto; display:flex; align-items:flex-end;">
                                    <button class="btn btn-success" onclick="pyqSaveCategory()">Save</button>
                                    <button class="btn btn-secondary" style="margin-left:8px;" onclick="pyqHideCategoryForm()">Cancel</button>
                                </div>
                            </div>
                        </div>
                        <!-- Category Chips -->
                        <div id="pyqCategoryList" class="category-list">
                            <div class="empty-state" style="padding:16px;">
                                <p style="font-size:13px;">No categories yet. Add one above.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Upload / View PYQs -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Previous Year Question Papers</h3>
                        </div>
                        <!-- Selection + Upload -->
                        <div style="padding:16px; background:#f9fafb; border-radius:10px; margin-bottom:20px;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Exam Category</label>
                                    <select id="pyqCategorySelect" onchange="pyqLoadYears()">
                                        <option value="">Select Category</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Year</label>
                                    <select id="pyqYearSelect" disabled onchange="pyqLoadQuestions()">
                                        <option value="">Select Category First</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Subject</label>
                                    <select id="pyqSubjectSelect">
                                        <option value="">Select Subject</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:0 0 auto; min-width:140px;">
                                    <label>Upload PDF</label>
                                    <input type="file" id="pyqPdfFile" accept=".pdf" style="font-size:13px; padding:8px;">
                                </div>
                                <div class="form-group" style="flex:0 0 auto; display:flex; align-items:flex-end;">
                                    <button class="btn btn-primary" id="pyqUploadBtn" onclick="pyqUpload()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg>
                                        Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- PYQ Table -->
                        <div id="pyqQuestionsContainer">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                <h4>Select Category &amp; Year</h4>
                                <p>Uploaded question papers will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="toast-container" id="toastContainer"></div>

    <script src="js/api.js"></script>
    <script src="js/firebase-config.js"></script>
    <script src="js/firebase-web.js"></script>
    <script>
        // ===== SIDEBAR TOGGLE =====
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); }
        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // ===== TAB SYSTEM =====
        document.querySelectorAll('.tabs-nav button').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tabs-nav button').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });

        // ===== TOAST HELPER =====
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            const icons = {
                success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
                error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
                info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
            };
            toast.innerHTML = icons[type] || icons.info;
            toast.innerHTML += message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // ===== LOADING BUTTON HELPER =====
        function setLoading(btnId, loading) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            if (loading) {
                btn.dataset.origHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner"></span> Saving...';
                btn.classList.add('btn-loading');
            } else {
                btn.innerHTML = btn.dataset.origHtml || btn.innerHTML;
                btn.classList.remove('btn-loading');
            }
        }

        // ================================================================
        //  PART 1: QUESTIONS & ANSWERS
        // ================================================================
        let qaEditingId = null;

        // Load classes for both Q&A and Suggestions
        async function loadClasses(selectId) {
            try {
                const res = await api.post('get-class.php');
                const sel = document.getElementById(selectId);
                sel.innerHTML = '<option value="">Select Class</option>';
                if (res.status && res.data) {
                    res.data.forEach(c => {
                        sel.innerHTML += `<option value="${c.id}">Class ${c.class_name}</option>`;
                    });
                }
            } catch(e) {
                showToast('Failed to load classes', 'error');
            }
        }

        // Q&A: Load subjects
        async function qaLoadSubjects() {
            const classId = document.getElementById('qaClass').value;
            const subjSel = document.getElementById('qaSubject');
            const chapSel = document.getElementById('qaChapter');
            const typeSel = document.getElementById('qaType');
            subjSel.disabled = true;
            chapSel.disabled = true;
            typeSel.disabled = true;
            subjSel.innerHTML = '<option value="">Select Class First</option>';
            chapSel.innerHTML = '<option value="">Select Subject First</option>';
            typeSel.innerHTML = '<option value="">Select Chapter First</option>';
            qaClearQuestions();
            if (!classId) return;
            try {
                const res = await api.post('get-subject.php', { class_id: parseInt(classId) });
                subjSel.innerHTML = '<option value="">Select Subject</option>';
                if (res.status && res.data) {
                    res.data.forEach(s => subjSel.innerHTML += `<option value="${s.id}">${s.subject_name}</option>`);
                }
                subjSel.disabled = false;
            } catch(e) {
                showToast('Failed to load subjects', 'error');
            }
        }

        // Q&A: Load chapters
        async function qaLoadChapters() {
            const subjectId = document.getElementById('qaSubject').value;
            const chapSel = document.getElementById('qaChapter');
            const typeSel = document.getElementById('qaType');
            chapSel.disabled = true;
            typeSel.disabled = true;
            chapSel.innerHTML = '<option value="">Select Subject First</option>';
            typeSel.innerHTML = '<option value="">Select Chapter First</option>';
            qaClearQuestions();
            if (!subjectId) return;
            try {
                const res = await api.post('get-chapter.php', { subject_id: parseInt(subjectId) });
                chapSel.innerHTML = '<option value="">Select Chapter</option>';
                if (res.status && res.data) {
                    res.data.forEach(ch => chapSel.innerHTML += `<option value="${ch.id}">${ch.chapter_name}</option>`);
                }
                chapSel.disabled = false;
            } catch(e) {
                showToast('Failed to load chapters', 'error');
            }
        }

        // Q&A: Load question types
        async function qaLoadQuestionTypes() {
            const chapterId = document.getElementById('qaChapter').value;
            const typeSel = document.getElementById('qaType');
            typeSel.disabled = true;
            typeSel.innerHTML = '<option value="">Select Chapter First</option>';
            qaClearQuestions();
            if (!chapterId) return;
            try {
                const res = await api.post('get-solution-question-type.php');
                typeSel.innerHTML = '<option value="">Select Question Type</option>';
                if (res.status && res.data) {
                    res.data.forEach(t => typeSel.innerHTML += `<option value="${t.id}">${t.type_name}</option>`);
                }
                typeSel.disabled = false;
            } catch(e) {
                showToast('Failed to load question types', 'error');
            }
        }

        // Q&A: Load questions
        async function qaLoadQuestions() {
            const chapterId = document.getElementById('qaChapter').value;
            const typeId = document.getElementById('qaType').value;
            qaClearQuestions();
            if (!chapterId || !typeId) return;
            try {
                const res = await api.post('get-solution-question.php', {
                    chapter_id: parseInt(chapterId),
                    question_type_id: parseInt(typeId)
                });
                const container = document.getElementById('qaQuestionsContainer');
                if (!res.status || !res.data || res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            <h4>No Questions Found</h4>
                            <p>Click "+ Add Question" to add the first question.</p>
                        </div>`;
                    return;
                }
                let html = `<div class="table-wrap"><table>
                    <thead><tr>
                        <th style="width:40px;">#</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th style="width:100px;">Type</th>
                        <th style="width:100px;">Actions</th>
                    </tr></thead><tbody>`;
                res.data.forEach((q, i) => {
                    const answerPreview = q.answer.length > 120 ? q.answer.substring(0, 120) + '...' : q.answer;
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td><strong>${escapeHtml(q.question)}</strong></td>
                        <td><div class="answer-box">${escapeHtml(answerPreview)}</div></td>
                        <td><span style="background:#eef2ff;color:#4f46e5;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:500;">${escapeHtml(q.type_name)}</span></td>
                        <td>
                            <button class="btn btn-warning-sm btn-sm" onclick="qaEditQuestion(${q.id}, '${escapeJs(q.question)}', '${escapeJs(q.answer)}')">Edit</button>
                            <button class="btn btn-danger-sm btn-sm" onclick="qaDeleteQuestion(${q.id})" style="margin-top:4px;">Delete</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } catch(e) {
                showToast('Failed to load questions', 'error');
            }
        }

        function qaClearQuestions() {
            document.getElementById('qaQuestionsContainer').innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <h4>Select Class, Subject, Chapter &amp; Question Type</h4>
                    <p>Questions will appear here once you make your selection.</p>
                </div>`;
            qaHideForm();
        }

        function qaShowAddForm() {
            const chapterId = document.getElementById('qaChapter').value;
            const typeId = document.getElementById('qaType').value;
            if (!chapterId || !typeId) {
                showToast('Please select a chapter and question type first', 'error');
                return;
            }
            qaEditingId = null;
            document.getElementById('qaFormTitle').textContent = 'Add New Question';
            document.getElementById('qaQuestionText').value = '';
            document.getElementById('qaAnswerText').value = '';
            document.getElementById('qaForm').style.display = 'block';
            document.getElementById('qaQuestionText').focus();
        }

        function qaHideForm() {
            document.getElementById('qaForm').style.display = 'none';
            qaEditingId = null;
        }

        function qaEditQuestion(id, question, answer) {
            qaEditingId = id;
            document.getElementById('qaFormTitle').textContent = 'Edit Question';
            document.getElementById('qaQuestionText').value = decodeHtml(question);
            document.getElementById('qaAnswerText').value = decodeHtml(answer);
            document.getElementById('qaForm').style.display = 'block';
            document.getElementById('qaQuestionText').focus();
        }

        async function qaSaveQuestion() {
            const chapterId = parseInt(document.getElementById('qaChapter').value);
            const typeId = parseInt(document.getElementById('qaType').value);
            const question = document.getElementById('qaQuestionText').value.trim();
            const answer = document.getElementById('qaAnswerText').value.trim();

            if (!question || !answer) {
                showToast('Please fill in both question and answer', 'error');
                return;
            }

            const btn = document.getElementById('qaSaveBtn');
            setLoading('qaSaveBtn', true);

            try {
                if (qaEditingId) {
                    // Update
                    const res = await api.post('update-solution-questions.php', {
                        solution_id: qaEditingId,
                        chapter_id: chapterId,
                        question_type_id: typeId,
                        question: question,
                        answer: answer
                    });
                    if (res.status) {
                        showToast('Question updated successfully!', 'success');
                        qaHideForm();
                        qaLoadQuestions();
                    } else {
                        showToast(res.message || 'Failed to update question', 'error');
                    }
                } else {
                    // Add
                    const res = await api.post('add-solution-questions.php', {
                        chapter_id: chapterId,
                        question_type_id: typeId,
                        question: question,
                        answer: answer
                    });
                    if (res.status) {
                        showToast('Question added successfully!', 'success');
                        qaHideForm();
                        qaLoadQuestions();
                    } else {
                        showToast(res.message || 'Failed to add question', 'error');
                    }
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            } finally {
                setLoading('qaSaveBtn', false);
            }
        }

        async function qaDeleteQuestion(id) {
            if (!confirm('Are you sure you want to delete this question?')) return;
            try {
                const res = await api.post('delete-solution-question.php', { solution_id: id });
                if (res.status) {
                    showToast('Question deleted successfully!', 'success');
                    qaLoadQuestions();
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            }
        }

        // ================================================================
        //  PART 2: SUGGESTIONS
        // ================================================================
        let sugEditingId = null;

        async function sugLoadSubjects() {
            const classId = document.getElementById('sugClass').value;
            const subjSel = document.getElementById('sugSubject');
            subjSel.disabled = true;
            subjSel.innerHTML = '<option value="">Select Class First</option>';
            sugClear();
            if (!classId) return;
            try {
                const res = await api.post('get-subject.php', { class_id: parseInt(classId) });
                subjSel.innerHTML = '<option value="">Select Subject</option>';
                if (res.status && res.data) {
                    res.data.forEach(s => subjSel.innerHTML += `<option value="${s.id}">${s.subject_name}</option>`);
                }
                subjSel.disabled = false;
            } catch(e) {
                showToast('Failed to load subjects', 'error');
            }
        }

        async function sugLoadSuggestions() {
            const subjectId = document.getElementById('sugSubject').value;
            sugClear();
            if (!subjectId) return;
            try {
                const res = await api.post('get-solution-suggestion.php', { subject_id: parseInt(subjectId) });
                const container = document.getElementById('sugContainer');
                if (!res.status || !res.data || res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            <h4>No Suggestions Yet</h4>
                            <p>Click "+ Add Suggestion" to add one.</p>
                        </div>`;
                    return;
                }
                let html = `<div class="table-wrap"><table>
                    <thead><tr>
                        <th style="width:40px;">#</th>
                        <th>Title</th>
                        <th>Answer</th>
                        <th style="width:110px;">Actions</th>
                    </tr></thead><tbody>`;
                res.data.forEach((s, i) => {
                    const answerPreview = s.answer.length > 150 ? s.answer.substring(0, 150) + '...' : s.answer;
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td><strong>${escapeHtml(s.title)}</strong></td>
                        <td><div class="answer-box">${escapeHtml(answerPreview)}</div></td>
                        <td>
                            <button class="btn btn-warning-sm btn-sm" onclick="sugEdit(${s.id}, '${escapeJs(s.title)}', '${escapeJs(s.description || '')}', '${escapeJs(s.answer)}')">Edit</button>
                            <button class="btn btn-danger-sm btn-sm" onclick="sugDelete(${s.id})" style="margin-top:4px;">Delete</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } catch(e) {
                showToast('Failed to load suggestions', 'error');
            }
        }

        function sugClear() {
            document.getElementById('sugContainer').innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    <h4>Select a Subject</h4>
                    <p>Suggestions will appear here.</p>
                </div>`;
            sugHideForm();
        }

        function sugShowAddForm() {
            const subjectId = document.getElementById('sugSubject').value;
            if (!subjectId) {
                showToast('Please select a subject first', 'error');
                return;
            }
            sugEditingId = null;
            document.getElementById('sugFormTitle').textContent = 'Add New Suggestion';
            document.getElementById('sugTitle').value = '';
            document.getElementById('sugDescription').value = '';
            document.getElementById('sugAnswerText').value = '';
            document.getElementById('sugForm').style.display = 'block';
            document.getElementById('sugTitle').focus();
        }

        function sugHideForm() {
            document.getElementById('sugForm').style.display = 'none';
            sugEditingId = null;
        }

        function sugEdit(id, title, desc, answer) {
            sugEditingId = id;
            document.getElementById('sugFormTitle').textContent = 'Edit Suggestion';
            document.getElementById('sugTitle').value = decodeHtml(title);
            document.getElementById('sugDescription').value = decodeHtml(desc);
            document.getElementById('sugAnswerText').value = decodeHtml(answer);
            document.getElementById('sugForm').style.display = 'block';
            document.getElementById('sugTitle').focus();
        }

        async function sugSave() {
            const subjectId = parseInt(document.getElementById('sugSubject').value);
            const title = document.getElementById('sugTitle').value.trim();
            const description = document.getElementById('sugDescription').value.trim();
            const answer = document.getElementById('sugAnswerText').value.trim();

            if (!title || !answer) {
                showToast('Please fill in title and answer', 'error');
                return;
            }

            setLoading('sugSaveBtn', true);

            try {
                if (sugEditingId) {
                    const res = await api.post('update-solution-suggestion.php', {
                        suggestion_id: sugEditingId,
                        title: title,
                        description: description,
                        answer: answer
                    });
                    if (res.status) {
                        showToast('Suggestion updated successfully!', 'success');
                        sugHideForm();
                        sugLoadSuggestions();
                    } else {
                        showToast(res.message || 'Failed to update', 'error');
                    }
                } else {
                    const res = await api.post('add-solution-suggestion.php', {
                        subject_id: subjectId,
                        title: title,
                        description: description,
                        answer: answer
                    });
                    if (res.status) {
                        showToast('Suggestion added successfully!', 'success');
                        sugHideForm();
                        sugLoadSuggestions();
                    } else {
                        showToast(res.message || 'Failed to add', 'error');
                    }
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            } finally {
                setLoading('sugSaveBtn', false);
            }
        }

        async function sugDelete(id) {
            if (!confirm('Are you sure you want to delete this suggestion?')) return;
            try {
                const res = await api.post('delete-solution-suggestion.php', { suggestion_id: id });
                if (res.status) {
                    showToast('Suggestion deleted successfully!', 'success');
                    sugLoadSuggestions();
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            }
        }

        // ================================================================
        //  PART 3: PREVIOUS YEAR QUESTIONS
        // ================================================================
        let pyqEditingCatId = null;

        // --- Exam Category CRUD ---

        async function pyqLoadCategories() {
            // Load into both the chip list and the select dropdown
            try {
                const res = await api.post('get-exam-category.php');
                const chipList = document.getElementById('pyqCategoryList');
                const select = document.getElementById('pyqCategorySelect');

                if (!res.status || !res.data || res.data.length === 0) {
                    chipList.innerHTML = `<div class="empty-state" style="padding:16px;"><p style="font-size:13px;">No categories yet. Add one above.</p></div>`;
                    select.innerHTML = '<option value="">Select Category</option>';
                    document.getElementById('pyqYearSelect').innerHTML = '<option value="">No Categories</option>';
                    return;
                }

                // Chips
                chipList.innerHTML = '';
                res.data.forEach(c => {
                    chipList.innerHTML += `
                        <span class="category-chip">
                            ${escapeHtml(c.title)}
                            <button class="chip-del" onclick="pyqDeleteCategory(${c.id})" title="Delete">&times;</button>
                        </span>`;
                });

                // Select dropdown
                select.innerHTML = '<option value="">Select Category</option>';
                res.data.forEach(c => {
                    select.innerHTML += `<option value="${c.id}">${escapeHtml(c.title)}</option>`;
                });

                // Populate year select
                pyqLoadYears();
            } catch(e) {
                showToast('Failed to load categories', 'error');
            }
        }

        function pyqShowAddCategory() {
            pyqEditingCatId = null;
            document.getElementById('pyqCatTitle').value = '';
            document.getElementById('pyqCatDesc').value = '';
            document.getElementById('pyqCategoryForm').style.display = 'block';
            document.getElementById('pyqCatTitle').focus();
        }

        function pyqHideCategoryForm() {
            document.getElementById('pyqCategoryForm').style.display = 'none';
            pyqEditingCatId = null;
        }

        async function pyqSaveCategory() {
            const title = document.getElementById('pyqCatTitle').value.trim();
            const description = document.getElementById('pyqCatDesc').value.trim();

            if (!title) {
                showToast('Please enter a category title', 'error');
                return;
            }

            try {
                let res;
                if (pyqEditingCatId) {
                    res = await api.post('update-exam-category.php', {
                        category_id: pyqEditingCatId,
                        title: title,
                        description: description
                    });
                } else {
                    res = await api.post('add-exam-category.php', {
                        title: title,
                        description: description
                    });
                }

                if (res.status) {
                    showToast(pyqEditingCatId ? 'Category updated!' : 'Category added!', 'success');
                    pyqHideCategoryForm();
                    pyqLoadCategories();
                } else {
                    showToast(res.message || 'Failed to save category', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            }
        }

        async function pyqDeleteCategory(id) {
            if (!confirm('Delete this category? Related question papers will also be deleted.')) return;
            try {
                const res = await api.post('delete-exam-category.php', { category_id: id });
                if (res.status) {
                    showToast('Category deleted!', 'success');
                    pyqLoadCategories();
                    pyqClearQuestions();
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            }
        }

        // --- Year generation ---
        function pyqLoadYears() {
            const sel = document.getElementById('pyqYearSelect');
            sel.disabled = true;
            sel.innerHTML = '<option value="">Select Category First</option>';
            pyqClearQuestions();

            const catId = document.getElementById('pyqCategorySelect').value;
            if (!catId) return;

            // Generate years from 2010 to current year
            const currentYear = new Date().getFullYear();
            sel.innerHTML = '<option value="">Select Year</option>';
            for (let y = currentYear; y >= 2010; y--) {
                sel.innerHTML += `<option value="${y}">${y}</option>`;
            }
            sel.disabled = false;
        }

        // --- Load subjects for PYQ ---
        async function pyqLoadSubjects() {
            const sel = document.getElementById('pyqSubjectSelect');
            sel.innerHTML = '<option value="">Loading...</option>';
            try {
                // Fetch all classes first
                const classRes = await api.post('get-class.php');
                if (!classRes.status || !classRes.data) {
                    sel.innerHTML = '<option value="">Select Subject</option>';
                    return;
                }
                // Get subjects for all classes
                let allSubjects = [];
                for (const cls of classRes.data) {
                    const subRes = await api.post('get-subject.php', { class_id: cls.id });
                    if (subRes.status && subRes.data) {
                        allSubjects = allSubjects.concat(subRes.data.map(s => ({ ...s, class_name: cls.class_name })));
                    }
                }
                sel.innerHTML = '<option value="">Select Subject</option>';
                allSubjects.forEach(s => {
                    sel.innerHTML += `<option value="${s.id}">${s.subject_name} (Class ${s.class_name})</option>`;
                });
            } catch(e) {
                sel.innerHTML = '<option value="">Select Subject</option>';
            }
        }

        // --- Upload PYQ ---
        async function pyqUpload() {
            const categoryId = document.getElementById('pyqCategorySelect').value;
            const year = document.getElementById('pyqYearSelect').value;
            const subjectId = document.getElementById('pyqSubjectSelect').value;
            const fileInput = document.getElementById('pyqPdfFile');

            if (!categoryId || !year || !subjectId) {
                showToast('Please select category, year, and subject', 'error');
                return;
            }
            if (!fileInput.files || fileInput.files.length === 0) {
                showToast('Please select a PDF file', 'error');
                return;
            }

            const file = fileInput.files[0];
            if (file.type !== 'application/pdf') {
                showToast('Only PDF files are allowed', 'error');
                return;
            }
            if (file.size > 50 * 1024 * 1024) {
                showToast('File size exceeds 50MB limit', 'error');
                return;
            }

            setLoading('pyqUploadBtn', true);

            try {
                const formData = new FormData();
                formData.append('exam_category_id', categoryId);
                formData.append('year', year);
                formData.append('subject_id', subjectId);
                formData.append('pdf_file', file);

                const response = await fetch(API_BASE + 'add-previous-year-question.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.status) {
                    showToast('PDF uploaded successfully!', 'success');
                    fileInput.value = '';
                    pyqLoadQuestions();
                } else {
                    showToast(res.message || 'Failed to upload', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            } finally {
                setLoading('pyqUploadBtn', false);
            }
        }

        // --- Load PYQ list ---
        async function pyqLoadQuestions() {
            const categoryId = document.getElementById('pyqCategorySelect').value;
            const year = document.getElementById('pyqYearSelect').value;
            pyqClearQuestions();
            if (!categoryId || !year) return;

            try {
                const res = await api.post('get-previous-year-question.php', {
                    exam_category_id: parseInt(categoryId),
                    year: parseInt(year)
                });
                const container = document.getElementById('pyqQuestionsContainer');
                if (!res.status || !res.data || res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            <h4>No Papers Found</h4>
                            <p>Upload a PDF above to get started.</p>
                        </div>`;
                    return;
                }
                let html = `<div class="table-wrap"><table>
                    <thead><tr>
                        <th style="width:40px;">#</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Year</th>
                        <th>PDF</th>
                        <th style="width:80px;">Actions</th>
                    </tr></thead><tbody>`;
                res.data.forEach((q, i) => {
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td>${escapeHtml(q.subject_name)}</td>
                        <td>Class ${escapeHtml(q.class_name)}</td>
                        <td>${q.year}</td>
                        <td><a href="${q.pdf_url}" target="_blank" class="pdf-link">
                            <svg viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/></svg>
                            View PDF
                        </a></td>
                        <td><button class="btn btn-danger-sm btn-sm" onclick="pyqDeleteQuestion(${q.id})">Delete</button></td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } catch(e) {
                showToast('Failed to load questions', 'error');
            }
        }

        function pyqClearQuestions() {
            document.getElementById('pyqQuestionsContainer').innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    <h4>Select Category &amp; Year</h4>
                    <p>Uploaded question papers will appear here.</p>
                </div>`;
        }

        async function pyqDeleteQuestion(id) {
            if (!confirm('Delete this question paper?')) return;
            try {
                const res = await api.post('delete-previous-year-question.php', { pyq_id: id });
                if (res.status) {
                    showToast('Question paper deleted!', 'success');
                    pyqLoadQuestions();
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            } catch(e) {
                showToast('Network error: ' + e.message, 'error');
            }
        }

        // ===== UTILITY: HTML ESCAPE / UNESCAPE =====
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function decodeHtml(str) {
            if (!str) return '';
            const txt = document.createElement('textarea');
            txt.innerHTML = String(str);
            return txt.value;
        }

        // Escape string for use inside single-quoted JS onclick handler
        function escapeJs(str) {
            if (!str) return '';
            return String(str)
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/\n/g, '\\n')
                .replace(/\r/g, '\\r');
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function() {
            loadClasses('qaClass');
            loadClasses('sugClass');
            pyqLoadCategories();
            pyqLoadSubjects();
        });
    </script>
</body>
</html>
