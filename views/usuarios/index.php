<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../Views/login/index.php');
    exit;
}
require_once __DIR__ . '/../../config/conexion.php';

$stmt = $pdo->prepare('SELECT usuario, email FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$usuario      = $data['usuario'] ?? 'Admin';
$email        = $data['email']   ?? '';
$nombre_corto = explode(' ', $usuario)[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Gestión de Usuarios</title>
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link rel="stylesheet" href="../../assets/css/light_theme.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0d1117;
            --bg2:        #161b22;
            --bg3:        #21262d;
            --border:     #30363d;
            --text:       #e6edf3;
            --text-muted: #8b949e;
            --accent:     #00c4d4;
            --accent2:    #0078a8;
            --green:      #3fb950;
            --yellow:     #d29922;
            --red:        #f85149;
            --purple:     #bc8cff;
            --radius:     16px;
            --sidebar-w:  260px;
            --topbar-h:   64px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0;
            z-index: 50;
        }
        .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid var(--border); }
        .sidebar-logo img { height: auto; width: 110px; display: block; margin: 0 auto; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--text-muted); padding: 8px 12px 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px;
            border-radius: 10px; color: var(--text-muted); font-size: 0.9rem;
            font-weight: 500; cursor: pointer; transition: all 0.2s ease;
            text-decoration: none; margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg3); color: var(--text); }
        .nav-item.active { background: rgba(0, 196, 212, 0.1); color: var(--accent); }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid var(--border); }
        .user-card {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            border-radius: 12px; background: var(--bg3);
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.9rem; color: #0d1117; flex-shrink: 0;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-info .name { font-size: 0.85rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 0.72rem; color: var(--text-muted); }
        .logout-btn {
            background: none; border: none; color: var(--text-muted); cursor: pointer;
            padding: 4px; border-radius: 6px; display: flex; transition: color 0.2s;
        }
        .logout-btn:hover { color: var(--red); }
        .logout-btn svg { width: 16px; height: 16px; }

        /* ── MAIN ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

        /* ── TOPBAR ── */
        .topbar {
            height: var(--topbar-h); background: var(--bg2); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; padding: 0 28px; flex-shrink: 0;
        }
        .topbar-title h1 { font-size: 1.1rem; font-weight: 700; color: var(--text); }
        .topbar-title p { font-size: 0.78rem; color: var(--text-muted); }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }

        /* ── CONTENT ── */
        .content { flex: 1; overflow-y: auto; padding: 28px; }

        /* ── TOOLBAR ── */
        .toolbar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; gap: 12px; flex-wrap: wrap;
        }
        .toolbar-left { display: flex; align-items: center; gap: 10px; }
        .toolbar-right { display: flex; align-items: center; gap: 8px; }

        .search-box {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg2); border: 1px solid var(--border);
            border-radius: 10px; padding: 9px 14px;
        }
        .search-box svg { width: 15px; height: 15px; color: var(--text-muted); }
        .search-box input {
            background: none; border: none; outline: none;
            color: var(--text); font-family: 'Inter', sans-serif; font-size: 0.85rem; width: 220px;
        }
        .search-box input::placeholder { color: var(--text-muted); }

        .filter-select {
            background: var(--bg2); border: 1px solid var(--border); color: var(--text);
            padding: 9px 14px; border-radius: 10px; font-family: 'Inter', sans-serif;
            font-size: 0.82rem; cursor: pointer; outline: none;
        }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--accent); color: #0d1117; border: none;
            padding: 9px 18px; border-radius: 10px; font-size: 0.85rem;
            font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: all 0.2s; text-decoration: none;
        }
        .btn-primary:hover { background: #00d9eb; transform: translateY(-1px); }
        .btn-primary svg { width: 15px; height: 15px; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--bg3); color: var(--text-muted); border: 1px solid var(--border);
            padding: 9px 14px; border-radius: 10px; font-size: 0.82rem;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
        .btn-secondary.spinning svg { animation: spin 0.8s linear infinite; }
        .btn-secondary svg { width: 14px; height: 14px; }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px;
        }
        .mini-stat {
            background: var(--bg2); border: 1px solid var(--border); border-radius: 12px;
            padding: 16px 20px; display: flex; align-items: center; gap: 14px;
            transition: border-color 0.2s, transform 0.2s;
        }
        .mini-stat:hover { border-color: var(--accent); transform: translateY(-2px); }
        .mini-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .mini-stat-icon svg { width: 18px; height: 18px; }
        .mini-stat-icon.cyan  { background: rgba(0,196,212,0.15);  color: var(--accent); }
        .mini-stat-icon.green { background: rgba(63,185,80,0.15);   color: var(--green); }
        .mini-stat-icon.yellow{ background: rgba(210,153,34,0.15);  color: var(--yellow); }
        .mini-stat-icon.purple{ background: rgba(188,140,255,0.15); color: var(--purple); }
        .mini-stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text); }
        .mini-stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

        /* ── TABLE CARD ── */
        .card {
            background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius);
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px; border-bottom: 1px solid var(--border);
        }
        .card-title { font-size: 0.95rem; font-weight: 700; }
        .card-subtitle { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: var(--text-muted);
            padding: 12px 20px; text-align: left; border-bottom: 1px solid var(--border);
        }
        tbody tr { border-bottom: 1px solid rgba(48,54,61,0.5); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(33,38,45,0.7); }
        tbody td { padding: 13px 20px; font-size: 0.85rem; vertical-align: middle; }

        .user-cell { display: flex; align-items: center; gap: 10px; }
        .mini-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, #00c4d4, #0078a8);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: #0d1117; flex-shrink: 0;
        }
        .uname { font-weight: 600; color: var(--text); }
        .uemail { font-size: 0.74rem; color: var(--text-muted); }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
        }
        .badge .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge.green  { background: rgba(63,185,80,0.15);   color: var(--green); }
        .badge.yellow { background: rgba(210,153,34,0.15);  color: var(--yellow); }
        .badge.cyan   { background: rgba(0,196,212,0.15);   color: var(--accent); }
        .badge.gray   { background: rgba(139,148,158,0.15); color: var(--text-muted); }
        .badge.red    { background: rgba(248,81,73,0.15);   color: var(--red); }

        /* ── ACTION BUTTONS ── */
        .actions { display: flex; gap: 6px; align-items: center; }
        .act-btn {
            width: 30px; height: 30px; border-radius: 7px; border: 1px solid var(--border);
            background: var(--bg3); color: var(--text-muted); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.18s; flex-shrink: 0;
        }
        .act-btn svg { width: 14px; height: 14px; pointer-events: none; }
        .act-btn:hover { border-color: var(--accent); color: var(--accent); }
        .act-btn.danger:hover { border-color: var(--red); color: var(--red); }
        .act-btn.warn:hover { border-color: var(--yellow); color: var(--yellow); }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state svg { width: 48px; height: 48px; opacity: 0.3; margin-bottom: 12px; }
        .empty-state p { font-size: 0.9rem; }

        /* ── SKELETON ── */
        .skeleton {
            background: linear-gradient(90deg, var(--bg3) 25%, var(--bg2) 50%, var(--bg3) 75%);
            background-size: 400% 100%; animation: shimmer 1.5s infinite;
            border-radius: 6px; height: 14px; display: inline-block;
        }
        @keyframes shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── MODAL ── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdropFilter: blur(4px);
            z-index: 100; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.2s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal {
            background: var(--bg2); border: 1px solid var(--border); border-radius: 20px;
            width: 480px; max-width: 95vw; padding: 28px;
            transform: scale(0.95) translateY(10px); transition: transform 0.25s;
        }
        .modal-overlay.open .modal { transform: scale(1) translateY(0); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .modal-title { font-size: 1.1rem; font-weight: 700; }
        .modal-close { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 6px; }
        .modal-close:hover { color: var(--red); }
        .modal-close svg { width: 18px; height: 18px; }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
        .form-input {
            width: 100%; background: var(--bg3); border: 1px solid var(--border);
            color: var(--text); padding: 10px 14px; border-radius: 10px;
            font-family: 'Inter', sans-serif; font-size: 0.875rem; outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus { border-color: var(--accent); }
        .form-note { font-size: 0.74rem; color: var(--text-muted); margin-top: 5px; }

        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
        .btn-cancel {
            background: var(--bg3); color: var(--text-muted); border: 1px solid var(--border);
            padding: 9px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
        }
        .btn-cancel:hover { border-color: var(--text-muted); }
        .btn-danger {
            background: rgba(248,81,73,0.15); color: var(--red); border: 1px solid rgba(248,81,73,0.4);
            padding: 9px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 700;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
        }
        .btn-danger:hover { background: rgba(248,81,73,0.25); }
        .btn-save {
            background: var(--accent); color: #0d1117; border: none;
            padding: 9px 22px; border-radius: 10px; font-size: 0.85rem; font-weight: 700;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
        }
        .btn-save:hover { background: #00d9eb; }

        /* Confirm modal */
        .confirm-icon { width: 52px; height: 52px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .confirm-icon.danger { background: rgba(248,81,73,0.15); color: var(--red); }
        .confirm-icon.warn   { background: rgba(210,153,34,0.15);  color: var(--yellow); }
        .confirm-icon svg { width: 26px; height: 26px; }
        .confirm-title { text-align: center; font-size: 1.05rem; font-weight: 700; margin-bottom: 8px; }
        .confirm-desc  { text-align: center; font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }

        /* ── LIGHT THEME ── */
        body.light-theme {
            --bg: #f6f8fa; --bg2: #ffffff; --bg3: #f0f2f4;
            --border: #d0d7de; --text: #1f2328; --text-muted: #59636e;
        }
        body.light-theme .sidebar { background: #fff; }
        body.light-theme .topbar  { background: #fff; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        /* Theme switch */
        .theme-switch { display:flex; gap:4px; background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:3px; }
        .theme-switch button { background:none; border:none; color:var(--text-muted); cursor:pointer; padding:5px; border-radius:5px; display:flex; align-items:center; transition:all 0.2s; }
        .theme-switch button:hover { color:var(--text); }
        .theme-switch button.active { background:var(--bg2); color:var(--accent); }
        .theme-switch button svg { width:15px; height:15px; }

        @media (max-width: 1100px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .sidebar { display:none; } .main { margin-left:0; } }
    </style>
</head>
<body>
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/toast.js"></script>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../../assets/img/logodark_02.png" alt="HEVELAB" id="dash-logo">
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a class="nav-item" href="../../Dashboard.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a class="nav-item active" href="index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuarios
        </a>
        <a class="nav-item" href="../configuracion/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/></svg>
            Configuración
        </a>
        <div class="nav-section-label" style="margin-top:16px;">Sistema</div>
        <a class="nav-item" href="../../Views/login/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Cerrar Sesión
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($nombre_corto, 0, 1)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($usuario) ?></div>
                <div class="role"><?= htmlspecialchars($email) ?></div>
            </div>
            <a href="../../Views/login/index.php" class="logout-btn" title="Salir">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">
    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-title">
            <h1>Gestión de Usuarios</h1>
            <p>HEVELAB · Administración de cuentas y accesos</p>
        </div>
        <div class="topbar-actions">
            <div class="theme-switch" id="theme-switch">
                <button id="btn-dark" title="Modo oscuro">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <button id="btn-light" title="Modo claro">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="content">

        <!-- STATS ROW -->
        <div class="stats-row">
            <div class="mini-stat">
                <div class="mini-stat-icon cyan">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div class="mini-stat-value" id="s-total">—</div>
                    <div class="mini-stat-label">Total usuarios</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <div class="mini-stat-value" id="s-verificados">—</div>
                    <div class="mini-stat-label">Verificados</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div>
                    <div class="mini-stat-value" id="s-facial">—</div>
                    <div class="mini-stat-label">Con biometría</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="mini-stat-value" id="s-pendientes">—</div>
                    <div class="mini-stat-label">Pendientes OTP</div>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="search-input" placeholder="Buscar por nombre o email…">
                </div>
                <select class="filter-select" id="filter-status">
                    <option value="all">Todos los estados</option>
                    <option value="verified">Verificados</option>
                    <option value="pending">Pendientes</option>
                </select>
                <select class="filter-select" id="filter-auth">
                    <option value="all">Todos los métodos</option>
                    <option value="facial">Biometría facial</option>
                    <option value="pwd">Contraseña + OTP</option>
                </select>
            </div>
            <div class="toolbar-right">
                <button class="btn-secondary" id="btn-refresh">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Refrescar
                </button>
                <button class="btn-primary" id="btn-nuevo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nuevo usuario
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Usuarios registrados</div>
                    <div class="card-subtitle" id="table-count">Cargando…</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Autenticación</th>
                            <th>Último acceso</th>
                            <th>Registrado</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <tr><td colspan="7" style="text-align:center;padding:40px;"><span class="skeleton" style="width:55%;"></span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- ══ MODAL: CREAR / EDITAR ══ -->
<div class="modal-overlay" id="modal-form">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="modal-form-title">Nuevo usuario</div>
            <button class="modal-close" id="modal-form-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <input type="hidden" id="edit-id">
        <div class="form-group">
            <label class="form-label" for="inp-usuario">Nombre de usuario</label>
            <input class="form-input" type="text" id="inp-usuario" placeholder="Ej: juan.perez">
        </div>
        <div class="form-group">
            <label class="form-label" for="inp-email">Correo electrónico</label>
            <input class="form-input" type="email" id="inp-email" placeholder="correo@ejemplo.com">
        </div>
        <div class="form-group" id="group-password">
            <label class="form-label" for="inp-password">Contraseña</label>
            <input class="form-input" type="password" id="inp-password" placeholder="Mínimo 8 caracteres">
            <div class="form-note">El usuario podrá cambiarla más adelante.</div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" id="modal-form-cancel">Cancelar</button>
            <button class="btn-save" id="modal-form-save">Guardar</button>
        </div>
    </div>
</div>

<!-- ══ MODAL: CONFIRMAR ELIMINAR ══ -->
<div class="modal-overlay" id="modal-confirm">
    <div class="modal" style="max-width:380px;">
        <div class="confirm-icon danger" id="confirm-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
        </div>
        <div class="confirm-title" id="confirm-title">¿Eliminar usuario?</div>
        <div class="confirm-desc" id="confirm-desc">Esta acción no se puede deshacer. El usuario y todos sus datos serán eliminados permanentemente.</div>
        <div class="modal-footer" style="justify-content:center;gap:12px;">
            <button class="btn-cancel" id="confirm-cancel">Cancelar</button>
            <button class="btn-danger" id="confirm-ok">Confirmar</button>
        </div>
    </div>
</div>

<script>
// ── API BASE ────────────────────────────────────
const API = '../../api/usuarios_api.php';
let allUsers = [];
let confirmCallback = null;

// ── CARGAR USUARIOS ─────────────────────────────
async function loadUsers(spinner = true) {
    if (spinner) {
        const btn = document.getElementById('btn-refresh');
        btn.classList.add('spinning');
    }
    try {
        const res  = await fetch(`${API}?action=listar`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        allUsers = data.usuarios || [];
        renderTable();
        updateStats();
    } catch (e) {
        showToast('Error al cargar usuarios: ' + e.message, 'error');
    } finally {
        document.getElementById('btn-refresh').classList.remove('spinning');
    }
}

// ── ESTADÍSTICAS ────────────────────────────────
function updateStats() {
    const total      = allUsers.length;
    const verificados = allUsers.filter(u => u.verificado == 1).length;
    const facial     = allUsers.filter(u => u.tiene_facial == 1).length;
    const pendientes = allUsers.filter(u => u.verificado == 0).length;
    document.getElementById('s-total').textContent      = total;
    document.getElementById('s-verificados').textContent = verificados;
    document.getElementById('s-facial').textContent     = facial;
    document.getElementById('s-pendientes').textContent  = pendientes;
}

// ── RENDERIZAR TABLA ─────────────────────────────
function renderTable() {
    const tbody   = document.getElementById('tabla-body');
    const q       = document.getElementById('search-input').value.toLowerCase();
    const fStatus = document.getElementById('filter-status').value;
    const fAuth   = document.getElementById('filter-auth').value;

    let list = allUsers;

    if (q) list = list.filter(u =>
        u.usuario.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
    );
    if (fStatus === 'verified') list = list.filter(u => u.verificado == 1);
    if (fStatus === 'pending')  list = list.filter(u => u.verificado == 0);
    if (fAuth === 'facial') list = list.filter(u => u.tiene_facial == 1);
    if (fAuth === 'pwd')    list = list.filter(u => u.tiene_facial == 0);

    document.getElementById('table-count').textContent =
        `${list.length} de ${allUsers.length} usuario${allUsers.length !== 1 ? 's' : ''}`;

    if (!list.length) {
        tbody.innerHTML = `
            <tr><td colspan="7">
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <p>No se encontraron usuarios con los filtros seleccionados.</p>
                </div>
            </td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((u, i) => `
        <tr>
            <td style="color:var(--text-muted);font-size:0.76rem;">${i + 1}</td>
            <td>
                <div class="user-cell">
                    <div class="mini-avatar">${u.usuario.charAt(0).toUpperCase()}</div>
                    <div>
                        <div class="uname">${esc(u.usuario)}</div>
                        <div class="uemail">${esc(u.email)}</div>
                    </div>
                </div>
            </td>
            <td>
                ${u.verificado == 1
                    ? '<span class="badge green"><span class="dot"></span>Verificado</span>'
                    : '<span class="badge yellow"><span class="dot"></span>Pendiente</span>'}
            </td>
            <td>
                ${u.tiene_facial == 1
                    ? '<span class="badge cyan">Biometría facial</span>'
                    : '<span class="badge gray">Contraseña + OTP</span>'}
            </td>
            <td style="font-size:0.8rem;color:var(--text-muted);">${fmtDate(u.ultimo_login)}</td>
            <td style="font-size:0.8rem;color:var(--text-muted);">${fmtDate(u.created_at)}</td>
            <td>
                <div class="actions">
                    <button class="act-btn" title="Editar" onclick="openEdit(${u.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="act-btn warn" title="${u.verificado == 1 ? 'Suspender' : 'Verificar'}" onclick="toggleVerificado(${u.id}, ${u.verificado == 1 ? 0 : 1})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </button>
                    ${u.tiene_facial == 1 ? `
                    <button class="act-btn warn" title="Eliminar biometría facial" onclick="resetFacial(${u.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </button>` : ''}
                    <button class="act-btn danger" title="Eliminar usuario" onclick="confirmDelete(${u.id}, '${esc(u.usuario)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── UTILIDADES ──────────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}
function fmtDate(str) {
    if (!str || str === '0000-00-00 00:00:00') return '<span style="color:var(--text-muted)">—</span>';
    const d = new Date(str);
    return d.toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric' })
        + ' ' + d.toLocaleTimeString('es-ES', { hour:'2-digit', minute:'2-digit' });
}

// ── MODAL CREAR ──────────────────────────────────
function openCreate() {
    document.getElementById('modal-form-title').textContent = 'Nuevo usuario';
    document.getElementById('edit-id').value = '';
    document.getElementById('inp-usuario').value = '';
    document.getElementById('inp-email').value   = '';
    document.getElementById('inp-password').value = '';
    document.getElementById('group-password').style.display = '';
    document.getElementById('modal-form').classList.add('open');
}

// ── MODAL EDITAR ─────────────────────────────────
function openEdit(id) {
    const u = allUsers.find(x => x.id == id);
    if (!u) return;
    document.getElementById('modal-form-title').textContent = 'Editar usuario';
    document.getElementById('edit-id').value     = u.id;
    document.getElementById('inp-usuario').value = u.usuario;
    document.getElementById('inp-email').value   = u.email;
    document.getElementById('inp-password').value = '';
    document.getElementById('group-password').style.display = 'none';
    document.getElementById('modal-form').classList.add('open');
}

function closeFormModal() {
    document.getElementById('modal-form').classList.remove('open');
}

// ── GUARDAR USUARIO ──────────────────────────────
document.getElementById('modal-form-save').addEventListener('click', async () => {
    const id      = document.getElementById('edit-id').value;
    const usuario = document.getElementById('inp-usuario').value.trim();
    const email   = document.getElementById('inp-email').value.trim();
    const password = document.getElementById('inp-password').value;

    if (!usuario || !email) { showToast('Completa todos los campos.', 'warning'); return; }

    const action = id ? 'editar' : 'crear';
    const body   = id ? { id, usuario, email } : { usuario, email, password };

    if (!id && !password) { showToast('La contraseña es requerida.', 'warning'); return; }

    try {
        const res  = await fetch(`${API}?action=${action}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showToast(data.message, 'success');
        closeFormModal();
        loadUsers(false);
    } catch (e) {
        showToast(e.message, 'error');
    }
});

// ── TOGGLE VERIFICADO ────────────────────────────
async function toggleVerificado(id, verificado) {
    try {
        const res  = await fetch(`${API}?action=toggle_verificado`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, verificado })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showToast(data.message, 'success');
        loadUsers(false);
    } catch (e) { showToast(e.message, 'error'); }
}

// ── RESET FACIAL ─────────────────────────────────
function resetFacial(id) {
    openConfirm(
        '¿Eliminar biometría facial?',
        'Se eliminará el descriptor facial de este usuario. Deberá registrar su rostro de nuevo para usar ingreso biométrico.',
        'warn',
        async () => {
            const res  = await fetch(`${API}?action=resetear_facial`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            showToast(data.message, 'success');
            loadUsers(false);
        }
    );
}

// ── ELIMINAR ─────────────────────────────────────
function confirmDelete(id, nombre) {
    openConfirm(
        `¿Eliminar a "${nombre}"?`,
        'Esta acción es permanente. Se eliminarán todos los datos de este usuario, incluyendo su descriptor facial.',
        'danger',
        async () => {
            const res  = await fetch(`${API}?action=eliminar`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            showToast(data.message, 'success');
            loadUsers(false);
        }
    );
}

// ── CONFIRM MODAL ────────────────────────────────
function openConfirm(title, desc, type, cb) {
    confirmCallback = cb;
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-desc').textContent  = desc;

    const icon = document.getElementById('confirm-icon');
    icon.className = 'confirm-icon ' + (type === 'danger' ? 'danger' : 'warn');

    const btn = document.getElementById('confirm-ok');
    btn.className = type === 'danger' ? 'btn-danger' : 'btn-save';
    btn.textContent = 'Confirmar';

    document.getElementById('modal-confirm').classList.add('open');
}

document.getElementById('confirm-ok').addEventListener('click', async () => {
    if (!confirmCallback) return;
    try {
        await confirmCallback();
    } catch (e) { showToast(e.message, 'error'); }
    document.getElementById('modal-confirm').classList.remove('open');
    confirmCallback = null;
});

document.getElementById('confirm-cancel').addEventListener('click', () => {
    document.getElementById('modal-confirm').classList.remove('open');
    confirmCallback = null;
});

// Cerrar al click fuera del modal
document.getElementById('modal-form').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-form')) closeFormModal();
});
document.getElementById('modal-confirm').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-confirm')) {
        document.getElementById('modal-confirm').classList.remove('open');
        confirmCallback = null;
    }
});

// ── EVENTOS ──────────────────────────────────────
document.getElementById('btn-nuevo').addEventListener('click', openCreate);
document.getElementById('modal-form-close').addEventListener('click', closeFormModal);
document.getElementById('modal-form-cancel').addEventListener('click', closeFormModal);
document.getElementById('btn-refresh').addEventListener('click', () => loadUsers());
document.getElementById('search-input').addEventListener('input', renderTable);
document.getElementById('filter-status').addEventListener('change', renderTable);
document.getElementById('filter-auth').addEventListener('change', renderTable);

// ── INIT ──────────────────────────────────────────
loadUsers();
setTimeout(() => showToast('👥 Gestión de usuarios cargada.', 'info', 3000), 600);
</script>
</body>
</html>
