<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../Controllers/authorizationController.php';

$auth  = new AuthorizationController($pdo);
$error = $auth->login();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Iniciar Sesión</title>
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
<?php if (isset($_GET['reset']) && $_GET['reset'] === 'ok'): ?>
<script>showToast('Contraseña actualizada correctamente. Ya puedes iniciar sesión.', 'success', 5500);</script>
<?php endif; ?>
<?php if (isset($error)): ?>
<script>showToast(<?= json_encode($error) ?>, 'error');</script>
<?php endif; ?>
<div class="auth-page">

    <!-- ── PANEL IZQUIERDO ── -->
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-left-top">
                <div class="brand-logo">
                    <a href="index.php">
                        <img src="../../assets/img/horizo_logo_01new.png" alt="HEVELAB">
                    </a>
                </div>
            </div>
            <div class="auth-left-body" style="z-index: 1;">
                <p class="auth-label-tag">Sistema ERP</p>
                <h2>Gestiona tu empresa<br>con <span>inteligencia.</span></h2>
                <p>Controla ventas, inventario, usuarios y más — todo desde un solo lugar.</p>
            </div>
            
            <!-- Mascota Hevy flotando -->
            <img src="../../assets/img/hevy_01.png" class="hevy-mascot" alt="Mascota Hevy">

            <div class="auth-left-bottom" style="z-index: 1;">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <!-- ── PANEL DERECHO ── -->
    <div class="auth-right">
        <p class="auth-form-label">Bienvenido</p>
        <h1>Iniciar <span>sesión.</span></h1>
        <p class="auth-sub">
            ¿No tienes cuenta? <a href="../register/registrar.php">Regístrate aquí</a>
        </p>

        <form method="POST" id="form-login">
            <div class="form-group">
                <label for="usuario">Usuario o correo</label>
                <input type="text" name="usuario" id="usuario"
                    placeholder="ejemplo@correo.com" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrap">
                    <input type="password" name="password" id="password"
                        placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-pass" data-target="password" aria-label="Mostrar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="forgot-link">
                <a href="../forgot/solicitar.php">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-submit">Ingresar</button>
        </form>

        <div class="auth-divider">o</div>

        <button type="button" class="btn-face" id="btn-face-login">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                <circle cx="12" cy="13" r="3"/>
                <path d="M6.5 10.5c0-1 .5-2 1.5-2.5M17.5 10.5c0-1-.5-2-1.5-2.5"/>
            </svg>
            Entrar con reconocimiento facial
        </button>
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
    document.querySelectorAll('.toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('active');
        });
    });

    let captchaVerified = false, sc = null;
    const overlay = document.getElementById('captcha-overlay');

    function openCaptcha() {
        overlay.setAttribute('aria-hidden','false');
        overlay.classList.add('visible');
        if (!sc) {
            sc = new SliderCaptcha({
                container: document.getElementById('captcha-container'),
                onSuccess: () => { captchaVerified = true; overlay.classList.remove('visible'); window.hvShowSplash?.(); document.getElementById('form-login').submit(); }
            });
        } else sc.reset();
    }
    function closeCaptcha() { overlay.classList.remove('visible'); overlay.setAttribute('aria-hidden','true'); }

    document.getElementById('form-login').addEventListener('submit', e => { if (!captchaVerified) { e.preventDefault(); openCaptcha(); } else { window.hvShowSplash?.(); } });
    document.getElementById('cap-close-x').addEventListener('click', closeCaptcha);
    document.getElementById('cap-close').addEventListener('click', closeCaptcha);
    document.getElementById('cap-refresh').addEventListener('click', () => { if(sc) sc.reset(); });
    overlay.addEventListener('click', e => { if(e.target===overlay) closeCaptcha(); });
</script>
<script src="../../assets/js/splash.js"></script>
<script>window.FACE_MODELS_URL = '../../assets/models'; window.FACE_API_LOGIN = '../../api/face_login.php';</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="../../assets/js/face_auth.js"></script>
<script>
    document.getElementById('btn-face-login').addEventListener('click', () => {
        window.FaceAuth.openLoginModal();
    });
</script>
</body>
</html>
