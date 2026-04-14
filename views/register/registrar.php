<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../controllers/authorizationController.php';

$auth      = new AuthorizationController($pdo);
$resultado = $auth->registro();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Crear Cuenta</title>
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/captcha.css">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link rel="stylesheet" href="../../assets/css/face_auth.css">
    <link rel="stylesheet" href="../../assets/css/light_theme.css">
</head>
<body>
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/toast.js"></script>
<?php if (isset($resultado['error'])): ?>
<script>showToast(<?= json_encode($resultado['error']) ?>, 'error');</script>
<?php endif; ?>
<div class="auth-page">

    <!-- ── PANEL IZQUIERDO ── -->
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
                <p class="auth-label-tag">Empieza hoy</p>
                <h2>Lleva tu administración<br>al <span>siguiente nivel.</span></h2>
            </div>
            
            <!-- Mascota Hevy Variante Centrada -->
            <img src="../../assets/img/recordando.png" class="hevy-mascot-reg" alt="Mascota Hevy">

            <div class="auth-left-bottom" style="z-index: 1;">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <!-- ── PANEL DERECHO ── -->
    <div class="auth-right">
        <p class="auth-form-label">Registro</p>
        <h1>Crear <span>cuenta.</span></h1>
        <p class="auth-sub">
            ¿Ya tienes cuenta? <a href="../login/index.php">Inicia sesión</a>
        </p>

        <form method="POST" id="form-registro" novalidate>

            <div class="form-group">
                <label for="nombre_completo">Nombre completo</label>
                <input type="text" name="nombre_completo" id="nombre_completo"
                    placeholder="Ej: Juan Pérez García" required autocomplete="name"
                    value="<?= htmlspecialchars($_POST['nombre_completo'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email"
                    placeholder="ejemplo@correo.com" required autocomplete="off"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div id="password-fields-wrap">
                <div class="form-group">
                    <label for="password">Contraseña</label>
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
                </div>

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
            </div>

            <!-- Descriptor facial (oculto, se llena con JS) -->
            <input type="hidden" name="face_descriptor" id="face_descriptor_input">

            <!-- Sección facial opcional -->
            <div class="face-register-section" id="face-register-section">
                <button type="button" class="face-register-toggle" id="face-register-toggle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M6.5 7c0-1 .5-2 1.5-2.5M17.5 7c0-1-.5-2-1.5-2.5"/>
                        <path d="M3 20c0-4 4-7 9-7s9 3 9 7"/>
                    </svg>
                    Añadir reconocimiento facial
                    <span class="face-register-badge" id="face-badge">Opcional</span>
                </button>
            </div>

            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
    </div>
</div>

<!-- CAPTCHA MODAL -->
<div class="captcha-overlay" id="captcha-overlay" aria-hidden="true">
    <div class="captcha-modal" role="dialog" aria-modal="true">
        <div class="captcha-header">
            <span>Verificación de seguridad</span>
            <button type="button" class="captcha-close" id="cap-close-x">✕</button>
        </div>
        <div id="captcha-container"></div>
        <div class="captcha-footer">
            <div class="captcha-actions">
                <button type="button" id="cap-refresh" title="Regenerar">↺</button>
                <button type="button" id="cap-close"   title="Cerrar">✕</button>
            </div>
            <span class="captcha-brand">HEVELAB VERIFY</span>
        </div>
    </div>
</div>

<script src="../../assets/js/captcha.js"></script>
<script>
    // Toggle contraseña
    document.querySelectorAll('.toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('active');
        });
    });

    // Barra de fuerza
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
    function updateReq(el, ok) {
        el.classList.toggle('ok', ok);
        el.classList.toggle('fail', !ok && pwInput.value.length > 0);
        el.querySelector('.req-icon').textContent = ok ? '✓' : (pwInput.value.length > 0 ? '✗' : '○');
    }
    function checkMatch() {
        if (!cfInput.value) { matchMsg.textContent = ''; matchMsg.className = 'match-msg'; return; }
        const ok = cfInput.value === pwInput.value;
        matchMsg.textContent = ok ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden';
        matchMsg.className = ok ? 'match-msg ok' : 'match-msg fail';
    }
    pwInput.addEventListener('input', () => {
        const lv = calcStr(pwInput.value);
        segs.forEach((s, i) => { s.style.background = i < lv ? LEVELS[lv].color : '#21262d'; });
        strLabel.textContent = LEVELS[lv].label;
        strLabel.style.color = LEVELS[lv].color;
        updateReq(reqLen, pwInput.value.length >= 8 && pwInput.value.length <= 30);
        updateReq(reqLet, /[a-zA-Z]/.test(pwInput.value) && /[0-9]/.test(pwInput.value));
        if (cfInput.value) checkMatch();
    });
    cfInput.addEventListener('input', checkMatch);

    // CAPTCHA
    let captchaVerified = false, sc = null;
    const overlay = document.getElementById('captcha-overlay');
    function openCaptcha() {
        overlay.setAttribute('aria-hidden','false'); overlay.classList.add('visible');
        if (!sc) { sc = new SliderCaptcha({ container: document.getElementById('captcha-container'), onSuccess: () => { captchaVerified=true; overlay.classList.remove('visible'); window.hvShowSplash?.(); document.getElementById('form-registro').submit(); } }); }
        else sc.reset();
    }
    function closeCaptcha() { overlay.classList.remove('visible'); overlay.setAttribute('aria-hidden','true'); }
    document.getElementById('form-registro').addEventListener('submit', e => { if (!captchaVerified) { e.preventDefault(); openCaptcha(); } else { window.hvShowSplash?.(); } });
    document.getElementById('cap-close-x').addEventListener('click', closeCaptcha);
    document.getElementById('cap-close').addEventListener('click', closeCaptcha);
    document.getElementById('cap-refresh').addEventListener('click', () => { if(sc) sc.reset(); });
    overlay.addEventListener('click', e => { if(e.target===overlay) closeCaptcha(); });
</script>
<script src="../../assets/js/splash.js"></script>
<script>window.FACE_MODELS_URL = '../../assets/models';</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="../../assets/js/face_auth.js"></script>
<script>
    document.getElementById('face-register-toggle').addEventListener('click', () => {
        window.FaceAuth.openRegisterCapture((descriptor) => {
            document.getElementById('face_descriptor_input').value = JSON.stringify(descriptor);
            const section = document.getElementById('face-register-section');
            const badge   = document.getElementById('face-badge');
            section.classList.add('captured');
            badge.textContent = 'Capturado';
            badge.classList.add('ok');

            // Ocultar sección de contraseña y vaciar contenido
            const passWrap = document.getElementById('password-fields-wrap');
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
            
            passWrap.style.opacity = '0';
            passWrap.style.transform = 'scaleY(0.95)';
            passWrap.style.transformOrigin = 'top center';
            setTimeout(() => {
                passWrap.style.display = 'none';
            }, 300);

            // Remover validación obligatoria en HTML
            document.getElementById('password').removeAttribute('required');
            document.getElementById('confirm_password').removeAttribute('required');
        });
    });
</script>
</body>
</html>
