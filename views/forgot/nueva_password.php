<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../Controllers/authorizationController.php';

$auth      = new AuthorizationController($pdo);
$resultado = $auth->resetPassword();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Nueva Contraseña</title>
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link rel="stylesheet" href="../../assets/css/light_theme.css">
</head>
<body>
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/toast.js"></script>
<?php if (isset($resultado['error'])): ?>
<script>showToast(<?= json_encode($resultado['error']) ?>, 'error');</script>
<?php endif; ?>
<div class="auth-page">

    
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-left-top">
                <div class="brand-logo">
                    <a href="../login/index.php">
                        <img src="../../assets/img/horizo_logo_01new.png" alt="HEVELAB">
                    </a>
                </div>
            </div>
            <div class="auth-left-body" style="z-index: 1;">
                <p class="auth-label-tag">Seguridad</p>
                <h2>Crea una contraseña <span>segura.</span></h2>
                <p>Usa una combinación de letras, números y símbolos para mayor protección.</p>
            </div>
            
            
            <img src="../../assets/img/recordando.png" class="hevy-mascot" alt="Mascota Hevy Recordando">

            <div class="auth-left-bottom" style="z-index: 1;">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    
    <div class="auth-right">
        <p class="auth-form-label">Restablecer</p>
        <h1>Nueva <span>contraseña.</span></h1>
        <p class="auth-sub">Elige una contraseña que no hayas usado antes.</p>

        <form method="POST" id="form-reset" novalidate>

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <div class="input-wrap">
                    <input type="password" name="password" id="password"
                        placeholder="••••••••" required autocomplete="new-password">
                    <button type="button" class="toggle-pass" data-target="password" aria-label="Mostrar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="strength-wrap">
                <div class="strength-segments">
                    <span class="seg"></span><span class="seg"></span>
                    <span class="seg"></span><span class="seg"></span><span class="seg"></span>
                </div>
                <span class="strength-label" id="strength-label"></span>
            </div>
            <ul class="requirements">
                <li id="req-length"><span class="req-icon">○</span> 8–30 caracteres</li>
                <li id="req-letnum"><span class="req-icon">○</span> Debe contener números y letras</li>
            </ul>

            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña</label>
                <div class="input-wrap">
                    <input type="password" name="confirm_password" id="confirm_password"
                        placeholder="••••••••" required>
                    <button type="button" class="toggle-pass" data-target="confirm_password" aria-label="Mostrar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <p class="match-msg" id="match-msg"></p>
            </div>

            <button type="submit" class="btn-submit">Guardar contraseña</button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('active');
        });
    });

    const pwInput  = document.getElementById('password');
    const cfInput  = document.getElementById('confirm_password');
    const segs     = document.querySelectorAll('.seg');
    const strLabel = document.getElementById('strength-label');
    const reqLen   = document.getElementById('req-length');
    const reqLet   = document.getElementById('req-letnum');
    const matchMsg = document.getElementById('match-msg');

    const LEVELS = [
        { label: '',           color: '#21262d' },
        { label: 'Muy débil',  color: '#ef4444' },
        { label: 'Débil',      color: '#f97316' },
        { label: 'Media',      color: '#eab308' },
        { label: 'Fuerte',     color: '#22c55e' },
        { label: 'Muy fuerte', color: '#15803d' },
    ];

    function calcStr(pw) {
        if (!pw.length) return 0;
        let s = 0;
        if (pw.length >= 8)          s++;
        if (pw.length >= 12)         s++;
        if (/[A-Z]/.test(pw))        s++;
        if (/[0-9]/.test(pw))        s++;
        if (/[^A-Za-z0-9]/.test(pw)) s++;
        return Math.min(s, 5);
    }

    pwInput.addEventListener('input', () => {
        const lv = calcStr(pwInput.value);
        segs.forEach((s, i) => { s.style.background = i < lv ? LEVELS[lv].color : '#21262d'; });
        strLabel.textContent = LEVELS[lv].label;
        strLabel.style.color = LEVELS[lv].color;
        const ok1 = pwInput.value.length >= 8 && pwInput.value.length <= 30;
        const ok2 = /[a-zA-Z]/.test(pwInput.value) && /[0-9]/.test(pwInput.value);
        [reqLen, reqLet].forEach((el, i) => {
            const ok = i === 0 ? ok1 : ok2;
            el.classList.toggle('ok', ok); el.classList.toggle('fail', !ok && pwInput.value.length > 0);
            el.querySelector('.req-icon').textContent = ok ? '✓' : (pwInput.value.length > 0 ? '✗' : '○');
        });
        if (cfInput.value) checkMatch();
    });

    function checkMatch() {
        if (!cfInput.value) { matchMsg.textContent = ''; matchMsg.className = 'match-msg'; return; }
        const ok = cfInput.value === pwInput.value;
        matchMsg.textContent = ok ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden';
        matchMsg.className = ok ? 'match-msg ok' : 'match-msg fail';
    }
    cfInput.addEventListener('input', checkMatch);
</script>
<script src="../../assets/js/splash.js"></script>
<script>document.querySelector("form").addEventListener("submit",()=>window.hvShowSplash?.());</script>
</body>
</html>
