<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . "/../utils/api_config.php";
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WB Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon svg {
            width: 22px;
            height: 22px;
            fill: #fff;
        }

        .sidebar-brand .brand-text {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .sidebar-brand .brand-text span {
            color: #667eea;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .sidebar-nav .nav-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, 0.35);
            padding: 12px 12px 8px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
            position: relative;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .sidebar-nav a .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-nav a .nav-icon svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .sidebar-nav a .nav-arrow {
            margin-left: auto;
            font-size: 12px;
            opacity: 0.4;
        }

        .sidebar-nav a .badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .sidebar-nav a.active .badge {
            background: rgba(255, 255, 255, 0.25);
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-footer a:hover {
            background: rgba(255, 50, 50, 0.12);
            color: #ff6b6b;
        }

        .sidebar-footer a .nav-icon svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 270px;
            flex: 1;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar .menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #374151;
        }

        .topbar .menu-toggle svg {
            width: 24px;
            height: 24px;
            fill: currentColor;
        }

        .topbar .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar .topbar-right .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar .topbar-right .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        .topbar .topbar-right .admin-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .topbar .topbar-right .admin-role {
            font-size: 12px;
            color: #6b7280;
        }

        .content-area {
            padding: 32px;
        }

        .content-area .welcome-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            padding: 36px;
            color: #fff;
            margin-bottom: 32px;
        }

        .content-area .welcome-card h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .content-area .welcome-card p {
            opacity: 0.85;
            font-size: 15px;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-card .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card .stat-icon svg {
            width: 22px;
            height: 22px;
            fill: #fff;
        }

        .stat-card .stat-icon.purple {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-card .stat-icon.green {
            background: linear-gradient(135deg, #34d399, #059669);
        }

        .stat-card .stat-icon.orange {
            background: linear-gradient(135deg, #fb923c, #ea580c);
        }

        .stat-card .stat-icon.blue {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .stat-card .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        /* ===== SIDEBAR OVERLAY (mobile) ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* ===== LOADING SPINNER ===== */
        .spinner {
            display: inline-block; width: 18px; height: 18px;
            border: 2.5px solid #e2e8f0; border-top-color: #6366f1;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { pointer-events: none; opacity: 0.8; }
        .btn-loading .spinner { margin-right: 6px; }

        /* ===== NOTIFICATION TOAST ===== */
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 99999;
            display: flex; flex-direction: column; gap: 8px;
            max-width: 360px; width: calc(100% - 40px);
        }
        .toast {
            padding: 14px 18px; border-radius: 12px;
            font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
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

        /* ===== TOUCH-FRIENDLY ===== */
        .btn, .sidebar-nav a, .sidebar-footer a { -webkit-tap-highlight-color: transparent; }
        .btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-overlay { transition: opacity 0.3s ease; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 12px 16px; }
            .topbar .page-title { font-size: 17px; }
            .content-area { padding: 16px; }
            .page-header h1 { font-size: 22px; }
            .page-header p { font-size: 14px; }
            .btn { font-size: 13px; padding: 8px 16px; min-height: 38px; }
            .stat-card { padding: 16px; }
            .stat-card .stat-number { font-size: 26px; }
        }
        @media (max-width: 480px) {
            .topbar { padding: 10px 12px; }
            .topbar .page-title { font-size: 15px; }
            .content-area { padding: 12px; }
            .page-header h1 { font-size: 20px; }
            .page-header p { font-size: 13px; }
            .btn { font-size: 12px; padding: 7px 14px; min-height: 36px; }
            .stat-card { padding: 14px; }
            .stat-card .stat-number { font-size: 22px; }
            .stat-card .stat-label { font-size: 12px; }
            .stats-grid { grid-template-columns: 1fr; }
        }

        /* Scrollbar styling */
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="brand-text">WB<span>Admin</span></div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>

            <a href="Dashboard.php" class="active">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                </span>
                Dashboard
                <span class="nav-arrow">›</span>
            </a>

            <a href="Users.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </span>
                Users
            </a>

            <a href="dailymocktest.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg>
                </span>
                Daily Mock Test
            </a>

            <a href="allmocktestscreen.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg>
                </span>
                All Mock Test Screen
            </a>

            <a href="solutions.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </span>
                All Solution
            </a>

            <div class="nav-label">Results</div>

            <a href="dailymockresult.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                </span>
                Daily Mock Test Result
            </a>

            <a href="allmocktestresult.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                </span>
                All Mock Test Result
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="login.php?logout=1" onclick="return confirm('Are you sure you want to logout?')">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                </span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <h1 class="page-title">Dashboard</h1>
            <div class="topbar-right">
                <div class="admin-info">
                    <div class="admin-avatar"><?= $adminInitial ?></div>
                    <div>
                        <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="welcome-card">
                <h2>Welcome back, <?= htmlspecialchars($adminName) ?>! 👋</h2>
                <p>Manage your mock tests, view results, and oversee users from your admin dashboard.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card" id="statClasses">
                    <div class="stat-header">
                        <div class="stat-icon purple">
                            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                    </div>
                    <div class="stat-value" id="totalClasses">—</div>
                    <div class="stat-label">Total Classes</div>
                </div>

                <div class="stat-card" id="statSubjects">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg>
                        </div>
                    </div>
                    <div class="stat-value" id="totalSubjects">—</div>
                    <div class="stat-label">Total Subjects</div>
                </div>

                <div class="stat-card" id="statMockTests">
                    <div class="stat-header">
                        <div class="stat-icon orange">
                            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                        </div>
                    </div>
                    <div class="stat-value" id="totalMockTests">—</div>
                    <div class="stat-label">Mock Tests</div>
                </div>

                <div class="stat-card" id="statQuestions">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        </div>
                    </div>
                    <div class="stat-value" id="totalQuestions">—</div>
                    <div class="stat-label">Total Questions</div>
                </div>


            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // Load dashboard stats from API
        async function loadDashboardStats() {
            const statIds = ['totalClasses', 'totalSubjects', 'totalMockTests', 'totalQuestions'];
            // Show loading spinners
            statIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<span class="spinner"></span>';
            });

            try {
                const res = await fetch('<?= ADMIN_API_URL ?>get-dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const result = await res.json();
                if (result.status && result.data) {
                    document.getElementById('totalClasses').textContent = result.data.total_classes || '0';
                    document.getElementById('totalSubjects').textContent = result.data.total_subjects || '0';
                    document.getElementById('totalMockTests').textContent = result.data.total_mock_tests || '0';
                    document.getElementById('totalQuestions').textContent = result.data.total_questions || '0';
                } else {
                    statIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.querySelector('.spinner')) el.textContent = '—';
                    });
                }
            } catch (err) {
                console.warn('Could not load dashboard stats:', err);
                statIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.querySelector('.spinner')) el.textContent = '—';
                });
            }
        }
        loadDashboardStats();

        // Highlight active link based on current page

        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    </script>
</body>
</html>
