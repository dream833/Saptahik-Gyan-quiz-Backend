<?php
session_start();
require_once "../utils/api_config.php";
require_once "../utils/db.php";

$users = [];
$dbError = '';

// Fetch all users with class name via JOIN (no search filtering - done client-side)
try {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.full_name,
            u.email,
            u.mobile,
            u.class_grade,
            c.class_name,
            u.about_me,
            u.created_at
        FROM users u
        LEFT JOIN classes c ON u.class_grade = c.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbError = 'Unable to load users. Please try again later.';
}
?>

<?php if (!empty($dbError)): ?>
<script>console.warn('<?= addslashes($dbError) ?>');</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - WB Admin</title>
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .sidebar-brand .brand-icon svg { width: 22px; height: 22px; fill: #fff; }

        .sidebar-brand .brand-text { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }

        .sidebar-brand .brand-text span { color: #818cf8; }

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
        }

        .sidebar-nav a:hover { background: rgba(255, 255, 255, 0.08); color: #fff; }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .sidebar-nav a .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .sidebar-nav a .nav-icon svg { width: 20px; height: 20px; fill: currentColor; }

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

        .sidebar-footer a:hover { background: rgba(255, 50, 50, 0.12); color: #ff6b6b; }

        .sidebar-footer a .nav-icon svg { width: 20px; height: 20px; fill: currentColor; }

        /* ===== MAIN CONTENT ===== */
        .main-content { margin-left: 270px; flex: 1; min-height: 100vh; }

        .topbar {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar .menu-toggle { display: none; background: none; border: none; cursor: pointer; padding: 8px; color: #374151; }

        .topbar .menu-toggle svg { width: 24px; height: 24px; fill: currentColor; }

        .topbar .page-title { font-size: 20px; font-weight: 700; color: #1a1a2e; }

        .topbar .topbar-right { display: flex; align-items: center; gap: 20px; }

        .topbar .topbar-right .admin-info { display: flex; align-items: center; gap: 10px; }

        .topbar .topbar-right .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
        }

        .page-content { padding: 32px; max-width: 1400px; margin: 0 auto; }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .page-header h1 { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }

        .page-header p { color: #64748b; font-size: 15px; margin-top: 4px; font-weight: 400; }

        /* ===== SEARCH BAR ===== */
        .search-wrapper {
            position: relative;
            min-width: 280px;
        }

        .search-wrapper .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .search-wrapper .search-icon svg { width: 18px; height: 18px; fill: currentColor; }

        .search-wrapper input[type="text"] {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .search-wrapper input[type="text"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .search-wrapper input[type="text"]::placeholder { color: #9ca3af; }

        .search-wrapper .clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            display: none;
        }

        .search-wrapper .clear-btn.visible { display: block; }

        .search-wrapper .clear-btn svg { width: 16px; height: 16px; fill: currentColor; }

        .user-count {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
            padding: 0 4px;
        }

        .user-count strong { color: #0f172a; font-weight: 600; }

        /* ===== TABLE ===== */
        .table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

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

        tbody td {
            padding: 16px 20px;
            font-size: 14px;
            color: #334155;
            vertical-align: middle;
        }

        .user-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        }

        .user-name .name-text { font-weight: 600; color: #0f172a; font-size: 14px; }

        .email-cell { color: #6366f1; font-weight: 500; }

        .phone-cell { font-family: 'Inter', monospace; font-size: 13px; color: #475569; letter-spacing: 0.3px; }

        .class-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .bio-cell { max-width: 200px; }

        .bio-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }

        .user-row.hidden { display: none; }

        .no-results {
            display: none;
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .no-results.visible { display: block; }

        .no-results .no-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
        }

        .no-results .no-icon svg { width: 24px; height: 24px; fill: #9ca3af; }

        .no-results h3 { font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 4px; }

        .no-results p { font-size: 14px; color: #94a3b8; }

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
            .page-header { flex-direction: column; align-items: stretch; }
            .search-wrapper { min-width: 100%; }
            table { font-size: 13px; }
            thead th, tbody td { padding: 10px 12px; }
            .bio-cell { max-width: 120px; }
            .user-avatar { width: 30px; height: 30px; font-size: 12px; }
        }

        @media (max-width: 480px) {
            .topbar .page-title { font-size: 17px; }
            .page-content { padding: 16px; }
            thead th, tbody td { padding: 8px 10px; font-size: 12px; }
            .user-name { gap: 6px; }
            .user-avatar { width: 26px; height: 26px; font-size: 10px; }
            .class-badge { font-size: 10px; padding: 2px 8px; }
            .phone-cell { font-size: 12px; }
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
            <div class="brand-text">WB<span>Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="Dashboard.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>Dashboard</a>
            <a href="Users.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></span>Users</a>
            <a href="dailymocktest.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></span>Daily Mock Test</a>
            <a href="allmocktestscreen.php"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/></svg></span>All Mock Test Screen</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <h1 class="page-title">Users</h1>
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
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Users</h1>
                    <p>Manage all registered users</p>
                </div>
                <div class="search-wrapper">
                    <span class="search-icon">
                        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </span>
                    <input type="text" id="searchInput" placeholder="Search by name, email, phone..." autocomplete="off">
                    <button type="button" class="clear-btn" id="clearBtn" aria-label="Clear search">
                        <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    </button>
                </div>
            </div>

            <!-- User Count -->
            <div class="user-count" id="userCount">
                Showing <strong id="countDisplay"><?= count($users) ?></strong> user<?= count($users) !== 1 ? 's' : '' ?>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Class</th>
                                <th>Bio</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="user-row" data-name="<?= htmlspecialchars(strtolower($user['full_name'] ?? '')) ?>" data-email="<?= htmlspecialchars(strtolower($user['email'] ?? '')) ?>" data-phone="<?= htmlspecialchars($user['mobile'] ?? '') ?>" data-class="<?= htmlspecialchars(strtolower($user['class_name'] ?? '')) ?>" data-bio="<?= htmlspecialchars(strtolower($user['about_me'] ?? '')) ?>">
                                        <td>
                                            <div class="user-name">
                                                <div class="user-avatar"><?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?></div>
                                                <span class="name-text" onclick="viewUserDetails(<?= $user['id'] ?>)" style="cursor:pointer;color:#6366f1;"><?= htmlspecialchars($user['full_name'] ?? 'Unknown') ?></span>
                                            </div>
                                        </td>
                                        <td class="email-cell"><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                        <td class="phone-cell"><?= htmlspecialchars($user['mobile'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($user['class_name'])): ?>
                                                <span class="class-badge"><?= htmlspecialchars($user['class_name']) ?></span>
                                            <?php else: ?>
                                                <span style="color:#9ca3af;font-size:13px;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="bio-cell">
                                            <?php if (!empty($user['about_me'])): ?>
                                                <div class="bio-text"><?= htmlspecialchars($user['about_me']) ?></div>
                                            <?php else: ?>
                                                <span style="color:#9ca3af;font-size:13px;">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="user-row" id="emptyRow">
                                    <td colspan="5">
                                        <div class="no-results visible" id="noResults">
                                            <div class="no-icon">
                                                <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                                            </div>
                                            <h3>No users found</h3>
                                            <p>No users have registered yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // ===== CLIENT-SIDE SEARCH =====
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearBtn');
        const rows = document.querySelectorAll('.user-row');
        const countDisplay = document.getElementById('countDisplay');
        const userCount = document.getElementById('userCount');
        const noResults = document.getElementById('noResults');

        function filterUsers() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.id === 'emptyRow') return; // skip the empty-state row
                const name = row.dataset.name || '';
                const email = row.dataset.email || '';
                const phone = row.dataset.phone || '';
                const cls = row.dataset.class || '';
                const bio = row.dataset.bio || '';

                const match = name.includes(query) || email.includes(query) || phone.includes(query) || cls.includes(query) || bio.includes(query);

                if (match || query === '') {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            // Update count
            countDisplay.textContent = visibleCount;
            userCount.innerHTML = `Showing <strong>${visibleCount}</strong> user${visibleCount !== 1 ? 's' : ''}${query ? ` for "<strong>${searchInput.value}</strong>"` : ''}`;

            // Show/hide no-results message
            if (noResults) {
                if (visibleCount === 0 && rows.length > 1) {
                    noResults.classList.add('visible');
                } else {
                    noResults.classList.remove('visible');
                }
            }

            // Show/hide clear button
            clearBtn.classList.toggle('visible', query !== '');
        }

        searchInput.addEventListener('input', filterUsers);

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterUsers();
            searchInput.focus();
        });

        // ===== API Integration: View User Details =====
        const API_BASE = '<?= ADMIN_API_URL ?>';

        function viewUserDetails(userId) {
            fetch(API_BASE + 'fetch-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            })
            .then(res => res.json())
            .then(result => {
                if (result.status && result.data) {
                    const u = result.data;
                    const info = [
                        '👤 Name: ' + (u.full_name || 'N/A'),
                        '📧 Email: ' + (u.email || 'N/A'),
                        '📱 Mobile: ' + (u.mobile || 'N/A'),
                        '📚 Class: ' + (u.class_name || 'N/A'),
                        '📍 Address: ' + (u.address || 'N/A'),
                        '📝 Bio: ' + (u.about_me || 'N/A'),
                        '📅 Joined: ' + (u.created_at || 'N/A')
                    ].join('\n');
                    alert(info);
                } else {
                    alert('Error: ' + (result.message || 'Could not load user details.'));
                }
            })
            .catch(err => {
                alert('Network error. Could not load user details.');
            });
        }
    </script>
</body>
</html>

