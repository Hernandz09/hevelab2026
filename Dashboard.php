<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: Views/login/index.php');
    exit;
}

require_once __DIR__ . '/config/conexion.php';

$usuario = 'Usuario';
$email   = '';
$stmt = $pdo->prepare('SELECT usuario, email FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    // usuario ya tiene el nombre completo si fue registrado con el nuevo flujo
    $usuario = $data['usuario'];
    $email   = $data['email'];
}

// Nombre amigable: primera palabra del nombre o el usuario completo
$nombre_corto = explode(' ', $usuario)[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Dashboard</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/light_theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo img {
            height: auto;
            width: 110px;
            display: block;
            margin: 0 auto;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 8px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg3);
            color: var(--text);
        }

        .nav-item.active {
            background: rgba(0, 196, 212, 0.1);
            color: var(--accent);
        }

        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: var(--bg3);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #0d1117;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-info .name { font-size: 0.85rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 0.72rem; color: var(--text-muted); }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            transition: color 0.2s;
        }

        .logout-btn:hover { color: var(--red); }
        .logout-btn svg { width: 16px; height: 16px; }

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* ── TOPBAR ── */
        .topbar {
            height: var(--topbar-h);
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            flex-shrink: 0;
        }

        .topbar-title h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-title p {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid var(--border);
            color: var(--text-muted);
            background: var(--bg3);
        }

        .topbar-badge.green { border-color: var(--green); color: var(--green); background: rgba(63, 185, 80, 0.1); }

        .cleanup-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(248, 81, 73, 0.1);
            border: 1px solid rgba(248, 81, 73, 0.3);
            color: var(--red);
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cleanup-btn:hover { background: rgba(248, 81, 73, 0.2); }
        .cleanup-btn svg { width: 14px; height: 14px; }

        /* ── CONTENT ── */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 28px;
        }

        /* ── STAT CARDS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: border-color 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg { width: 22px; height: 22px; }
        .stat-icon.cyan { background: rgba(0, 196, 212, 0.15); color: var(--accent); }
        .stat-icon.green { background: rgba(63, 185, 80, 0.15); color: var(--green); }
        .stat-icon.yellow { background: rgba(210, 153, 34, 0.15); color: var(--yellow); }
        .stat-icon.purple { background: rgba(188, 140, 255, 0.15); color: var(--purple); }
        .stat-icon.red { background: rgba(248, 81, 73, 0.15); color: var(--red); }

        .stat-info { flex: 1; }
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 6px;
            font-weight: 500;
        }
        .stat-trend {
            font-size: 0.72rem;
            margin-top: 4px;
            font-weight: 600;
        }
        .stat-trend.up { color: var(--green); }
        .stat-trend.warn { color: var(--yellow); }

        /* ── CHARTS ROW ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 16px;
            margin-bottom: 28px;
        }

        .chart-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
        }

        .chart-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .chart-wrap {
            position: relative;
            height: 220px;
        }

        .chart-wrap-donut {
            position: relative;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .donut-center {
            position: absolute;
            text-align: center;
            pointer-events: none;
        }

        .donut-center .big-num {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
        }

        .donut-center .small-label {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        .chart-legend {
            display: flex;
            gap: 20px;
            margin-top: 16px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        /* ── USERS TABLE ── */
        .table-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .table-title {
            font-size: 0.95rem;
            font-weight: 700;
        }

        .table-search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 12px;
        }

        .table-search svg { width: 14px; height: 14px; color: var(--text-muted); flex-shrink: 0; }
        .table-search input {
            background: none;
            border: none;
            outline: none;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            width: 160px;
        }

        .table-search input::placeholder { color: var(--text-muted); }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 12px 24px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid rgba(48, 54, 61, 0.5);
            transition: background 0.15s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg3); }

        tbody td {
            padding: 14px 24px;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-cell .mini-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00c4d4, #0078a8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
            color: #0d1117;
            flex-shrink: 0;
        }

        .user-cell .uname { font-weight: 600; color: var(--text); }
        .user-cell .uemail { font-size: 0.75rem; color: var(--text-muted); }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge.green { background: rgba(63, 185, 80, 0.15); color: var(--green); }
        .badge.yellow { background: rgba(210, 153, 34, 0.15); color: var(--yellow); }
        .badge.cyan { background: rgba(0, 196, 212, 0.15); color: var(--accent); }
        .badge.gray { background: rgba(139, 148, 158, 0.15); color: var(--text-muted); }
        .badge .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            color: var(--green);
            font-weight: 600;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .refresh-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .refresh-btn:hover { border-color: var(--accent); color: var(--accent); }
        .refresh-btn svg { width: 13px; height: 13px; }
        .refresh-btn.spinning svg { animation: spin 0.8s linear infinite; }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loading-row td {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .skeleton {
            background: linear-gradient(90deg, var(--bg3) 25%, var(--bg2) 50%, var(--bg3) 75%);
            background-size: 400% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 6px;
            height: 14px;
            display: inline-block;
        }

        @keyframes shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }

        /* ── LIGHT MODE ── */
        body.light-theme {
            --bg:         #f6f8fa;
            --bg2:        #ffffff;
            --bg3:        #f0f2f4;
            --border:     #d0d7de;
            --text:       #1f2328;
            --text-muted: #59636e;
        }

        body.light-theme .sidebar { background: #fff; }
        body.light-theme .topbar  { background: #fff; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        /* Responsive */
        @media (max-width: 1100px) {
            .charts-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<!-- Theme loader -->
<script src="assets/js/theme.js"></script>
<script src="assets/js/toast.js"></script>

<!-- ══ SIDEBAR ═══════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="assets/img/logodark_02.png" alt="HEVELAB" id="dash-logo">
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a class="nav-item active" href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a class="nav-item" href="views/usuarios/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuarios
        </a>
        <a class="nav-item" href="views/configuracion/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/></svg>
            Configuración
        </a>

        <div class="nav-section-label" style="margin-top:16px;">Sistema</div>
        <a class="nav-item" href="Views/login/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Cerrar Sesión
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($nombre_corto, 0, 1)) ?></div>
            <div class="user-info">
                <div class="name" title="<?= htmlspecialchars($usuario) ?>"><?= htmlspecialchars($usuario) ?></div>
                <div class="role"><?= htmlspecialchars($email) ?></div>
            </div>
            <a href="Views/login/index.php" class="logout-btn" title="Salir">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </div>
</aside>

<!-- ══ MAIN ═════════════════════════════════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-title">
            <h1>Análisis del Sistema</h1>
            <p>VIISION ERP · <span id="fecha-actual"></span></p>
        </div>
        <div class="topbar-actions">
            <span class="live-indicator">
                <span class="live-dot"></span>
                En línea
            </span>
            <div class="topbar-badge" id="badge-pendientes" style="display:none;"></div>
            <button class="cleanup-btn" id="btn-cleanup" title="Eliminar usuarios no verificados manualmente">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Limpiar no verificados
            </button>
            <!-- Theme Switcher -->
            <div class="theme-switch-wrap" style="position:relative;top:auto;right:auto;">
                <div class="theme-switch" id="theme-switch">
                    <button id="btn-dark" title="Modo oscuro">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <button id="btn-light" title="Modo claro">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="content">

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon cyan">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-total">—</div>
                    <div class="stat-label">Usuarios totales</div>
                    <div class="stat-trend up" id="stat-semana-trend"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-facial">—</div>
                    <div class="stat-label">Con biometría facial</div>
                    <div class="stat-trend" id="stat-facial-pct"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-hoy">—</div>
                    <div class="stat-label">Registros hoy</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-pendientes">—</div>
                    <div class="stat-label">Pendientes de OTP</div>
                    <div class="stat-trend warn" id="stat-pendientes-info"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-eliminados">0</div>
                    <div class="stat-label">Eliminados (sesión)</div>
                    <div class="stat-trend" style="color:var(--text-muted);font-size:0.7rem;">No verificaron OTP a tiempo</div>
                </div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="charts-grid">
            <!-- Gráfica de área: registros 30 días -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Registros en los últimos 30 días</div>
                        <div class="chart-subtitle">Solamente usuarios verificados (OTP validado)</div>
                    </div>
                    <button class="refresh-btn" id="btn-refresh-chart">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Actualizar
                    </button>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartRegistros"></canvas>
                </div>
            </div>

            <!-- Gráfica donut: facial vs contraseña -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Métodos de autenticación</div>
                        <div class="chart-subtitle">Distribución de tipo de acceso</div>
                    </div>
                </div>
                <div class="chart-wrap-donut">
                    <canvas id="chartMetodos"></canvas>
                    <div class="donut-center">
                        <div class="big-num" id="donut-center-num">—</div>
                        <div class="small-label">usuarios</div>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><div class="legend-dot" style="background:#00c4d4;"></div>Biometría facial</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#3fb950;"></div>Contraseña + OTP</div>
                </div>
            </div>
        </div>

        <!-- USERS TABLE -->
        <div class="table-card" id="section-usuarios">
            <div class="table-header">
                <div>
                    <div class="table-title">Usuarios registrados</div>
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:2px;">Se actualiza automáticamente cada 30 segundos</div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <div class="table-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Buscar usuario...">
                    </div>
                    <button class="refresh-btn" id="btn-refresh-table">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Refrescar
                    </button>
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
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios">
                        <tr class="loading-row">
                            <td colspan="6">
                                <span class="skeleton" style="width:60%;"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
// ══════════════════════════════════════════════
//  CONFIGURACIÓN
// ══════════════════════════════════════════════
const API = 'api/dashboard_data.php';
let chartRegistros = null;
let chartMetodos   = null;
let totalEliminados = 0;
let allUsers = [];

// ══════════════════════════════════════════════
//  FECHA Y HORA
// ══════════════════════════════════════════════
function updateClock() {
    const now = new Date();
    document.getElementById('fecha-actual').textContent = now.toLocaleDateString('es-ES', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });
}
updateClock();
setInterval(updateClock, 60000);

// ══════════════════════════════════════════════
//  UTILIDADES
// ══════════════════════════════════════════════
function fmtDate(str) {
    if (!str) return '<span style="color:var(--text-muted)">—</span>';
    const d = new Date(str);
    return d.toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric' }) +
           ' ' + d.toLocaleTimeString('es-ES', { hour:'2-digit', minute:'2-digit' });
}

function getChartColors() {
    const isDark = !document.body.classList.contains('light-theme');
    return {
        text:      isDark ? '#8b949e' : '#59636e',
        grid:      isDark ? 'rgba(48,54,61,0.5)' : 'rgba(208,215,222,0.5)',
        gradient1: isDark ? [0.3, 0] : [0.15, 0],
    };
}

// ══════════════════════════════════════════════
//  CARGAR ESTADÍSTICAS
// ══════════════════════════════════════════════
async function loadStats() {
    try {
        const res  = await fetch(API + '?action=stats');
        const data = await res.json();

        document.getElementById('stat-total').textContent     = data.total;
        document.getElementById('stat-facial').textContent    = data.conFacial;
        document.getElementById('stat-hoy').textContent       = data.hoy;
        document.getElementById('stat-pendientes').textContent= data.pendientes;
        document.getElementById('donut-center-num').textContent = data.total;

        // Tendencias
        document.getElementById('stat-semana-trend').textContent = `+${data.semana} esta semana`;
        const pct = data.total > 0 ? Math.round(data.conFacial / data.total * 100) : 0;
        const t = document.getElementById('stat-facial-pct');
        t.textContent = pct + '% del total';
        t.className   = 'stat-trend ' + (pct >= 50 ? 'up' : '');

        if (data.pendientes > 0) {
            const b = document.getElementById('badge-pendientes');
            b.textContent = data.pendientes + ' pendiente' + (data.pendientes > 1 ? 's' : '');
            b.style.display = '';
            b.className = 'topbar-badge yellow';
        }

        // Eliminados
        if (data.eliminados > 0) {
            totalEliminados += data.eliminados;
            document.getElementById('stat-eliminados').textContent = totalEliminados;
            showToast(`🗑️ ${data.eliminados} usuario${data.eliminados > 1 ? 's eliminados' : ' eliminado'} por no verificar OTP a tiempo.`, 'warning', 6000);
        }

        document.getElementById('stat-pendientes-info').textContent =
            data.pendientes > 0 ? 'Expiran en ≤5 min sin OTP' : '';

        // Actualizar gráficas
        renderChartRegistros(data.registros30);
        renderChartMetodos(data.conFacial, data.sinFacial);

    } catch (e) {
        showToast('Error al cargar estadísticas del servidor.', 'error');
    }
}

// ══════════════════════════════════════════════
//  GRÁFICA DE ÁREA — Registros 30 días
// ══════════════════════════════════════════════
function renderChartRegistros(datos) {
    const labels = [];
    const values = [];

    // Generar todos los días del rango
    const hoy = new Date();
    for (let i = 29; i >= 0; i--) {
        const d = new Date(hoy);
        d.setDate(d.getDate() - i);
        const key = d.toISOString().split('T')[0];
        labels.push(d.toLocaleDateString('es-ES', { day:'2-digit', month:'short' }));
        const found = datos.find(r => r.dia === key);
        values.push(found ? parseInt(found.total) : 0);
    }

    const ctx = document.getElementById('chartRegistros');
    const colors = getChartColors();
    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(0, 196, 212, 0.3)');
    gradient.addColorStop(1, 'rgba(0, 196, 212, 0.0)');

    const cfg = {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: '#00c4d4',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#00c4d4',
                pointBorderWidth: 0,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#161b22',
                titleColor: '#e6edf3',
                bodyColor: '#8b949e',
                borderColor: '#30363d',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    title: ctx => ctx[0].label,
                    label: ctx => ` ${ctx.parsed.y} registros`,
                }
            }},
            scales: {
                x: {
                    ticks: { color: colors.text, font: { size: 11 }, maxRotation: 0, maxTicksLimit: 10 },
                    grid: { color: colors.grid },
                },
                y: {
                    ticks: { color: colors.text, font: { size: 11 }, stepSize: 1 },
                    grid: { color: colors.grid },
                    beginAtZero: true,
                    min: 0,
                }
            }
        }
    };

    if (chartRegistros) {
        chartRegistros.destroy();
    }
    chartRegistros = new Chart(ctx, cfg);
}

