<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once "../utils/api_config.php";
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - WB Admin</title>
    <style>
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
        .sidebar-footer a .nav-icon svg { width: 20px; height: 20px; fill: currentColor; }
        .main-content { margin-left: 270px; flex: 1; min-height: 100vh; }
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
        .page-content { padding: 32px; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 24px; font-weight: 700; color: #1a1a2e; }
        .page-header p { color: #6b7280; font-size: 14px; margin-top: 4px; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }

        /* ===== FCM STATUS CARD ===== */
        .fcm-card {
            background: #fff; border-radius: 14px; padding: 24px 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; flex-wrap: wrap;
            border: 1.5px solid #e5e7eb;
        }
        .fcm-card.configured { border-color: #bbf7d0; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); }
        .fcm-card.not-configured { border-color: #fecaca; background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); }
        .fcm-card .fcm-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .fcm-card .fcm-status-icon {
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .fcm-card .fcm-status-icon svg { width: 24px; height: 24px; fill: #fff; }
        .fcm-card .fcm-status-icon.ok { background: linear-gradient(135deg, #34d399, #059669); }
        .fcm-card .fcm-status-icon.warn { background: linear-gradient(135deg, #fb923c, #ea580c); }
        .fcm-card .fcm-title { font-size: 15px; font-weight: 700; color: #1f2937; }
        .fcm-card .fcm-sub {
            font-size: 13px; color: #6b7280; margin-top: 4px; line-height: 1.5;
            word-break: break-word;
        }
        .fcm-card .fcm-badges { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .fcm-badge {
            font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .fcm-badge.green { background: #d1fae5; color: #047857; }
        .fcm-badge.red { background: #fee2e2; color: #b91c1c; }
        .fcm-badge.blue { background: #dbeafe; color: #1d4ed8; }
        .fcm-badge.amber { background: #fef3c7; color: #b45309; }
        .fcm-card .fcm-actions { display: flex; gap: 10px; flex-shrink: 0; }

        /* ===== CREATE CARD ===== */
        .create-card {
            background: #fff; border-radius: 14px; padding: 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 28px;
        }
        .create-card h2 {
            font-size: 17px; font-weight: 700; color: #1a1a2e;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .create-card h2 svg { width: 20px; height: 20px; fill: #667eea; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-group input, .form-group textarea, .form-group select {
            padding: 11px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-size: 14px; font-family: inherit; color: #1f2937;
            transition: border-color 0.2s, box-shadow 0.2s; background: #fafafa;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: #667eea; background: #fff;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }
        .form-group textarea { resize: vertical; min-height: 84px; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 42px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; box-shadow: 0 4px 14px rgba(102,126,234,0.35);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(102,126,234,0.45); }
        .btn-ghost { background: #f3f4f6; color: #374151; }
        .btn-ghost:hover { background: #e5e7eb; }
        .btn-sm { padding: 6px 12px; font-size: 12px; min-height: 32px; border-radius: 8px; }
        .btn-icon { padding: 7px; min-height: 32px; border-radius: 8px; }

        /* ===== LIST ===== */
        .list-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;
        }
        .list-header h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; }
        .list-header .count-badge {
            background: #eef2ff; color: #4f46e5; font-size: 13px; font-weight: 600;
            padding: 4px 12px; border-radius: 20px;
        }
        .notif-list { display: flex; flex-direction: column; gap: 12px; }
        .notif-card {
            background: #fff; border-radius: 14px; padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            display: flex; align-items: flex-start; gap: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid #e5e7eb;
        }
        .notif-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .notif-card.type-test { border-left-color: #667eea; }
        .notif-card.type-solution { border-left-color: #10b981; }
        .notif-card.type-custom { border-left-color: #f59e0b; }
        .notif-card.inactive { opacity: 0.6; }
        .notif-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .notif-icon svg { width: 20px; height: 20px; fill: #fff; }
        .notif-icon.test { background: linear-gradient(135deg, #667eea, #764ba2); }
        .notif-icon.solution { background: linear-gradient(135deg, #34d399, #059669); }
        .notif-icon.custom { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .notif-body { flex: 1; min-width: 0; }
        .notif-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .notif-title { font-size: 15px; font-weight: 700; color: #1f2937; }
        .notif-type {
            font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
            padding: 2px 10px; border-radius: 20px;
        }
        .notif-type.test { background: #eef2ff; color: #4f46e5; }
        .notif-type.solution { background: #ecfdf5; color: #059669; }
        .notif-type.custom { background: #fffbeb; color: #d97706; }
        .notif-message { font-size: 13.5px; color: #6b7280; margin-top: 6px; line-height: 1.55; }
        .notif-time { font-size: 12px; color: #9ca3af; margin-top: 8px; display: flex; align-items: center; gap: 5px; }
        .notif-time svg { width: 14px; height: 14px; fill: #9ca3af; }
        .notif-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .status-toggle {
            width: 40px; height: 22px; border-radius: 20px; border: none; cursor: pointer;
            position: relative; transition: background 0.25s ease; flex-shrink: 0;
        }
        .status-toggle::after {
            content: ''; position: absolute; top: 3px; left: 3px;
            width: 16px; height: 16px; border-radius: 50%; background: #fff;
            transition: transform 0.25s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        }
        .status-toggle.on { background: #10b981; }
        .status-toggle.on::after { transform: translateX(18px); }
        .status-toggle.off { background: #d1d5db; }
        .empty-state {
            background: #fff; border-radius: 14px; padding: 56px 24px; text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .empty-state .empty-icon {
            width: 60px; height: 60px; margin: 0 auto 14px; background: #f0f2f5;
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
        }
        .empty-state .empty-icon svg { width: 30px; height: 30px; fill: #9ca3af; }
        .empty-state h3 { font-size: 16px; color: #374151; margin-bottom: 6px; }
        .empty-state p { color: #9ca3af; font-size: 13.5px; }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            z-index: 10000; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff; border-radius: 16px; width: 100%; max-width: 520px;
            padding: 28px; animation: modalIn 0.25s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes modalIn { from { opacity: 0; transform: translateY(20px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .modal h3 { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

        /* ===== LOADING SPINNER ===== */
        .spinner {
            display: inline-block; width: 18px; height: 18px;
            border: 2.5px solid #e2e8f0; border-top-color: #6366f1;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { pointer-events: none; opacity: 0.8; }

        /* ===== NOTIFICATION TOAST ===== */
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
        @keyframes toastIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }

        .btn, .sidebar-nav a, .sidebar-footer a { -webkit-tap-highlight-color: transparent; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 12px 16px; }
            .topbar .page-title { font-size: 17px; }
            .page-content { padding: 16px; }
            .page-header h1 { font-size: 22px; }
            .form-grid { grid-template-columns: 1fr; }
            .notif-card { flex-direction: column; }
            .notif-actions { width: 100%; justify-content: flex-end; }
        }
        @media (max-width: 480px) {
            .page-content { padding: 12px; }
            .topbar { padding: 10px 12px; }
            .topbar .page-title { font-size: 15px; }
            .page-header h1 { font-size: 20px; }
            .btn { font-size: 12px; padding: 7px 14px; min-height: 36px; }
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
            <a href="solutions.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>All Solution</a>
            <a href="notifications.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></span>Notifications</a>
            <div class="nav-label">Results</div>
            <a href="dailymockresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></span>Daily Mock Test Result</a>
            <a href="allmocktestresult.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>All Mock Test Result</a>
        </nav>
        <div class="sidebar-footer">
            <a href="login.php?logout=1" onclick="return confirm('Are you sure you want to logout?')">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span>Logout
            </a>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <h1 class="page-title">Notifications</h1>
            <div class="topbar-right">
                <div class="admin-info">
                    <div class="admin-avatar"><?= $adminInitial ?></div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1f2937;"><?= htmlspecialchars($adminName) ?></div>
                        <div style="font-size:12px;color:#6b7280;">Administrator</div>
                    </div>
                </div>
            </div>
        </header>
        <div class="page-content">
            <div class="page-header">
                <h1>Notifications</h1>
                <p>Create custom notifications and manage automated alerts sent to the app</p>
            </div>

            <!-- FCM Status Card -->
            <div class="fcm-card" id="fcmCard">
                <div class="fcm-left">
                    <div class="fcm-status-icon warn" id="fcmIcon">
                        <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <div>
                        <div class="fcm-title" id="fcmTitle">Checking FCM status...</div>
                        <div class="fcm-sub" id="fcmSub"></div>
                        <div class="fcm-badges" id="fcmBadges"></div>
                    </div>
                </div>
                <div class="fcm-actions">
                    <button class="btn btn-primary" onclick="sendTestPush()" id="testPushBtn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                        Send Test Push
                    </button>
                </div>
            </div>

            <!-- Create / Edit Card -->
            <div class="create-card">
                <h2>
                    <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                    <span id="formTitle">Create Notification</span>
                </h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="notifTitle">Title *</label>
                        <input type="text" id="notifTitle" placeholder="e.g. New Test Added" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="notifType">Type</label>
                        <select id="notifType">
                            <option value="custom">Custom</option>
                            <option value="test">Test</option>
                            <option value="solution">Solution</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="notifMessage">Message</label>
                        <textarea id="notifMessage" placeholder="Write the notification message shown in the app..."></textarea>
                    </div>
                    <div class="form-group full">
                        <button class="btn btn-primary" id="saveNotifBtn" onclick="saveNotification()">Send Notification</button>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="list-header">
                <h2>All Notifications</h2>
                <span class="count-badge" id="notifCount">0</span>
            </div>
            <div id="notifList"></div>
        </div>
    </main>

    <!-- Test Push Modal -->
    <div class="modal-overlay" id="testPushModal">
        <div class="modal">
            <h3>Send Test Push</h3>
            <p style="font-size:13px;color:#6b7280;margin:-10px 0 18px;">Sends a push to all registered devices — no notification is saved.</p>
            <div class="form-grid" style="grid-template-columns:1fr;">
                <div class="form-group">
                    <label for="testTitle">Title</label>
                    <input type="text" id="testTitle" value="Test Push" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="testMessage">Message</label>
                    <textarea id="testMessage">This is a test push from the admin panel.</textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-ghost" onclick="closeTestModal()">Cancel</button>
                <button class="btn btn-primary" id="testSendBtn" onclick="doTestPush()">Send Test Push</button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <h3>Edit Notification</h3>
            <div class="form-grid" style="grid-template-columns:1fr;">
                <div class="form-group">
                    <label for="editTitle">Title *</label>
                    <input type="text" id="editTitle" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="editType">Type</label>
                    <select id="editType">
                        <option value="custom">Custom</option>
                        <option value="test">Test</option>
                        <option value="solution">Solution</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editMessage">Message</label>
                    <textarea id="editMessage"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" id="updateBtn" onclick="updateNotification()">Save Changes</button>
            </div>
        </div>
    </div>

    <script src="js/api.js"></script>
    <script src="js/firebase-config.js"></script>
    <script src="js/firebase-web.js"></script>
    <script>
        // Browser push status callback (from firebase-web.js)
        function applyBrowserStatus(status) {
            const badge = document.getElementById('browserBadge');
            if (!badge) return;
            if (status.registered) {
                badge.className = 'fcm-badge green';
                badge.textContent = '● This browser: registered';
            } else {
                const reason = status.reason === 'no-vapid-key'
                    ? 'set VAPID key in js/firebase-config.js'
                    : (status.reason === 'permission-denied' ? 'permission denied' : 'not registered');
                badge.className = 'fcm-badge amber';
                badge.textContent = '● This browser: ' + reason;
            }
        }

        window.FCMWebStatusCallback = applyBrowserStatus;
        // firebase-web.js may have already finished before this script ran —
        // read its stored status if present
        if (window.FCMWebStatus) {
            applyBrowserStatus(window.FCMWebStatus);
        }

        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); }
        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        let editingId = null;

        // ===== FCM STATUS =====
        async function loadFcmStatus() {
            try {
                const res = await api.post('fcm-status.php');
                if (!res.status || !res.data) {
                    renderFcmStatus(false, null, 0);
                    return;
                }
                renderFcmStatus(res.data.configured, res.data.project_id, res.data.total_devices || 0, res.data.client_email);
            } catch (err) {
                renderFcmStatus(false, null, 0);
            }
        }

        function renderFcmStatus(configured, projectId, deviceCount, clientEmail) {
            const card = document.getElementById('fcmCard');
            const icon = document.getElementById('fcmIcon');
            const title = document.getElementById('fcmTitle');
            const sub = document.getElementById('fcmSub');
            const badges = document.getElementById('fcmBadges');

            card.classList.remove('configured', 'not-configured');
            icon.className = 'fcm-status-icon ' + (configured ? 'ok' : 'warn');

            if (configured) {
                card.classList.add('configured');
                title.textContent = 'FCM connected ✓';
                sub.textContent = 'Server can send push notifications.';
                badges.innerHTML =
                    '<span class="fcm-badge green">● Connected</span>' +
                    (projectId ? '<span class="fcm-badge blue">' + escapeHtml(projectId) + '</span>' : '') +
                    '<span class="fcm-badge ' + (deviceCount > 0 ? 'green' : 'amber') + '">' + deviceCount + ' device' + (deviceCount === 1 ? '' : 's') + ' registered</span>' +
                    '<span class="fcm-badge amber" id="browserBadge">● This browser: waiting...</span>';
            } else {
                card.classList.add('not-configured');
                title.textContent = 'FCM not configured';
                sub.textContent = 'Upload google_service.json (service account key) to the project root (/wb-admin/). In-app notifications still work without it.';
                badges.innerHTML =
                    '<span class="fcm-badge red">● Missing key</span>' +
                    '<span class="fcm-badge ' + (deviceCount > 0 ? 'amber' : 'blue') + '">' + deviceCount + ' device' + (deviceCount === 1 ? '' : 's') + ' registered</span>' +
                    '<span class="fcm-badge amber" id="browserBadge">● This browser: waiting...</span>';
            }
        }

        // ===== TEST PUSH =====
        function sendTestPush() {
            document.getElementById('testPushModal').classList.add('active');
        }

        function closeTestModal() {
            document.getElementById('testPushModal').classList.remove('active');
        }

        async function doTestPush() {
            const title = document.getElementById('testTitle').value.trim() || 'Test Push';
            const message = document.getElementById('testMessage').value.trim();
            const btn = document.getElementById('testSendBtn');
            setBtnLoading(btn, true, 'Send Test Push');
            try {
                const res = await api.post('test-push.php', { title, message, type: 'custom' });
                if (res.status) {
                    showToast('Test push delivered: ' + (res.data ? res.data.sent : 0) + ' sent, ' + (res.data ? res.data.failed : 0) + ' failed', 'success');
                    closeTestModal();
                    loadFcmStatus();
                } else {
                    showToast(res.message || res.data?.detail || 'Test push failed', 'error');
                }
            } catch (err) {
                showToast('Network error: ' + err.message, 'error');
            } finally {
                setBtnLoading(btn, false, 'Send Test Push');
            }
        }

        const typeMeta = {
            test: { label: 'Test', icon: '<svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>' },
            solution: { label: 'Solution', icon: '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>' },
            custom: { label: 'Custom', icon: '<svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>' }
        };

        function showToast(message, type = 'info') {
            const existing = document.querySelector('.toast-container');
            if (!existing) {
                const container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            const container = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d)) return dateStr;
            return d.toLocaleString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        }

        async function loadNotifications() {
            const listEl = document.getElementById('notifList');
            listEl.innerHTML = '<div class="empty-state"><span class="spinner"></span><p style="margin-top:12px;">Loading notifications...</p></div>';
            try {
                const res = await api.post('get-notifications.php');
                if (!res.status) {
                    listEl.innerHTML = '<div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></div><h3>Error loading notifications</h3><p>' + (res.message || 'Please try again.') + '</p></div>';
                    return;
                }
                const items = res.data || [];
                document.getElementById('notifCount').textContent = items.length;
                if (items.length === 0) {
                    listEl.innerHTML = '<div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></div><h3>No notifications yet</h3><p>Create one using the form above.</p></div>';
                    return;
                }
                listEl.innerHTML = '<div class="notif-list">' + items.map(item => {
                    const type = typeMeta[item.type] || typeMeta.custom;
                    const active = parseInt(item.is_active) === 1;
                    return `
                        <div class="notif-card type-${item.type} ${active ? '' : 'inactive'}" data-id="${item.id}">
                            <div class="notif-icon ${item.type}">${type.icon}</div>
                            <div class="notif-body">
                                <div class="notif-title-row">
                                    <span class="notif-title">${escapeHtml(item.title)}</span>
                                    <span class="notif-type ${item.type}">${type.label}</span>
                                </div>
                                ${item.message ? `<p class="notif-message">${escapeHtml(item.message)}</p>` : ''}
                                <div class="notif-time">
                                    <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.3l4.5 2.7-.8 1.2z"/></svg>
                                    ${formatDate(item.created_at)}
                                </div>
                            </div>
                            <div class="notif-actions">
                                <button class="status-toggle ${active ? 'on' : 'off'}" title="${active ? 'Active' : 'Inactive'}" onclick="toggleNotification(${item.id}, ${active ? 0 : 1})"></button>
                                <button class="btn btn-ghost btn-icon" title="Edit" onclick="openEditModal(${item.id})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-icon" title="Delete" onclick="deleteNotification(${item.id})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="#dc2626"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                            </div>
                        </div>`;
                }).join('') + '</div>';
            } catch (err) {
                listEl.innerHTML = '<div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></div><h3>Error loading notifications</h3><p>' + err.message + '</p></div>';
            }
        }

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function setBtnLoading(btn, loading, text) {
            if (loading) {
                btn.dataset.original = btn.innerHTML;
                btn.innerHTML = '<span class="spinner"></span> Saving...';
                btn.classList.add('btn-loading');
            } else {
                btn.innerHTML = btn.dataset.original || text;
                btn.classList.remove('btn-loading');
            }
        }

        async function saveNotification() {
            const title = document.getElementById('notifTitle').value.trim();
            const message = document.getElementById('notifMessage').value.trim();
            const type = document.getElementById('notifType').value;
            if (!title) {
                showToast('Please enter a title', 'error');
                return;
            }
            const btn = document.getElementById('saveNotifBtn');
            setBtnLoading(btn, true, 'Send Notification');
            try {
                const res = await api.post('add-notification.php', { title, message, type });
                if (res.status) {
                    showToast('Notification sent to all users!', 'success');
                    document.getElementById('notifTitle').value = '';
                    document.getElementById('notifMessage').value = '';
                    document.getElementById('notifType').value = 'custom';
                    loadNotifications();
                } else {
                    showToast(res.message || 'Failed to send notification', 'error');
                }
            } catch (err) {
                showToast('Network error: ' + err.message, 'error');
            } finally {
                setBtnLoading(btn, false, 'Send Notification');
            }
        }

        function openEditModal(id) {
            const items = document.querySelectorAll('.notif-card');
            let target = null;
            items.forEach(el => { if (el.dataset.id === String(id)) target = el; });
            if (!target) return;
            const title = target.querySelector('.notif-title').textContent;
            const type = target.classList.contains('type-test') ? 'test' : (target.classList.contains('type-solution') ? 'solution' : 'custom');
            const message = target.querySelector('.notif-message') ? target.querySelector('.notif-message').textContent : '';
            editingId = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editType').value = type;
            document.getElementById('editMessage').value = message;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
            editingId = null;
        }

        async function updateNotification() {
            const title = document.getElementById('editTitle').value.trim();
            const message = document.getElementById('editMessage').value.trim();
            const type = document.getElementById('editType').value;
            if (!title || !editingId) {
                showToast('Please enter a title', 'error');
                return;
            }
            const btn = document.getElementById('updateBtn');
            setBtnLoading(btn, true, 'Save Changes');
            try {
                const res = await api.post('update-notification.php', { notification_id: editingId, title, message, type });
                if (res.status) {
                    showToast('Notification updated', 'success');
                    closeModal();
                    loadNotifications();
                } else {
                    showToast(res.message || 'Failed to update', 'error');
                }
            } catch (err) {
                showToast('Network error: ' + err.message, 'error');
            } finally {
                setBtnLoading(btn, false, 'Save Changes');
            }
        }

        async function toggleNotification(id, newState) {
            try {
                const res = await api.post('update-notification.php', { notification_id: id, is_active: newState });
                if (res.status) {
                    showToast(newState === 1 ? 'Notification activated' : 'Notification hidden from app', 'success');
                    loadNotifications();
                } else {
                    showToast(res.message || 'Failed to update status', 'error');
                }
            } catch (err) {
                showToast('Network error: ' + err.message, 'error');
            }
        }

        async function deleteNotification(id) {
            if (!confirm('Delete this notification?')) return;
            try {
                const res = await api.post('delete-notification.php', { notification_id: id });
                if (res.status) {
                    showToast('Notification deleted', 'success');
                    loadNotifications();
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            } catch (err) {
                showToast('Network error: ' + err.message, 'error');
            }
        }

        loadNotifications();
        loadFcmStatus();
    </script>
</body>
</html>
