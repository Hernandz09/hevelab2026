<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../Controllers/authorizationController.php';

$auth      = new AuthorizationController($pdo);
$resultado = $auth->solicitarReset();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Recuperar Contraseña</title>
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
<?php if (isset($resultado['info'])): ?>
<script>showToast(<?= json_encode($resultado['info']) ?>, 'info', 7000);</script>
<?php endif; ?>
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
                <p class="auth-label-tag">Recuperación</p>
                <h2>Recupera tu <span>acceso.</span></h2>
                <p>Te enviaremos un código de verificación a tu correo electrónico para restablecer tu contraseña.</p>
            </div>
            
            <!-- Mascota Hevy flotando -->
            <img src="../../assets/img/enviando.png" class="hevy-mascot" alt="Mascota Hevy Enviando">

            <div class="auth-left-bottom" style="z-index: 1;">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <!-- ── PANEL DERECHO ── -->
    <div class="auth-right">
        <p class="auth-form-label">Contraseña olvidada</p>
        <h1>Ingresa tu <span>correo.</span></h1>
        <p class="auth-sub">Te enviaremos un código de 6 dígitos.</p>

        <?php if (!isset($resultado['info'])): ?>
        <form method="POST" id="form-forgot">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email"
                    placeholder="ejemplo@correo.com" required autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-submit">Enviar código</button>
        </form>
        <?php endif; ?>

        <a class="back-link" href="../login/index.php">← Volver al inicio de sesión</a>
    </div>
</div>
<script src="../../assets/js/splash.js"></script>
<script>document.querySelector("form").addEventListener("submit",()=>window.hvShowSplash?.());</script>
</body>
</html>