// ══════════════════════════════════════════════
//  GRÁFICA DONUT — Métodos de autenticación
// ══════════════════════════════════════════════
function renderChartMetodos(facial, password) {
    const ctx = document.getElementById('chartMetodos');

    const cfg = {
        type: 'doughnut',
        data: {
            labels: ['Biometría Facial', 'Contraseña + OTP'],
            datasets: [{
                data: [facial, password],
                backgroundColor: ['#00c4d4', '#3fb950'],
                borderColor: [document.body.classList.contains('light-theme') ? '#fff' : '#161b22'],
                borderWidth: 4,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#161b22',
                    titleColor: '#e6edf3',
                    bodyColor: '#8b949e',
                    borderColor: '#30363d',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed} usuarios (${ctx.label})`,
                    }
                }
            }
        }
    };

    if (chartMetodos) {
        chartMetodos.destroy();
    }
    chartMetodos = new Chart(ctx, cfg);
}

// ══════════════════════════════════════════════
//  CARGAR TABLA DE USUARIOS
// ══════════════════════════════════════════════
async function loadUsers(showSpinner = true) {
    if (showSpinner) {
        const btn = document.getElementById('btn-refresh-table');
        btn.classList.add('spinning');
    }
    try {
        const res  = await fetch(API + '?action=usuarios');
        const data = await res.json();
        allUsers = data.usuarios || [];
        renderTable(allUsers);
    } catch (e) {
        showToast('Error al cargar la lista de usuarios.', 'error');
    } finally {
        document.getElementById('btn-refresh-table').classList.remove('spinning');
    }
}

function renderTable(users) {
    const tbody = document.getElementById('tabla-usuarios');
    const q     = document.getElementById('search-input').value.toLowerCase();
    const list  = q ? users.filter(u =>
        u.usuario.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
    ) : users;

    if (!list.length) {
        tbody.innerHTML = `<tr class="loading-row"><td colspan="6">No hay usuarios que coincidan.</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((u, i) => `
        <tr>
            <td style="color:var(--text-muted);font-size:0.76rem;">${i + 1}</td>
            <td>
                <div class="user-cell">
                    <div class="mini-avatar">${u.usuario.charAt(0).toUpperCase()}</div>
                    <div>
                        <div class="uname">${escHtml(u.usuario)}</div>
                        <div class="uemail">${escHtml(u.email)}</div>
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
        </tr>
    `).join('');
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// ══════════════════════════════════════════════
//  LIMPIEZA MANUAL
// ══════════════════════════════════════════════
document.getElementById('btn-cleanup').addEventListener('click', async () => {
    try {
        const res  = await fetch(API + '?action=cleanup');
        const data = await res.json();
        totalEliminados += data.eliminados;
        document.getElementById('stat-eliminados').textContent = totalEliminados;
        showToast(data.mensaje, data.eliminados > 0 ? 'success' : 'info', 5000);
        loadStats();
        loadUsers(false);
    } catch (e) {
        showToast('Error al ejecutar la limpieza.', 'error');
    }
});

// Búsqueda en tiempo real
document.getElementById('search-input').addEventListener('input', () => renderTable(allUsers));
document.getElementById('btn-refresh-table').addEventListener('click', () => loadUsers());
document.getElementById('btn-refresh-chart').addEventListener('click', () => loadStats());

// ══════════════════════════════════════════════
//  SINCRONIZACIÓN DE GRÁFICAS CON EL TEMA
// ══════════════════════════════════════════════
// Escuchamos los clics en los botones de tema para redibujar las gráficas
document.getElementById('btn-dark').addEventListener('click', () => {
    if (chartRegistros || chartMetodos) {
        setTimeout(() => { loadStats(); }, 150);
    }
});
document.getElementById('btn-light').addEventListener('click', () => {
    if (chartRegistros || chartMetodos) {
        setTimeout(() => { loadStats(); }, 150);
    }
});

// ══════════════════════════════════════════════
//  INIT + AUTO-REFRESH 30s
// ══════════════════════════════════════════════
loadStats();
loadUsers();

setInterval(() => {
    loadStats();
    loadUsers(false);
}, 30000);

// Toast de bienvenida
setTimeout(() => {
    showToast('👋 ¡Bienvenido, <?= htmlspecialchars($nombre_corto, ENT_QUOTES) ?>! El sistema está monitoreando usuarios en tiempo real.', 'success', 5000);
}, 1000);
</script>
</body>
</html>
