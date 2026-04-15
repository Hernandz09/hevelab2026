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
    <title>HEVELAB | Configuración</title>
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
            width: var(--sidebar-w); height: 100vh; background: var(--bg2);
            border-right: 1px solid var(--border); display: flex; flex-direction: column;
            position: fixed; left: 0; top: 0; z-index: 50;
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
        .nav-item.active { background: rgba(0,196,212,0.1); color: var(--accent); }
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
        .topbar {
            height: var(--topbar-h); background: var(--bg2); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; padding: 0 28px; flex-shrink: 0;
        }
        .topbar-title h1 { font-size: 1.1rem; font-weight: 700; color: var(--text); }
        .topbar-title p { font-size: 0.78rem; color: var(--text-muted); }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .content { flex: 1; overflow-y: auto; padding: 28px; }

        /* ── LAYOUT ── */
        .config-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 22px;
            align-items: start;
        }
        .config-main { display: flex; flex-direction: column; gap: 22px; }
        .config-sidebar { display: flex; flex-direction: column; gap: 22px; }

        /* ── CARD ── */
        .card {
            background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 28px;
        }
        .card-header { margin-bottom: 28px; }
        .card-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .card-title-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .card-title-icon svg { width: 18px; height: 18px; }
        .card-title-icon.cyan   { background: rgba(0,196,212,0.15);  color: var(--accent); }
        .card-title-icon.purple { background: rgba(188,140,255,0.15); color: var(--purple); }
        .card-title-icon.green  { background: rgba(63,185,80,0.15);   color: var(--green); }
        .card-subtitle { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; line-height: 1.5; }

        /* ── SETTING ITEM ── */
        .setting-item { margin-bottom: 32px; }
        .setting-item:last-child { margin-bottom: 0; }

        .setting-label {
            font-size: 0.88rem; font-weight: 600; color: var(--text);
            margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;
        }
        .setting-desc { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 14px; line-height: 1.5; }

        /* ── SLIDER PRINCIPAL: THRESHOLD ── */
        .threshold-display {
            background: var(--bg3); border: 1px solid var(--border); border-radius: 14px;
            padding: 20px 24px; margin-bottom: 18px; text-align: center;
        }
        .threshold-big {
            font-size: 4rem; font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.15s;
        }
        .threshold-pct {
            font-size: 1.1rem; font-weight: 600; color: var(--text-muted);
            margin-top: 4px;
        }
        .threshold-status {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 10px; padding: 4px 12px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 700;
        }
        .threshold-status.strict   { background: rgba(0,196,212,0.15);   color: var(--accent); }
        .threshold-status.balanced { background: rgba(63,185,80,0.15);   color: var(--green); }
        .threshold-status.lenient  { background: rgba(210,153,34,0.15);  color: var(--yellow); }
        .threshold-status.very-lenient { background: rgba(248,81,73,0.15); color: var(--red); }

        /* ── SLIDER CUSTOM ── */
        .slider-wrap { position: relative; }
        .range-labels {
            display: flex; justify-content: space-between;
            font-size: 0.72rem; color: var(--text-muted); margin-bottom: 8px;
        }

        input[type="range"] {
            -webkit-appearance: none; appearance: none;
            width: 100%; height: 6px; border-radius: 99px;
            background: var(--bg3); outline: none; cursor: pointer;
        }
        input[type="range"]::-webkit-slider-track {
            height: 6px; border-radius: 99px;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none;
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--accent); cursor: pointer;
            border: 3px solid var(--bg2);
            box-shadow: 0 0 0 2px var(--accent);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 0 0 4px rgba(0,196,212,0.25);
        }
        input[type="range"]::-moz-range-thumb {
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--accent); cursor: pointer;
            border: 3px solid var(--bg2);
        }

        /* Gradient track usando CSS variable */
        #slider-threshold {
            background: linear-gradient(to right, var(--accent) var(--pct, 52%), var(--bg3) var(--pct, 52%));
        }

        /* ── ZONE INDICATOR ── */
        .zone-bar {
            display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
            height: 6px; border-radius: 99px; overflow: hidden;
            margin-top: 10px; gap: 1px;
        }
        .zone-bar span { height: 100%; }
        .zone-bar .z1 { background: var(--accent); }
        .zone-bar .z2 { background: var(--green); }
        .zone-bar .z3 { background: var(--yellow); }
        .zone-bar .z4 { background: var(--red); }
        .zone-labels {
            display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
            font-size: 0.68rem; text-align: center; margin-top: 5px;
        }
        .zone-labels span { color: var(--text-muted); }

        /* ── NUMBER INPUT ── */
        .number-input-wrap {
            display: flex; align-items: center; gap: 10px;
        }
        .number-input {
            background: var(--bg3); border: 1px solid var(--border); color: var(--text);
            padding: 10px 14px; border-radius: 10px; font-family: 'Inter', sans-serif;
            font-size: 0.95rem; font-weight: 700; outline: none; width: 90px;
            text-align: center; transition: border-color 0.2s;
        }
        .number-input:focus { border-color: var(--accent); }
        .number-spin {
            display: flex; flex-direction: column; gap: 4px;
        }
        .spin-btn {
            background: var(--bg3); border: 1px solid var(--border); color: var(--text-muted);
            width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: all 0.18s;
        }
        .spin-btn:hover { border-color: var(--accent); color: var(--accent); }
        .spin-btn svg { width: 13px; height: 13px; }

        /* ── SAVE BUTTON ── */
        .btn-save-config {
            width: 100%; padding: 13px; background: var(--accent); color: #0d1117;
            border: none; border-radius: 12px; font-size: 0.9rem; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-save-config:hover { background: #00d9eb; transform: translateY(-1px); }
        .btn-save-config svg { width: 16px; height: 16px; }
        .btn-save-config.saving svg { animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── INFO BOX ── */
        .info-box {
            border-radius: 10px; padding: 14px 16px;
            font-size: 0.8rem; line-height: 1.55;
        }
        .info-box.cyan   { background: rgba(0,196,212,0.08); border: 1px solid rgba(0,196,212,0.2); color: var(--accent); }
        .info-box.yellow { background: rgba(210,153,34,0.08); border: 1px solid rgba(210,153,34,0.2); color: var(--yellow); }
        .info-box.red    { background: rgba(248,81,73,0.08); border: 1px solid rgba(248,81,73,0.2); color: var(--red); }
        .info-box strong { font-weight: 700; }

        /* ── META ── */
        .meta-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 0.82rem;
        }
        .meta-row:last-child { border-bottom: none; padding-bottom: 0; }
        .meta-label { color: var(--text-muted); }
        .meta-value { font-weight: 600; color: var(--text); }
        .meta-value.green  { color: var(--green); }
        .meta-value.accent { color: var(--accent); }

        /* ── THEME SWITCH ── */
        .theme-switch { display:flex; gap:4px; background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:3px; }
        .theme-switch button { background:none; border:none; color:var(--text-muted); cursor:pointer; padding:5px; border-radius:5px; display:flex; align-items:center; transition:all 0.2s; }
        .theme-switch button:hover { color:var(--text); }
        .theme-switch button.active { background:var(--bg2); color:var(--accent); }
        .theme-switch button svg { width:15px; height:15px; }

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

        @media (max-width: 1100px) { .config-grid { grid-template-columns: 1fr; } .config-sidebar { order: -1; } }
        @media (max-width: 768px) { .sidebar { display:none; } .main { margin-left:0; } }
    </style>
</head>
<body>
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/toast.js"></script>

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
        <a class="nav-item" href="../usuarios/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuarios
        </a>
        <a class="nav-item active" href="index.php">
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

<div class="main">
    <header class="topbar">
        <div class="topbar-title">
            <h1>Configuración del Sistema</h1>
            <p>HEVELAB · Parámetros de seguridad y autenticación biométrica</p>
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

    <main class="content">
        <div class="config-grid">

            
            <div class="config-main">

                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <div class="card-title-icon cyan">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3m13-5v3a2 2 0 0 0-2 2h-3"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            Reconocimiento Facial
                        </div>
                        <div class="card-subtitle" style="margin-left:46px;">
                            A mayor porcentaje, mayor seguridad: el sistema exige una similitud más alta entre
                            el rostro capturado y el registrado. A menor porcentaje, mayor tolerancia (más permisivo).
                        </div>
                    </div>

                    
                    <div class="setting-item">
                        <div class="setting-label">
                            <span>Nivel de seguridad del reconocimiento facial</span>
                        </div>
                        <div class="setting-desc">
                            Controla qué tan estricto debe ser el sistema al comparar rostros.
                            <strong>100%</strong> = máxima seguridad (solo acepta coincidencias muy precisas).
                            <strong>10%</strong> = mínima seguridad (acepta similitudes lejanas).
                        </div>

                        
                        <div class="threshold-display">
                            <div class="threshold-big" id="thresh-val">70%</div>
                            <div class="threshold-pct" id="thresh-pct">Alta seguridad</div>
                            <div>
                                <span class="threshold-status balanced" id="thresh-status">⚖️ Equilibrado</span>
                            </div>
                        </div>

                        
                        <div class="slider-wrap">
                            <div class="range-labels">
                                <span>10% — Muy permisivo</span>
                                <span>100% — Muy estricto</span>
                            </div>
                            <input
                                type="range"
                                id="slider-threshold"
                                min="10"
                                max="100"
                                step="1"
                                value="70"
                            >
                            
                            <div class="zone-bar" style="margin-top:10px;">
                                <span class="z4"></span>
                                <span class="z3"></span>
                                <span class="z2"></span>
                                <span class="z1"></span>
                            </div>
                            <div class="zone-labels">
                                <span style="color:var(--red);">Muy permisivo<br>10%–35%</span>
                                <span style="color:var(--yellow);">Permisivo<br>35%–55%</span>
                                <span style="color:var(--green);">Equilibrado<br>55%–75%</span>
                                <span style="color:var(--accent);">Muy estricto<br>75%–100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <div class="card-title-icon purple">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            Seguridad y OTP
                        </div>
                        <div class="card-subtitle" style="margin-left:46px;">
                            Configura la caducidad del código OTP y la política de intentos de inicio de sesión.
                        </div>
                    </div>

                    
                    <div class="setting-item">
                        <div class="setting-label">Tiempo de expiración del OTP</div>
                        <div class="setting-desc">
                            Número de minutos que el código OTP permanece válido tras ser enviado por email.
                            Recomendado: 5 minutos para mayor seguridad.
                        </div>
                        <div class="number-input-wrap">
                            <input type="number" class="number-input" id="inp-otp-min" min="1" max="60" value="5">
                            <div class="number-spin">
                                <button class="spin-btn" onclick="spin('inp-otp-min', 1, 1, 60)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                </button>
                                <button class="spin-btn" onclick="spin('inp-otp-min', -1, 1, 60)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                            <span style="color:var(--text-muted);font-size:0.85rem;">minutos</span>
                        </div>
                    </div>

                    
                    <div class="setting-item">
                        <div class="setting-label">Máximo de intentos de inicio de sesión</div>
                        <div class="setting-desc">
                            Número de intentos fallidos de contraseña antes de que la cuenta quede pendiente
                            de revisión. Recomendado: 5 intentos.
                        </div>
                        <div class="number-input-wrap">
                            <input type="number" class="number-input" id="inp-max-intentos" min="1" max="20" value="5">
                            <div class="number-spin">
                                <button class="spin-btn" onclick="spin('inp-max-intentos', 1, 1, 20)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                </button>
                                <button class="spin-btn" onclick="spin('inp-max-intentos', -1, 1, 20)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                            <span style="color:var(--text-muted);font-size:0.85rem;">intentos</span>
                        </div>
                    </div>

                    
                    <button class="btn-save-config" id="btn-guardar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Guardar configuración
                    </button>
                </div>

            </div>

            
            <div class="config-sidebar">

                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <div class="card-title-icon green">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            Guía de valores
                        </div>
                    </div>
                    <div class="info-box cyan" style="margin-bottom:10px;">
                        <strong>75% – 100% · Muy estricto 🔒</strong><br>
                        Solo rostros con alta similitud pasan. Ideal para alta seguridad. Puede rechazar usuarios en condiciones de luz variable.
                    </div>
                    <div class="info-box" style="background:rgba(63,185,80,0.08);border:1px solid rgba(63,185,80,0.2);color:var(--green);margin-bottom:10px;">
                        <strong>55% – 75% · Equilibrado ✓ Recomendado</strong><br>
                        Balance óptimo entre seguridad y usabilidad. El valor predeterminado es <strong>70%</strong>.
                    </div>
                    <div class="info-box yellow" style="margin-bottom:10px;">
                        <strong>35% – 55% · Permisivo</strong><br>
                        Admite más variaciones de iluminación y posición, pero aumenta el riesgo de falsos positivos.
                    </div>
                    <div class="info-box red">
                        <strong>10% – 35% · Muy permisivo ⚠️</strong><br>
                        Puede permitir acceso a personas con rasgos similares. No recomendado para entornos de producción.
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title" style="font-size:0.9rem;">Estado actual guardado</div>
                    </div>
                    <div id="meta-list">
                        <div class="meta-row">
                            <span class="meta-label">Umbral facial</span>
                            <span class="meta-value accent" id="meta-threshold">—</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Expiración OTP</span>
                            <span class="meta-value" id="meta-otp">— min</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Máx. intentos</span>
                            <span class="meta-value" id="meta-intentos">—</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Última modificación</span>
                            <span class="meta-value" id="meta-fecha" style="font-size:0.78rem;">—</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<script>
const API = '../../api/config_api.php';

// ── SLIDER LOGIC ────────────────────────────────
const slider   = document.getElementById('slider-threshold');
const valEl    = document.getElementById('thresh-val');
const pctEl    = document.getElementById('thresh-pct');
const statusEl = document.getElementById('thresh-status');

function pctToThreshold(pct) {
    return parseFloat(((110 - pct) / 100).toFixed(2));
}

function thresholdToPct(threshold) {
    return Math.round(110 - threshold * 100);
}

function getStatus(pct) {
    if (pct >= 75) return { cls: 'strict',      label: '🔒 Muy estricto' };
    if (pct >= 55) return { cls: 'balanced',    label: '⚖️ Equilibrado' };
    if (pct >= 35) return { cls: 'lenient',     label: '⚠️ Permisivo' };
    return              { cls: 'very-lenient',  label: '🚨 Muy permisivo' };
}

function updateSlider(pct) {
    const p    = parseInt(pct);
    const fill = Math.round((p - 10) / 90 * 100);
    valEl.textContent  = p + '%';
    pctEl.textContent  = p >= 75 ? 'Alta seguridad'
                        : p >= 55 ? 'Seguridad media-alta'
                        : p >= 35 ? 'Seguridad media'
                        : 'Baja seguridad';
    slider.style.setProperty('--pct', fill + '%');
    const s = getStatus(p);
    statusEl.className   = 'threshold-status ' + s.cls;
    statusEl.textContent = s.label;
}

slider.addEventListener('input', () => updateSlider(slider.value));

function spin(id, delta, min, max) {
    const inp = document.getElementById(id);
    let v = parseInt(inp.value) + delta;
    v = Math.max(min, Math.min(max, v));
    inp.value = v;
}

async function loadConfig() {
    try {
        const res  = await fetch(`${API}?action=leer`);
        const data = await res.json();
        if (!data.success) return;
        const cfg = data.config;

        const pct = thresholdToPct(cfg.face_threshold);
        slider.value = pct;
        updateSlider(pct);
        document.getElementById('inp-otp-min').value      = cfg.otp_expiracion_min;
        document.getElementById('inp-max-intentos').value = cfg.max_intentos_login;

        document.getElementById('meta-threshold').textContent = thresholdToPct(cfg.face_threshold) + '%';
        document.getElementById('meta-otp').textContent       = cfg.otp_expiracion_min + ' min';
        document.getElementById('meta-intentos').textContent  = cfg.max_intentos_login + ' intentos';
        document.getElementById('meta-fecha').textContent     = cfg.updated_at
            ? new Date(cfg.updated_at).toLocaleString('es-ES')
            : 'Sin modificaciones';
    } catch (e) {
        showToast('No se pudo cargar la configuración guardada.', 'warning');
    }
}

document.getElementById('btn-guardar').addEventListener('click', async () => {
    const btn = document.getElementById('btn-guardar');
    btn.classList.add('saving');
    btn.disabled = true;

    const secPct   = parseInt(slider.value);
    const payload  = {
        face_threshold:     pctToThreshold(secPct),
        otp_expiracion_min: parseInt(document.getElementById('inp-otp-min').value),
        max_intentos_login: parseInt(document.getElementById('inp-max-intentos').value),
    };

    try {
        const res  = await fetch(`${API}?action=guardar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        showToast('✅ ' + data.message, 'success', 5000);
        const cfg = data.config;
        document.getElementById('meta-threshold').textContent = thresholdToPct(cfg.face_threshold) + '%';
        document.getElementById('meta-otp').textContent       = cfg.otp_expiracion_min + ' min';
        document.getElementById('meta-intentos').textContent  = cfg.max_intentos_login + ' intentos';
        document.getElementById('meta-fecha').textContent     = new Date(cfg.updated_at).toLocaleString('es-ES');
    } catch (e) {
        showToast('❌ ' + e.message, 'error');
    } finally {
        btn.classList.remove('saving');
        btn.disabled = false;
    }
});

updateSlider(70);
loadConfig();
</script>
</body>
</html>
