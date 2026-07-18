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
    <title>All Mock Test Screen - WB Admin</title>
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
        .form-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
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
        .form-group select, .form-group input, .form-group textarea {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
            width: 100%;
        }
        .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .form-group select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-group select:disabled { background-color: #f1f5f9; cursor: not-allowed; }
        .correct-answer-group {
            display: flex; gap: 10px; flex-wrap: wrap; padding: 4px 0;
        }
        .correct-answer-group label {
            padding: 10px 20px; border: 1.5px solid #e2e8f0; border-radius: 10px;
            cursor: pointer; font-size: 14px; font-weight: 500; color: #475569;
            transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
            background: #fff; user-select: none;
        }
        .correct-answer-group label:hover { border-color: #a5b4fc; background: #eef2ff; }
        .correct-answer-group label.selected { border-color: #22c55e; background: #f0fdf4; color: #16a34a; font-weight: 600; }
        .correct-answer-group label input[type="radio"] { display: none; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.2s ease; white-space: nowrap;
        }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,0.25); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.35); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger { background: #fef2f2; color: #ef4444; }
        .btn-danger:hover { background: #fee2e2; }
        .btn-success { background: #f0fdf4; color: #22c55e; }
        .btn-success:hover { background: #dcfce7; }
        .btn-warning { background: #fffbeb; color: #f59e0b; }
        .btn-warning:hover { background: #fef3c7; }
        .btn-info { background: #eef2ff; color: #6366f1; }
        .btn-info:hover { background: #e0e7ff; }
        .btn-sm { padding: 7px 14px; font-size: 12px; border-radius: 8px; }
        .btn-icon { padding: 8px; border-radius: 8px; }
        .form-actions { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .select-with-add { display: flex; gap: 8px; align-items: center; }
        .select-with-add select { flex: 1; }
        .add-inline { display: flex; gap: 8px; margin-top: 8px; }
        .add-inline input {
            flex: 1; padding: 8px 12px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; outline: none;
        }
        .add-inline input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.1); }

        /* ===== STEPPER ===== */
        .step-indicator {
            display: flex; align-items: center; gap: 24px;
            margin-bottom: 24px; padding: 16px 20px;
            background: #f8fafc; border-radius: 12px;
            flex-wrap: wrap;
        }
        .step {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 500; color: #94a3b8;
        }
        .step.active { color: #4f46e5; font-weight: 600; }
        .step.done { color: #16a34a; }
        .step .step-num {
            width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            background: #e2e8f0; color: #94a3b8;
            flex-shrink: 0;
        }
        .step.active .step-num { background: #6366f1; color: #fff; }
        .step.done .step-num { background: #22c55e; color: #fff; }
        .step-connector {
            width: 20px; height: 2px; background: #e2e8f0; flex-shrink: 0;
        }
        .step-connector.done { background: #22c55e; }

        /* ===== SET CARDS ===== */
        .set-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }
        .set-card {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .set-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 16px rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }
        .set-card:active { transform: translateY(0); }
        .set-card .set-name {
            font-size: 15px; font-weight: 700; color: #0f172a;
        }
        .set-card .set-meta {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; color: #64748b;
        }
        .set-card .set-count {
            display: inline-flex; align-items: center; gap: 4px;
            background: #eef2ff; color: #4f46e5;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .set-card .set-accent {
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
        }
        .set-empty {
            grid-column: 1 / -1;
            text-align: center; padding: 40px 20px; color: #94a3b8;
        }
        .set-empty .empty-icon {
            width: 40px; height: 40px; margin: 0 auto 8px;
            background: #f1f5f9; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .set-empty .empty-icon svg { width: 20px; height: 20px; fill: #94a3b8; }

        /* ===== SET DETAIL ===== */
        .detail-back {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 500; color: #6366f1;
            cursor: pointer; padding: 6px 12px; border-radius: 8px;
            transition: all 0.15s; margin-bottom: 16px; border: none; background: none;
            font-family: 'Inter', sans-serif;
        }
        .detail-back:hover { background: #eef2ff; }
        .detail-path {
            font-size: 14px; color: #64748b; margin-bottom: 20px;
            display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
        }

        /* ===== CORRECT ANSWER BADGE IN TABLE ===== */
        .correct-ans-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 6px;
            font-size: 12px; font-weight: 600;
            background: #f0fdf4; color: #16a34a;
        }
        .correct-ans-badge .opt-label-sm {
            display: inline-block; width: 18px; height: 18px; line-height: 18px;
            text-align: center; background: #16a34a; color: #fff;
            border-radius: 4px; font-size: 10px; font-weight: 700;
        }

        /* ===== GROUP HEADER ===== */
        .group-header td {
            padding: 10px 20px;
            background: linear-gradient(135deg, #f1f5f9, #f8fafc);
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600; font-size: 13px; color: #1e293b;
        }
        .group-header .group-path {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .group-header .group-count {
            margin-left: auto;
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; color: #6366f1;
            background: #eef2ff; padding: 4px 14px; border-radius: 20px;
        }
        .group-header .group-count strong { font-size: 14px; }
        .group-arrow { color: #94a3b8; font-size: 14px; font-weight: 400; }
        .group-body td { border-top: none; }

        /* ===== TABLE ===== */
        .table-container {
            background: #fff; border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden; border: 1px solid rgba(0,0,0,0.04);
        }
        .table-header {
            padding: 20px 24px 0;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .table-header h3 { font-size: 16px; font-weight: 700; color: #0f172a; }
        .table-header .result-count { font-size: 13px; color: #64748b; font-weight: 500; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        thead th {
            padding: 16px 20px; text-align: left;
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; color: #64748b; white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr { transition: all 0.15s ease; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }
        tbody td { padding: 16px 20px; font-size: 14px; color: #334155; vertical-align: middle; }
        .opt-a { background: #fef3c7; color: #d97706; }
        .opt-b { background: #dbeafe; color: #2563eb; }
        .opt-c { background: #fce7f3; color: #db2777; }
        .opt-d { background: #e0e7ff; color: #4f46e5; }
        .class-badge { display: inline-block; padding: 4px 12px; background: #eef2ff; color: #4f46e5; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .subject-badge { display: inline-block; padding: 4px 12px; background: #f0fdf4; color: #16a34a; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .chapter-badge { display: inline-block; padding: 4px 12px; background: #fef3c7; color: #d97706; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .set-badge { display: inline-block; padding: 4px 12px; background: #fce7f3; color: #db2777; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .q-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; background: #f1f5f9; border-radius: 50%;
            font-size: 13px; font-weight: 700; color: #0f172a;
        }
        .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state .empty-icon {
            width: 48px; height: 48px; margin: 0 auto 12px;
            background: #f1f5f9; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #e2e8f0;
        }
        .empty-state .empty-icon svg { width: 24px; height: 24px; fill: #94a3b8; }
        .empty-state h3 { font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 4px; }
        .empty-state p { font-size: 14px; color: #94a3b8; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .sidebar-overlay.active { display: block; }

        /* ===== OPTIONS INLINE ===== */
        .options-inline {
            display: flex; gap: 6px; flex-wrap: wrap;
        }
        .options-inline .opt-chip {
            padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 700;
        }
        .options-inline .opt-chip.correct {
            outline: 2px solid #22c55e; outline-offset: 1px;
        }

        /* ===== SECTION DIVIDER ===== */
        .section-divider {
            display: flex; align-items: center; gap: 16px;
            margin: 8px 0 24px;
        }
        .section-divider h2 {
            font-size: 16px; font-weight: 700; color: #0f172a;
            display: flex; align-items: center; gap: 10px; white-space: nowrap;
        }
        .section-divider .divider-line {
            flex: 1; height: 1px; background: #e2e8f0;
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

        /* ===== UI POLISH ===== */
        .btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .form-group select, .form-group input, .form-group textarea {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .set-card { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        table tbody tr { transition: background 0.15s ease; }
        .sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-overlay { transition: opacity 0.3s ease; }

        /* ===== TOUCH-FRIENDLY TARGETS ===== */
        .btn, .set-card, .detail-back, .sidebar-nav a {
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .correct-answer-group label {
            min-height: 44px; display: flex; align-items: center; justify-content: center;
        }
        .actions-cell .btn {
            min-height: 38px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .form-grid-4 { grid-template-columns: 1fr 1fr; }
            .set-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
            .topbar-right .admin-info > div:last-child { display: none; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .topbar .menu-toggle { display: block; }
            .topbar { padding: 12px 16px; }
            .topbar .page-title { font-size: 17px; }
            .topbar-right .admin-info { gap: 6px; }
            .page-content { padding: 16px; }
            .form-grid-4 { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .form-card { padding: 18px; }
            .form-card h2 { font-size: 15px; }
            .set-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
            .set-card { padding: 14px 16px; }
            .step-indicator { gap: 10px; padding: 12px 14px; overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
            .step { font-size: 12px; white-space: nowrap; flex-shrink: 0; }
            .step-connector { width: 12px; flex-shrink: 0; }
            .actions-cell { flex-direction: row; flex-wrap: wrap; gap: 4px; }
            .actions-cell .btn { padding: 6px 12px; font-size: 12px; flex: 1; min-width: 0; justify-content: center; }
            .select-with-add { flex-wrap: nowrap; }
            .select-with-add select { min-width: 0; }
            .select-with-add .btn { padding: 10px 10px; }
            .add-inline { flex-wrap: nowrap; }
            .add-inline input { min-width: 0; }
            .add-inline .btn { white-space: nowrap; padding: 8px 12px; font-size: 12px; }
            thead th, tbody td { padding: 10px 12px; }
            .group-header td { padding: 8px 12px; }
            .options-inline { flex-direction: row; flex-wrap: wrap; gap: 4px; }
            .options-inline .opt-chip { font-size: 10px; padding: 2px 6px; }
            .section-divider { flex-direction: column; align-items: flex-start; gap: 8px; }
            .section-divider .divider-line { width: 100%; }
            .section-divider h2 { font-size: 14px; white-space: normal; }
            .table-header { padding: 16px 16px 0; flex-direction: column; align-items: flex-start; }
            .table-header .result-count { font-size: 12px; }
            .detail-path { font-size: 13px; gap: 4px; }
            .correct-answer-group { gap: 8px; }
            .correct-answer-group label { padding: 8px 14px; font-size: 13px; min-height: 38px; flex: 1; }
            .form-actions { flex-direction: column; }
            .form-actions .btn { width: 100%; justify-content: center; }
            .empty-state { padding: 40px 16px; }
            .empty-state h3 { font-size: 15px; }
            .empty-state p { font-size: 13px; }
            .class-badge, .subject-badge, .chapter-badge, .set-badge { font-size: 11px; padding: 3px 8px; }
            .correct-ans-badge { font-size: 11px; }
            .q-num { width: 26px; height: 26px; font-size: 12px; }
        }
        @media (max-width: 480px) {
            .page-content { padding: 12px; }
            .form-card { padding: 14px; margin-bottom: 16px; }
            .form-card h2 { font-size: 14px; }
            .page-header { margin-bottom: 16px; }
            .page-header h1 { font-size: 20px; }
            .page-header p { font-size: 13px; }
            .topbar { padding: 10px 12px; }
            .topbar .page-title { font-size: 15px; }
            thead th { padding: 8px 10px; font-size: 10px; }
            tbody td { padding: 8px 10px; font-size: 12px; }
            .set-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .set-card { padding: 12px 14px; }
            .set-card .set-name { font-size: 13px; }
            .set-card .set-count { font-size: 10px; padding: 2px 8px; }
            .step-indicator { gap: 6px; padding: 10px 12px; }
            .step { font-size: 11px; }
            .step .step-num { width: 22px; height: 22px; font-size: 10px; }
            .step-connector { width: 8px; }
            .form-group label { font-size: 12px; }
            .form-group select, .form-group input, .form-group textarea { font-size: 13px; padding: 8px 12px; }
            .btn { font-size: 13px; padding: 8px 16px; }
            .btn-sm { font-size: 11px; padding: 6px 10px; }
            .actions-cell .btn { font-size: 11px; padding: 5px 8px; min-height: 34px; }
            .actions-cell .btn svg { width: 12px; height: 12px; }
            .correct-answer-group label { padding: 8px 12px; font-size: 12px; min-height: 36px; }
            .table-header { padding: 12px 12px 0; }
            .table-header h3 { font-size: 14px; }
            .group-header td { padding: 6px 10px; font-size: 12px; }
            .group-header .group-count { font-size: 11px; padding: 3px 10px; }
            .group-header .group-count strong { font-size: 12px; }
            .group-arrow { font-size: 12px; }
            .class-badge, .subject-badge, .chapter-badge, .set-badge { font-size: 10px; padding: 2px 6px; }
            .options-inline .opt-chip { font-size: 9px; padding: 2px 5px; }
            .correct-ans-badge { font-size: 10px; padding: 2px 8px; }
            .correct-ans-badge .opt-label-sm { width: 16px; height: 16px; line-height: 16px; font-size: 9px; }
            .q-num { width: 22px; height: 22px; font-size: 11px; }
            .form-card h2 .form-icon { width: 26px; height: 26px; }
            .form-card h2 .form-icon svg { width: 14px; height: 14px; }
            .select-with-add .btn { padding: 8px 8px; }
            .select-with-add .btn svg { width: 14px; height: 14px; }
            .detail-path { font-size: 12px; }
            .detail-back { font-size: 12px; padding: 5px 10px; }
            .empty-state { padding: 30px 12px; }
            .empty-state .empty-icon { width: 36px; height: 36px; }
            .empty-state .empty-icon svg { width: 18px; height: 18px; }
            .empty-state h3 { font-size: 14px; }
            .empty-state p { font-size: 12px; }
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
    <div class="sidebar-footer">            <a href="login.php?logout=1" onclick="return confirm('Are you sure you want to logout?')">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg></span>Logout</a>
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
                <div><div style="font-size:14px;font-weight:600;color:#0f172a;">Admin</div><div style="font-size:12px;color:#64748b;">Administrator</div></div>
            </div>
        </div>
    </header>

    <div class="page-content">
        <div class="page-header">
            <h1>Question Bank</h1>
            <p>Manage questions by filtering Class → Subject → Chapter → Set. All questions are also listed by date below.</p>
        </div>

        <!-- ===== MANAGE QUESTIONS ===== -->
        <div class="form-card">
            <h2>
                <span class="form-icon"><svg viewBox="0 0 24 24"><path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/></svg></span>
                Manage Questions
            </h2>

            <!-- Step Indicator -->
            <div class="step-indicator" id="stepIndicator">
                <span class="step active" id="step1"><span class="step-num">1</span> Select Class</span>
                <span class="step-connector" id="conn1"></span>
                <span class="step" id="step2"><span class="step-num">2</span> Select Subject</span>
                <span class="step-connector" id="conn2"></span>
                <span class="step" id="step3"><span class="step-num">3</span> Select Chapter</span>
                <span class="step-connector" id="conn3"></span>
                <span class="step" id="step4"><span class="step-num">4</span> Choose Set</span>
            </div>

            <!-- Step 1-3: Filter Row -->
            <div class="form-grid-4" id="filterRow">
                <div class="form-group">
                    <label>Class <span class="required">*</span></label>
                    <div class="select-with-add">
                        <select id="classSelect"><option value="">Select Class</option></select>
                        <button class="btn btn-secondary btn-sm" onclick="toggleAddInput('class')" type="button" title="Add New Class" style="padding:10px 12px;flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        </button>
                    </div>
                    <div id="addClassRow" class="add-inline" style="display:none;">
                        <input type="text" id="newClassName" placeholder="Enter class name">
                        <button class="btn btn-primary btn-sm" onclick="addNewItem('class')" type="button">Add</button>
                        <button class="btn btn-secondary btn-sm" onclick="hideAddInput('class')" type="button">Cancel</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Subject <span class="required">*</span></label>
                    <div class="select-with-add">
                        <select id="subjectSelect" disabled><option value="">Select Class First</option></select>
                        <button class="btn btn-secondary btn-sm" onclick="toggleAddInput('subject')" type="button" title="Add New Subject" style="padding:10px 12px;flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        </button>
                    </div>
                    <div id="addSubjectRow" class="add-inline" style="display:none;">
                        <input type="text" id="newSubjectName" placeholder="Enter subject name">
                        <button class="btn btn-primary btn-sm" onclick="addNewItem('subject')" type="button">Add</button>
                        <button class="btn btn-secondary btn-sm" onclick="hideAddInput('subject')" type="button">Cancel</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Chapter <span class="required">*</span></label>
                    <div class="select-with-add">
                        <select id="chapterSelect" disabled><option value="">Select Subject First</option></select>
                        <button class="btn btn-secondary btn-sm" onclick="toggleAddInput('chapter')" type="button" title="Add New Chapter" style="padding:10px 12px;flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        </button>
                    </div>
                    <div id="addChapterRow" class="add-inline" style="display:none;">
                        <input type="text" id="newChapterName" placeholder="Enter chapter name">
                        <button class="btn btn-primary btn-sm" onclick="addNewItem('chapter')" type="button">Add</button>
                        <button class="btn btn-secondary btn-sm" onclick="hideAddInput('chapter')" type="button">Cancel</button>
                    </div>
                </div>
                <div class="form-group" style="justify-content:flex-end;">
                    <label style="opacity:0;user-select:none;">.</label>
                    <button class="btn btn-secondary" onclick="resetManageSection()" style="width:100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Set Cards (shown when chapter is selected) -->
            <div id="setCardsSection" style="display:none;margin-top:4px;">
                <div class="section-divider">
                    <h2><span class="form-icon" style="width:28px;height:28px;"><svg viewBox="0 0 24 24" fill="#6366f1" width="14" height="14"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></span>Available Sets</h2>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div class="divider-line" style="flex:1;"></div>
                        <button class="btn btn-secondary btn-sm" onclick="toggleAddInput('set')" type="button" title="Add New Set">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            Add Set
                        </button>
                    </div>
                    <div id="addSetRow" class="add-inline" style="display:none;">
                        <input type="text" id="newSetName" placeholder="Enter set name (e.g. Set-4)">
                        <button class="btn btn-primary btn-sm" onclick="addNewItem('set')" type="button">Add</button>
                        <button class="btn btn-secondary btn-sm" onclick="hideAddInput('set')" type="button">Cancel</button>
                    </div>
                </div>
                <div class="set-grid" id="setGrid"></div>
            </div>

            <!-- Set Detail (shown when a set is clicked) -->
            <div id="setDetailSection" style="display:none;margin-top:4px;">
                <button class="detail-back" onclick="closeSetDetail()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    Back to Sets
                </button>
                <div class="detail-path" id="detailPath"></div>

                <!-- Add Question Form -->
                <div class="form-card" style="margin-bottom:20px;padding:24px;box-shadow:none;border:1px solid #e2e8f0;">
                    <h2 style="margin-bottom:16px;">
                        <span class="form-icon"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></span>
                        <span id="detailFormTitle">Add Question</span>
                    </h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Question <span class="required">*</span></label>
                            <textarea id="detailQuestionText" placeholder="Enter the question..."></textarea>
                        </div>
                        <div class="form-group"><label>Option A <span class="required">*</span></label><input type="text" id="detailOptA" placeholder="Option A"></div>
                        <div class="form-group"><label>Option B <span class="required">*</span></label><input type="text" id="detailOptB" placeholder="Option B"></div>
                        <div class="form-group"><label>Option C <span class="required">*</span></label><input type="text" id="detailOptC" placeholder="Option C"></div>
                        <div class="form-group"><label>Option D <span class="required">*</span></label><input type="text" id="detailOptD" placeholder="Option D"></div>
                        <div class="form-group full-width">
                            <label>Correct Answer <span class="required">*</span></label>
                            <div class="correct-answer-group" id="detailCorrectGroup">
                                <label><input type="radio" name="detailCorrect" value="A"> A</label>
                                <label><input type="radio" name="detailCorrect" value="B"> B</label>
                                <label><input type="radio" name="detailCorrect" value="C"> C</label>
                                <label><input type="radio" name="detailCorrect" value="D"> D</label>
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <div class="form-actions">
                                <button class="btn btn-primary" id="detailSaveBtn" onclick="saveDetailQuestion()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    <span id="detailSaveText">Add Question</span>
                                </button>
                                <button class="btn btn-secondary" onclick="clearDetailForm()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Questions Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Questions in this Set</h3>
                        <span class="result-count" id="detailResultCount">0 questions</span>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Options</th>
                                    <th>Correct</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg></div><h3>No questions yet</h3><p>Use the form above to add questions to this set.</p></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== ALL QUESTIONS BY DATE ===== -->
        <div class="form-card">
            <h2>
                <span class="form-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg></span>
                All Questions by Date
            </h2>
            <div class="table-container">
                <div class="table-header">
                    <h3>All Questions (Newest First)</h3>
                    <span class="result-count" id="allQuestionsCount">No questions yet</span>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Category</th>
                                <th>Options</th>
                                <th>Correct</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="allQuestionsTableBody">
                            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg></div><h3>No questions in the system</h3><p>Add questions using the Manage Questions section above.</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    function toggleSidebar() { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); }
    menuToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    // ===== API CONFIGURATION =====
    const ADMIN_API = '<?= ADMIN_API_URL ?>';
    const APP_API = '<?= APP_API_URL ?>';

    // ===== STATE =====
    let data = { classes: [], subjects: {}, chapters: {}, sets: {} };
    let allQuestions = [];
    let editingQuestionId = null;
    let selectedSetId = null;
    let currentClassId = 0, currentSubjectId = 0, currentChapterId = 0;
    let questions = [];
    let detailContext = {};
    let nextClassId = 1000;
    let nextSubjectId = 1000;
    let nextChapterId = 1000;
    let nextSetId = 1000;

    // ===== API HELPER =====
    async function apiPost(endpoint, payload) {
        try {
            const res = await fetch(ADMIN_API + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            return await res.json();
        } catch(e) {
            return { status: false, message: 'Network error: ' + e.message };
        }
    }

    // ===== LOAD CLASSES FROM API =====
    async function loadClasses() {
        try {
            const res = await fetch(APP_API + 'get-class.php', { method: 'GET', headers: { 'Accept': 'application/json' } });
            const result = await res.json();
            if (result.status) {
                data.classes = result.data || [];
            }
        } catch(e) { console.warn('Failed to load classes:', e); }
        populateSelect('class');
    }

    // ===== LOAD SUBJECTS FROM API =====
    async function loadSubjects(classId) {
        try {
            const res = await fetch(APP_API + 'get-subject.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ class_id: classId })
            });
            const result = await res.json();
            data.subjects[classId] = (result.status && result.data) ? result.data : [];
        } catch(e) {
            console.warn('Failed to load subjects:', e);
            data.subjects[classId] = [];
        }
        populateSelect('subject');
    }

    // ===== LOAD CHAPTERS FROM API =====
    async function loadChapters(subjectId) {
        try {
            const result = await apiPost('get-chapter.php', { subject_id: subjectId });
            data.chapters[subjectId] = (result.status && result.data) ? result.data : [];
        } catch(e) {
            data.chapters[subjectId] = [];
        }
        populateSelect('chapter');
    }

    // ===== LOAD SETS FROM API =====
    async function loadSets(chapterId) {
        try {
            const result = await apiPost('get-set-details.php', { chapter_id: chapterId });
            data.sets[chapterId] = (result.status && result.data) ? result.data.map(s => ({
                id: s.id, name: s.set_name, duration_minutes: s.duration_minutes, total_questions: s.total_questions
            })) : [];
        } catch(e) {
            data.sets[chapterId] = [];
        }
        renderSetCards();
    }

    // ===== LOAD QUESTIONS FOR A SET =====
    async function loadQuestionsForSet(setId) {
        try {
            const result = await apiPost('get-all-mock-questions.php', { set_id: setId });
            if (result.status && result.data) {
                allQuestions = result.data.map(q => ({
                    id: q.id, text: q.question,
                    opts: [q.option_a, q.option_b, q.option_c, q.option_d],
                    correct: q.correct_answer,
                    date: q.created_at ? q.created_at.split(' ')[0] : '',
                    time: q.created_at ? (q.created_at.split(' ')[1] || '') : '',
                    setId: setId
                }));
                // Also populate the local questions array for CRUD operations
                questions = result.data.map(q => ({
                    id: q.id, text: q.question,
                    options: [q.option_a, q.option_b, q.option_c, q.option_d],
                    correct: q.correct_answer,
                    date: q.created_at ? q.created_at.split(' ')[0] : '',
                    time: q.created_at ? (q.created_at.split(' ')[1] || '') : '',
                    classId: detailContext.classId || 0,
                    subjectId: detailContext.subjectId || 0,
                    chapterId: detailContext.chapterId || 0,
                    setId: setId,
                    className: detailContext.className || '',
                    subjectName: detailContext.subjectName || '',
                    chapterName: detailContext.chapterName || '',
                    setName: detailContext.setName || ''
                }));
            } else {
                allQuestions = [];
                questions = [];
            }
        } catch(e) {
            allQuestions = [];
            questions = [];
        }
        renderDetailQuestions();
    }

    // ===== INIT =====
    function init() {
        loadClasses();
        populateSelect('class');
        setupCascading();
        updateStepIndicator();
        renderSetCards();
        renderAllQuestionsByDate();
    }

    // ===== POPULATE SELECT =====
    function populateSelect(type) {
        const sel = getSelect(type);
        if (type === 'class') {
            sel.innerHTML = '<option value="">Select Class</option>';
            data.classes.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.class_name}</option>`);
        } else if (type === 'subject') {
            const classId = parseInt(document.getElementById('classSelect').value);
            sel.innerHTML = '';
            if (!classId) { sel.innerHTML = '<option value="">Select Class First</option>'; sel.disabled = true; return; }
            sel.disabled = false;
            const subs = data.subjects[classId] || [];
            sel.innerHTML = '<option value="">Select Subject</option>';
            subs.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.subject_name}</option>`);
        } else if (type === 'chapter') {
            const subjectId = parseInt(document.getElementById('subjectSelect').value);
            sel.innerHTML = '';
            if (!subjectId) { sel.innerHTML = '<option value="">Select Subject First</option>'; sel.disabled = true; return; }
            sel.disabled = false;
            const chs = data.chapters[subjectId] || [];
            sel.innerHTML = '<option value="">Select Chapter</option>';
            chs.forEach(ch => sel.innerHTML += `<option value="${ch.id}">${ch.chapter_name}</option>`);
        } else if (type === 'set') {
            const chapterId = parseInt(document.getElementById('chapterSelect').value);
            sel.innerHTML = '';
            if (!chapterId) { sel.innerHTML = '<option value="">Select Chapter First</option>'; sel.disabled = true; return; }
            sel.disabled = false;
            const sts = data.sets[chapterId] || [];
            sel.innerHTML = '<option value="">Select Set</option>';
            sts.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        }
    }

    function getSelect(type) {
        const map = { class: 'classSelect', subject: 'subjectSelect', chapter: 'chapterSelect' };
        return document.getElementById(map[type]);
    }

    function getSelectedText(selectId) {
        const sel = document.getElementById(selectId);
        const val = sel.value;
        const text = sel.options[sel.selectedIndex]?.text || '';
        if (!val || /^Select (Class|Subject|Chapter|Set)( First)?$/.test(text)) return '';
        return text;
    }

    // ===== STEP INDICATOR =====
    function updateStepIndicator() {
        const steps = ['step1','step2','step3','step4'];
        const conns = ['conn1','conn2','conn3'];
        const vals = [
            document.getElementById('classSelect').value,
            document.getElementById('subjectSelect').value,
            document.getElementById('chapterSelect').value
        ];
        let activeIdx = 0;
        for (let i = 0; i < vals.length; i++) {
            if (vals[i]) activeIdx = i + 1;
            else break;
        }
        steps.forEach((id, i) => {
            const el = document.getElementById(id);
            el.className = 'step';
            if (i < activeIdx) el.classList.add('done');
            else if (i === activeIdx) el.classList.add('active');
        });
        conns.forEach((id, i) => {
            document.getElementById(id).className = 'step-connector' + (i < activeIdx ? ' done' : '');
        });
    }

    // ===== CASCADING =====
    function setupCascading() {
        document.getElementById('classSelect').addEventListener('change', function() {
            const classId = parseInt(this.value);
            document.getElementById('subjectSelect').value = '';
            document.getElementById('chapterSelect').value = '';
            if (classId) {
                loadSubjects(classId);
            } else {
                populateSelect('subject');
            }
            populateSelect('chapter');
            closeSetDetail();
            updateStepIndicator(); renderSetCards();
        });
        document.getElementById('subjectSelect').addEventListener('change', function() {
            const subjectId = parseInt(this.value);
            document.getElementById('chapterSelect').value = '';
            if (subjectId) {
                loadChapters(subjectId);
            } else {
                populateSelect('chapter');
            }
            closeSetDetail();
            updateStepIndicator(); renderSetCards();
        });
        document.getElementById('chapterSelect').addEventListener('change', function() {
            const chapterId = parseInt(this.value);
            if (chapterId) {
                renderSetCards();
                loadSets(chapterId);
            } else {
                renderSetCards();
            }
            closeSetDetail();
            updateStepIndicator();
        });
    }

    // ===== SET CARDS =====
    function renderSetCards() {
        const chapterId = parseInt(document.getElementById('chapterSelect').value);
        const section = document.getElementById('setCardsSection');
        const grid = document.getElementById('setGrid');

        if (!chapterId) { section.style.display = 'none'; return; }
        section.style.display = 'block';

        const sts = data.sets[chapterId] || [];
        if (sts.length === 0) {
            grid.innerHTML = `<div class="set-empty"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></div><h3>No sets available</h3><p>Add a new set using the + button above.</p></div>`;
            return;
        }

        let html = '';
        sts.forEach(s => {
            const cnt = questions.filter(q => q.setId === s.id && q.chapterId === chapterId).length;
            html += `
                <div class="set-card" onclick="openSetDetail(${s.id})">
                    <div class="set-accent"></div>
                    <div class="set-name">${s.name}</div>
                    <div class="set-meta">
                        <span class="set-count">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg>
                            ${cnt} question${cnt !== 1 ? 's' : ''}
                        </span>
                    </div>
                </div>`;
        });
        grid.innerHTML = html;
    }

    // ===== SET DETAIL =====
    detailContext = { classId: null, subjectId: null, chapterId: null, setId: null };

    function openSetDetail(setId) {
        const classId = parseInt(document.getElementById('classSelect').value);
        const subjectId = parseInt(document.getElementById('subjectSelect').value);
        const chapterId = parseInt(document.getElementById('chapterSelect').value);
        if (!classId || !subjectId || !chapterId || !setId) return;

        detailContext = { classId, subjectId, chapterId, setId };
        selectedSetId = setId;

        const className = getSelectedText('classSelect');
        const subjectName = getSelectedText('subjectSelect');
        const chapterName = getSelectedText('chapterSelect');
        const set = data.sets[chapterId]?.find(s => s.id === setId);
        const setName = set ? set.name : '';

        document.getElementById('detailPath').innerHTML = `
            <span class="class-badge">${className}</span>
            <span class="group-arrow">›</span>
            <span class="subject-badge">${subjectName}</span>
            <span class="group-arrow">›</span>
            <span class="chapter-badge">${chapterName}</span>
            <span class="group-arrow">›</span>
            <span class="set-badge">${setName}</span>
        `;

        document.getElementById('setCardsSection').style.display = 'none';
        document.getElementById('setDetailSection').style.display = 'block';
        clearDetailForm();
        renderDetailTable();
    }

    function closeSetDetail() {
        document.getElementById('setDetailSection').style.display = 'none';
        document.getElementById('setCardsSection').style.display = 'block';
        selectedSetId = null;
        editingQuestionId = null;
        clearDetailForm();
    }

    function getDetailQuestions() {
        if (!detailContext.setId) return [];
        return questions.filter(q =>
            q.classId === detailContext.classId &&
            q.subjectId === detailContext.subjectId &&
            q.chapterId === detailContext.chapterId &&
            q.setId === detailContext.setId
        );
    }

    function renderDetailTable() {
        const tbody = document.getElementById('detailTableBody');
        const countEl = document.getElementById('detailResultCount');
        const qs = getDetailQuestions();
        const labels = ['A','B','C','D'];
        const optClasses = ['opt-a','opt-b','opt-c','opt-d'];

        if (qs.length === 0) {
            countEl.textContent = '0 questions';
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg></div><h3>No questions yet</h3><p>Use the form above to add questions to this set.</p></div></td></tr>`;
            return;
        }

        countEl.textContent = `${qs.length} question${qs.length !== 1 ? 's' : ''}`;
        let html = '';
        qs.forEach((q, idx) => {
            html += `<tr>
                <td><span class="q-num">${idx + 1}</span></td>
                <td style="font-weight:500;max-width:240px;">${q.text}</td>
                <td><div class="options-inline">${q.options.map((opt, i) => `<span class="opt-chip ${optClasses[i]}${labels[i] === q.correct ? ' correct' : ''}">${labels[i]}. ${opt}</span>`).join('')}</div></td>
                <td><span class="correct-ans-badge"><span class="opt-label-sm">${q.correct}</span> ${q.options[labels.indexOf(q.correct)]}</span></td>
                <td style="white-space:nowrap;font-size:13px;color:#64748b;">
                    <span>${formatDate(q.date)}</span>
                    <span style="display:block;font-size:11px;color:#94a3b8;">${q.time || ''}</span>
                </td>
                <td>
                    <div class="actions-cell">
                        <button class="btn btn-warning btn-sm" onclick="editDetailQuestion(${q.id})"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteDetailQuestion(${q.id})"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg> Delete</button>
                    </div>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    // ===== SAVE / EDIT / DELETE QUESTIONS IN DETAIL =====
    async function saveDetailQuestion() {
        const text = document.getElementById('detailQuestionText').value.trim();
        const optA = document.getElementById('detailOptA').value.trim();
        const optB = document.getElementById('detailOptB').value.trim();
        const optC = document.getElementById('detailOptC').value.trim();
        const optD = document.getElementById('detailOptD').value.trim();
        const correctRadio = document.querySelector('input[name="detailCorrect"]:checked');
        const correct = correctRadio ? correctRadio.value : '';
        if (!text || !optA || !optB || !optC || !optD || !correct) {
            alert('Please fill in all fields and select the correct answer.'); return;
        }

        const { setId } = detailContext;
        if (!setId) { alert('No set selected.'); return; }

        try {
            let result;
            if (editingQuestionId) {
                // UPDATE existing question
                result = await apiPost('update-all-mock-questions.php', {
                    question_id: editingQuestionId,
                    set_id: setId,
                    question: text,
                    option_a: optA,
                    option_b: optB,
                    option_c: optC,
                    option_d: optD,
                    correct_answer: correct,
                    explanation: ''
                });
            } else {
                // ADD new question
                result = await apiPost('all-mock-test-questions.php', {
                    set_id: setId,
                    question: text,
                    option_a: optA,
                    option_b: optB,
                    option_c: optC,
                    option_d: optD,
                    correct_answer: correct,
                    explanation: ''
                });
            }

            if (result.status) {
                alert(result.message || 'Success!');
                editingQuestionId = null;
                document.getElementById('detailSaveText').textContent = 'Add Question';
                document.getElementById('detailSaveBtn').className = 'btn btn-primary';
                clearDetailForm();
                await loadQuestionsForSet(setId);
                renderSetCards();
                renderAllQuestionsByDate();
            } else {
                alert(result.message || 'Operation failed.');
            }
        } catch(e) {
            alert('Network error: ' + e.message);
        }
    }

    function editDetailQuestion(qId) {
        const q = questions.find(q => q.id === qId);
        if (!q) return;
        editingQuestionId = qId;
        document.getElementById('detailQuestionText').value = q.text;
        document.getElementById('detailOptA').value = q.options[0];
        document.getElementById('detailOptB').value = q.options[1];
        document.getElementById('detailOptC').value = q.options[2];
        document.getElementById('detailOptD').value = q.options[3];
        const radios = document.querySelectorAll('input[name="detailCorrect"]');
        radios.forEach(r => { r.checked = r.value === q.correct; r.parentElement.classList.toggle('selected', r.value === q.correct); });
        document.getElementById('detailSaveText').textContent = 'Update Question';
        document.getElementById('detailSaveBtn').className = 'btn btn-warning';
        document.getElementById('detailQuestionFormCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function deleteDetailQuestion(qId) {
        if (!confirm('Delete this question?')) return;
        try {
            const result = await apiPost('delete-all-mock-test.php', { question_id: qId });
            if (result.status) {
                alert(result.message || 'Deleted!');
                questions = questions.filter(q => q.id !== qId);
                if (editingQuestionId === qId) { editingQuestionId = null; clearDetailForm(); }
                if (detailContext.setId) await loadQuestionsForSet(detailContext.setId);
                renderSetCards();
            } else {
                alert(result.message || 'Delete failed.');
            }
        } catch(e) {
            alert('Network error: ' + e.message);
        }
    }

    function clearDetailForm() {
        document.getElementById('detailQuestionText').value = '';
        document.getElementById('detailOptA').value = '';
        document.getElementById('detailOptB').value = '';
        document.getElementById('detailOptC').value = '';
        document.getElementById('detailOptD').value = '';
        document.querySelectorAll('input[name="detailCorrect"]').forEach(r => { r.checked = false; r.parentElement.classList.remove('selected'); });
        editingQuestionId = null;
        document.getElementById('detailSaveText').textContent = 'Add Question';
        document.getElementById('detailSaveBtn').className = 'btn btn-primary';
    }

    // ===== RESET =====
    function resetManageSection() {
        document.getElementById('classSelect').value = '';
        document.getElementById('subjectSelect').value = '';
        document.getElementById('chapterSelect').value = '';
        populateSelect('subject'); populateSelect('chapter');
        closeSetDetail();
        updateStepIndicator(); renderSetCards();
    }

    // ===== ADD NEW ITEMS =====
    function toggleAddInput(type) {
        const row = document.getElementById('add' + type.charAt(0).toUpperCase() + type.slice(1) + 'Row');
        row.style.display = row.style.display === 'none' ? 'flex' : 'none';
        if (row.style.display === 'flex') document.getElementById('new' + type.charAt(0).toUpperCase() + type.slice(1) + 'Name').focus();
    }
    function hideAddInput(type) {
        document.getElementById('add' + type.charAt(0).toUpperCase() + type.slice(1) + 'Row').style.display = 'none';
        document.getElementById('new' + type.charAt(0).toUpperCase() + type.slice(1) + 'Name').value = '';
    }

    function addNewItem(type) {
        const input = document.getElementById('new' + type.charAt(0).toUpperCase() + type.slice(1) + 'Name');
        const name = input.value.trim();
        if (!name) { alert('Please enter a ' + type + ' name.'); return; }

        if (type === 'class') {
            if (data.classes.some(c => c.class_name.toLowerCase() === name.toLowerCase())) { alert('This class already exists.'); return; }
            const id = nextClassId++; data.classes.push({ id, class_name: name }); data.subjects[id] = [];
            hideAddInput('class'); populateSelect('class');
            document.getElementById('classSelect').value = id; document.getElementById('classSelect').dispatchEvent(new Event('change'));
        } else if (type === 'subject') {
            const classId = parseInt(document.getElementById('classSelect').value); if (!classId) { alert('Please select a class first.'); return; }
            const subs = data.subjects[classId] || [];
            if (subs.some(s => s.subject_name.toLowerCase() === name.toLowerCase())) { alert('This subject already exists.'); return; }
            const id = nextSubjectId++; subs.push({ id, subject_name: name }); data.subjects[classId] = subs; data.chapters[id] = [];
            hideAddInput('subject'); populateSelect('subject');
            document.getElementById('subjectSelect').value = id; document.getElementById('subjectSelect').dispatchEvent(new Event('change'));
        } else if (type === 'chapter') {
            const subjectId = parseInt(document.getElementById('subjectSelect').value); if (!subjectId) { alert('Please select a subject first.'); return; }
            const chs = data.chapters[subjectId] || [];
            if (chs.some(ch => ch.chapter_name.toLowerCase() === name.toLowerCase())) { alert('This chapter already exists.'); return; }
            const id = nextChapterId++; chs.push({ id, chapter_name: name }); data.chapters[subjectId] = chs; data.sets[id] = [];
            hideAddInput('chapter'); populateSelect('chapter');
            document.getElementById('chapterSelect').value = id; document.getElementById('chapterSelect').dispatchEvent(new Event('change'));
        } else if (type === 'set') {
            const chapterId = parseInt(document.getElementById('chapterSelect').value); if (!chapterId) { alert('Please select a chapter first.'); return; }
            const sts = data.sets[chapterId] || [];
            if (sts.some(s => s.name.toLowerCase() === name.toLowerCase())) { alert('This set already exists.'); return; }
            const id = nextSetId++; sts.push({ id, name }); data.sets[chapterId] = sts;
            hideAddInput('set');
            renderSetCards();
        }
    }

    // ===== HELPERS =====        function getClassName(id) { const c = data.classes.find(x => x.id === id); return c ? c.class_name : ''; }        function getSubjectName(classId, subjectId) { const s = (data.subjects[classId] || []).find(x => x.id === subjectId); return s ? s.subject_name : ''; }        function getChapterName(subjectId, chapterId) { const c = (data.chapters[subjectId] || []).find(x => x.id === chapterId); return c ? c.chapter_name : ''; }
    function getSetName(chapterId, setId) { const s = (data.sets[chapterId] || []).find(x => x.id === setId); return s ? s.name : ''; }
    function formatDate(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        return d.getDate() + ' ' + ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()] + ' ' + d.getFullYear();
    }
    function timeToMinutes(t) {
        if (!t) return 0;
        const m = t.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
        if (!m) return 0;
        let h = parseInt(m[1], 10); const min = parseInt(m[2], 10);
        if (m[3]?.toUpperCase() === 'PM' && h !== 12) h += 12;
        if (m[3]?.toUpperCase() === 'AM' && h === 12) h = 0;
        return h * 60 + min;
    }

    // ===== ALL QUESTIONS BY DATE =====
    function renderAllQuestionsByDate() {
        const tbody = document.getElementById('allQuestionsTableBody');
        const countEl = document.getElementById('allQuestionsCount');
        if (questions.length === 0) {
            countEl.textContent = 'No questions yet';
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg></div><h3>No questions in the system</h3><p>Add questions using the Manage Questions section above.</p></div></td></tr>`;
            return;
        }
        const sorted = [...questions].sort((a, b) => a.date !== b.date ? b.date.localeCompare(a.date) : timeToMinutes(b.time||'') - timeToMinutes(a.time||''));
        const groups = {};
        sorted.forEach(q => {
            const key = `${q.className}||${q.subjectName}||${q.chapterName}||${q.setName}`;
            if (!groups[key]) groups[key] = { className: q.className, subjectName: q.subjectName, chapterName: q.chapterName, setName: q.setName, questions: [] };
            groups[key].questions.push(q);
        });
        const gk = Object.keys(groups);
        countEl.textContent = `${questions.length} questions across ${gk.length} groups · Newest first`;

        const labels = ['A','B','C','D'];
        const optClasses = ['opt-a','opt-b','opt-c','opt-d'];
        let html = '', gIdx = 0;

        gk.forEach(key => {
            const g = groups[key];
            const first = g.questions[0];
            html += `<tr class="group-header"><td colspan="7"><div class="group-path">
                <span class="class-badge">${g.className}</span><span class="group-arrow">›</span>
                <span class="subject-badge">${g.subjectName}</span><span class="group-arrow">›</span>
                <span class="chapter-badge">${g.chapterName}</span><span class="group-arrow">›</span>
                <span class="set-badge">${g.setName}</span>
                <span class="group-count"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg><strong>${g.questions.length}</strong> question${g.questions.length !== 1 ? 's' : ''}</span>
                <button class="btn btn-info btn-sm" onclick="navigateToSet(${first.classId}, ${first.subjectId}, ${first.chapterId}, ${first.setId})" style="margin-left:8px;" title="Add questions to this set">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Add
                </button>
            </div></td></tr>`;
            g.questions.forEach(q => {
                gIdx++;
                html += `<tr class="group-body">
                    <td><span class="q-num">${gIdx}</span></td>
                    <td style="font-weight:500;max-width:200px;">${q.text}</td>
                    <td style="min-width:160px;"><div style="display:flex;flex-wrap:wrap;gap:4px;">
                        <span class="class-badge">${q.className}</span>
                        <span class="subject-badge">${q.subjectName}</span>
                        <span class="chapter-badge">${q.chapterName}</span>
                        <span class="set-badge">${q.setName}</span>
                    </div></td>
                    <td><div class="options-inline">${q.options.map((opt, i) => `<span class="opt-chip ${optClasses[i]}${labels[i] === q.correct ? ' correct' : ''}">${labels[i]}. ${opt}</span>`).join('')}</div></td>
                    <td><span class="correct-ans-badge"><span class="opt-label-sm">${q.correct}</span> ${q.options[labels.indexOf(q.correct)]}</span></td>
                    <td style="white-space:nowrap;font-size:13px;color:#64748b;"><span>${formatDate(q.date)}</span><span style="display:block;font-size:11px;color:#94a3b8;">${q.time||''}</span></td>
                    <td><div class="actions-cell">
                        <button class="btn btn-warning btn-sm" onclick="editAllQuestion(${q.id})"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteAllQuestion(${q.id})"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg> Delete</button>
                    </div></td>
                </tr>`;
            });
        });
        tbody.innerHTML = html;
    }

    // ===== EDIT/DELETE FROM ALL QUESTIONS =====
    function editAllQuestion(qId) {
        const q = questions.find(q => q.id === qId);
        if (!q) return;
        // Navigate to the set and open detail view
        const { classId, subjectId, chapterId, setId } = q;
        const classSel = document.getElementById('classSelect');
        const subjectSel = document.getElementById('subjectSelect');
        const chapterSel = document.getElementById('chapterSelect');
        classSel.value = classId; classSel.dispatchEvent(new Event('change'));
        subjectSel.value = subjectId; subjectSel.dispatchEvent(new Event('change'));
        chapterSel.value = chapterId; chapterSel.dispatchEvent(new Event('change'));
        // Open the set detail
        openSetDetail(setId);
        // Fill the form
        editingQuestionId = qId;
        document.getElementById('detailQuestionText').value = q.text;
        document.getElementById('detailOptA').value = q.options[0];
        document.getElementById('detailOptB').value = q.options[1];
        document.getElementById('detailOptC').value = q.options[2];
        document.getElementById('detailOptD').value = q.options[3];
        document.querySelectorAll('input[name="detailCorrect"]').forEach(r => { r.checked = r.value === q.correct; r.parentElement.classList.toggle('selected', r.value === q.correct); });
        document.getElementById('detailSaveText').textContent = 'Update Question';
        document.getElementById('detailSaveBtn').className = 'btn btn-warning';
    }

    async function deleteAllQuestion(qId) {
        if (!confirm('Delete this question?')) return;
        try {
            const result = await apiPost('delete-all-mock-test.php', { question_id: qId });
            if (result.status) {
                alert(result.message || 'Deleted!');
                questions = questions.filter(q => q.id !== qId);
                allQuestions = allQuestions.filter(q => q.id !== qId);
                renderAllQuestionsByDate();
                renderSetCards();
                if (selectedSetId) renderDetailTable();
                if (editingQuestionId === qId) { editingQuestionId = null; clearDetailForm(); }
            } else {
                alert(result.message || 'Delete failed.');
            }
        } catch(e) {
            alert('Network error: ' + e.message);
        }
    }

    // ===== NAVIGATE TO SET FROM ALL QUESTIONS =====
    function navigateToSet(classId, subjectId, chapterId, setId) {
        const classSel = document.getElementById('classSelect');
        const subjectSel = document.getElementById('subjectSelect');
        const chapterSel = document.getElementById('chapterSelect');
        classSel.value = classId; classSel.dispatchEvent(new Event('change'));
        subjectSel.value = subjectId; subjectSel.dispatchEvent(new Event('change'));
        chapterSel.value = chapterId; chapterSel.dispatchEvent(new Event('change'));
        openSetDetail(setId);
        clearDetailForm();
        // Scroll and focus after render
        setTimeout(() => {
            document.getElementById('detailQuestionText')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => document.getElementById('detailQuestionText')?.focus(), 150);
        }, 100);
    }

    // ===== CORRECT ANSWER RADIO TOGGLE =====
    document.querySelectorAll('.correct-answer-group input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            this.closest('.correct-answer-group').querySelectorAll('label').forEach(l => l.classList.remove('selected'));
            if (this.checked) this.parentElement.classList.add('selected');
        });
    });

    // ===== INIT =====
    document.addEventListener('DOMContentLoaded', init);
</script>
</body>
</html>

