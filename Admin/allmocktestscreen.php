<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Mock Test Screen - WB Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }
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
        .placeholder-card {
            background: #fff; border-radius: 14px; padding: 60px; text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .placeholder-card .placeholder-icon {
            width: 64px; height: 64px; margin: 0 auto 16px;
            background: #f0f2f5; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
        }
        .placeholder-card .placeholder-icon svg { width: 32px; height: 32px; fill: #9ca3af; }
        .placeholder-card h3 { font-size: 18px; color: #374151; margin-bottom: 8px; }
        .placeholder-card p { color: #9ca3af; font-size: 14px; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 14px 20px; }
            .page-content { padding: 20px; }
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
            <a href="allmocktestscreen.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg></span>All Mock Test Screen</a>
            <a href="solutions.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>All Solution</a>
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
            <h1 class="page-title">All Mock Test Screen</h1>
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
                <h1>All Mock Test Screen</h1>
                <p>View and manage all mock test screens</p>
            </div>
            <div class="placeholder-card">
                <div class="placeholder-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg>
                </div>
                <h3>All Mock Test Screen</h3>
                <p>All mock test screens will be listed here.</p>
            </div>
        </div>
    </main>
    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); }
        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
