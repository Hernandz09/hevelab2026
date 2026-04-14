<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../controllers/authorizationController.php';

$auth  = new AuthorizationController($pdo);
$error = $auth->verificarOTP();

$tipo          = $_SESSION['otp_tipo']  ?? 'login';
$emailMostrado = $_SESSION['temp_email'] ?? '';
$reenviado     = isset($_GET['reenviado']);

$backLink  = match($tipo) {
    'registro' => '../register/registrar.php',
    'reset'    => '../forgot/solicitar.php',
    default    => '../login/index.php',
};
$backLabel = match($tipo) {
    'registro' => 'Volver al registro',
    'reset'    => 'Volver a recuperación',
    default    => 'Volver al inicio de sesión',
};
$leftTitle = match($tipo) {
    'registro' => 'Casi <span>listo.</span>',
    'reset'    => 'Verifica tu <span>identidad.</span>',
    default    => 'Un paso <span>más.</span>',
};
$leftDesc = match($tipo) {
    'registro' => 'Solo confirma tu correo electrónico para activar tu cuenta.',
    'reset'    => 'Ingresa el código que enviamos para proteger tu cuenta.',
    default    => 'Confirma tu identidad ingresando el código enviado a tu correo.',
};

// Segundos del countdown según tipo
$countdownSecs = ($tipo === 'reset') ? 120 : 60;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Verificar Código</title>
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
<?php if ($reenviado): ?>
<script>showToast('Código reenviado correctamente. Revisa tu correo.', 'success', 5000);</script>
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
                    <a href="../login/index.php">
                        <img src="../../assets/img/horizo_logo_01new.png" alt="HEVELAB">
                    </a>
                </div>
            </div>
            <div class="auth-left-body" style="z-index: 1;">
                <p class="auth-label-tag">Verificación</p>
                <h2><?= $leftTitle ?></h2>
                <p><?= $leftDesc ?></p>
            </div>

            <!-- Mascota Hevy flotando -->
            <img src="../../assets/img/otp.png" class="hevy-mascot" alt="Mascota Hevy OTP">

            <div class="auth-left-bottom" style="z-index: 1;">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <!-- ── PANEL DERECHO ── -->
    <div class="auth-right">
        <p class="auth-form-label">Seguridad</p>
        <h1>Ingresar <span>código.</span></h1>

        <?php if ($emailMostrado): ?>
            <p class="otp-hint">
                Código de 6 dígitos enviado a<br>
                <strong><?= htmlspecialchars($emailMostrado) ?></strong>
            </p>
        <?php else: ?>
            <p class="otp-hint">Ingresa el código de 6 dígitos enviado a tu correo.</p>
        <?php endif; ?>

        <form method="POST" id="form-otp">
            <div class="otp-inputs">
                <input type="text" inputmode="numeric" name="otp"
                    id="otp-input" maxlength="6" placeholder="000000"
                    required autocomplete="one-time-code">
            </div>
            <button type="submit" class="btn-submit">Verificar código</button>
        </form>

        <!-- Resend countdown -->
        <div class="resend-wrap">
            <div class="resend-countdown" id="resend-countdown">
                <span>¿No recibiste el código? Reenviar en</span>
                <span class="resend-timer-badge" id="countdown-timer">--</span>
            </div>
            <button type="button" class="resend-btn" id="resend-btn">
                Reenviar código
            </button>
        </div>

        <a class="back-link" href="<?= $backLink ?>">← <?= $backLabel ?></a>
    </div>
</div>

<script src="../../assets/js/splash.js"></script>
<script>
    // ── Splash en submit ──────────────────────────────────────────
    document.getElementById('form-otp').addEventListener('submit', () => {
        window.hvShowSplash?.();
    });

    // ── Countdown para reenvío ────────────────────────────────────
    const TIPO        = <?= json_encode($tipo) ?>;
    const COUNTDOWN   = <?= (int) $countdownSecs ?>;
    const KEY         = 'hv_otp_sent_' + TIPO;
    const IS_REENVIADO = <?= $reenviado ? 'true' : 'false' ?>;

    // Reiniciar o inicializar timestamp
    if (IS_REENVIADO || !sessionStorage.getItem(KEY)) {
        sessionStorage.setItem(KEY, Date.now().toString());
    }

    const sentAt       = parseInt(sessionStorage.getItem(KEY));
    const countdownEl  = document.getElementById('countdown-timer');
    const countdownWrap = document.getElementById('resend-countdown');
    const resendBtn    = document.getElementById('resend-btn');

    function tick() {
        const elapsed   = Math.floor((Date.now() - sentAt) / 1000);
        const remaining = Math.max(0, COUNTDOWN - elapsed);

        if (remaining > 0) {
            countdownEl.textContent = remaining + 's';
            countdownWrap.style.display = 'flex';
            resendBtn.style.display     = 'none';
        } else {
            countdownWrap.style.display = 'none';
            resendBtn.style.display     = 'inline';
        }
    }

    tick();
    const timer = setInterval(tick, 1000);

    resendBtn.addEventListener('click', () => {
        sessionStorage.setItem(KEY, Date.now().toString());
        window.location.href = 'verificar_otp.php?reenviar=1';
    });
</script>
</body>
</html>