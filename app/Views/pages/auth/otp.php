<?php

$basePath = (string) ($basePath ?? '');
$tipo = (string) ($tipo ?? 'login');
$emailMostrado = (string) ($emailMostrado ?? '');
$reenviado = (bool) ($reenviado ?? false);
$error = $error ?? null;
$info = $info ?? null;
$otpExpiresAt = (string) ($otpExpiresAt ?? '');
$otpUserId = (int) ($otpUserId ?? 0);
$otpRemainingSecs = (int) ($otpRemainingSecs ?? 0);
$otpExpiredModal = (bool) ($otpExpiredModal ?? false);

$backLink = match ($tipo) {
    'registro' => $basePath . '/register',
    'reset' => $basePath . '/forgot',
    default => $basePath . '/login',
};
$backLabel = match ($tipo) {
    'registro' => 'Volver al registro',
    'reset' => 'Volver a recuperación',
    default => 'Volver al inicio de sesión',
};
$leftTitle = match ($tipo) {
    'registro' => 'Casi <span>listo.</span>',
    'reset' => 'Verifica tu <span>identidad.</span>',
    default => 'Un paso <span>más.</span>',
};
$leftDesc = match ($tipo) {
    'registro' => 'Solo confirma tu correo electrónico para activar tu cuenta.',
    'reset' => 'Ingresa el código que enviamos para proteger tu cuenta.',
    default => 'Confirma tu identidad ingresando el código enviado a tu correo.',
};
$countdownSecs = ($tipo === 'reset') ? 120 : 60;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Verificar Código</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/public/assets/images/icons/favicon.png', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <?php $authCssV = @filemtime(__DIR__ . '/../../../../public/assets/css/pages/auth/auth.css') ?: time(); ?>
    <?php $lightCssV = @filemtime(__DIR__ . '/../../../../public/assets/css/layouts/light_theme.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/auth/auth.css?v=' . $authCssV, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/toast.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/layouts/light_theme.css?v=' . $lightCssV, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">
<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/theme.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/toast.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<?php if ($reenviado): ?>
<script>showToast('Código reenviado correctamente. Revisa tu correo.', 'success', 5000);</script>
<?php endif; ?>
<?php if (is_string($info) && $info !== ''): ?>
<script>showToast(<?= json_encode($info) ?>, 'info', 4500);</script>
<?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?>
<script>showToast(<?= json_encode($error) ?>, 'error');</script>
<?php endif; ?>
<div class="auth-page">
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-left-top">
                <div class="brand-logo">
                    <a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= htmlspecialchars($basePath . '/public/assets/images/logos/horizo_logo_01new.png', ENT_QUOTES, 'UTF-8') ?>" alt="HEVELAB">
                    </a>
                </div>
            </div>
            <div class="auth-left-body">
                <p class="auth-label-tag">Verificación</p>
                <h2><?= $leftTitle ?></h2>
                <p><?= $leftDesc ?></p>
            </div>

            <img src="<?= htmlspecialchars($basePath . '/public/assets/images/illustrations/otp.png', ENT_QUOTES, 'UTF-8') ?>" class="hevy-mascot" alt="Mascota Hevy OTP">

            <div class="auth-left-bottom">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <div class="auth-right">
        <p class="auth-form-label">Seguridad</p>
        <h1>Ingresar <span>código.</span></h1>

        <?php if ($emailMostrado !== ''): ?>
            <p class="otp-hint">
                Código de 6 dígitos enviado a<br>
                <strong><?= htmlspecialchars($emailMostrado, ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
        <?php else: ?>
            <p class="otp-hint">Ingresa el código de 6 dígitos enviado a tu correo.</p>
        <?php endif; ?>

        <div class="otp-expiry" id="otp-expiry">
            <span>Tiempo restante</span>
            <span class="otp-expiry-badge" id="otp-expiry-badge">--:--</span>
        </div>

        <form method="POST" id="form-otp" action="<?= htmlspecialchars($basePath . '/otp', ENT_QUOTES, 'UTF-8') ?>">
            <div class="otp-inputs">
                <input type="text" inputmode="numeric" name="otp" id="otp-input" maxlength="6" placeholder="000000" required autocomplete="one-time-code">
            </div>
            <button type="submit" class="btn-submit">Verificar código</button>
        </form>

        <div class="resend-wrap">
            <div class="resend-countdown" id="resend-countdown">
                <span>¿No recibiste el código? Reenviar en</span>
                <span class="resend-timer-badge" id="countdown-timer">--</span>
            </div>
            <a class="resend-btn" id="resend-btn" href="<?= htmlspecialchars($basePath . '/otp?reenviar=1', ENT_QUOTES, 'UTF-8') ?>">Reenviar código</a>
        </div>

        <a class="back-link" href="<?= htmlspecialchars($backLink, ENT_QUOTES, 'UTF-8') ?>">← <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?></a>
    </div>
</div>

<div class="otp-expired-overlay" id="otp-expired-overlay" aria-hidden="true">
    <div class="otp-expired-modal" role="dialog" aria-modal="true">
        <div class="otp-expired-head">
            <div class="otp-expired-title" id="otp-expired-title">Tiempo expirado</div>
            <button type="button" class="otp-expired-close" id="otp-expired-close" aria-label="Cerrar">✕</button>
        </div>
        <div class="otp-expired-body">
            <p id="otp-expired-msg">El tiempo para verificar el código expiró.</p>
        </div>
        <div class="otp-expired-actions">
            <button type="button" class="btn-submit" id="otp-expired-action">Continuar</button>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/splash.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
    document.getElementById('form-otp').addEventListener('submit', () => {
        window.hvShowSplash?.();
    });

    const TIPO = <?= json_encode($tipo) ?>;
    const COUNTDOWN = <?= (int) $countdownSecs ?>;
    const KEY = 'hv_otp_sent_' + TIPO;
    const IS_REENVIADO = <?= $reenviado ? 'true' : 'false' ?>;
    const OTP_USER_ID = <?= (int) $otpUserId ?>;
    const OTP_REMAINING_SECS = <?= (int) $otpRemainingSecs ?>;
    const OTP_EXPIRED_MODAL = <?= $otpExpiredModal ? 'true' : 'false' ?>;

    if (IS_REENVIADO || !sessionStorage.getItem(KEY)) {
        sessionStorage.setItem(KEY, Date.now().toString());
    }

    const sentAt = parseInt(sessionStorage.getItem(KEY));
    const countdownEl = document.getElementById('countdown-timer');
    const countdownWrap = document.getElementById('resend-countdown');
    const resendBtn = document.getElementById('resend-btn');
    const expiryWrap = document.getElementById('otp-expiry');
    const expiryBadge = document.getElementById('otp-expiry-badge');
    const overlay = document.getElementById('otp-expired-overlay');
    const overlayMsg = document.getElementById('otp-expired-msg');
    const overlayAction = document.getElementById('otp-expired-action');
    const overlayClose = document.getElementById('otp-expired-close');
    const otpInput = document.getElementById('otp-input');
    const otpForm = document.getElementById('form-otp');

    function tick() {
        const elapsed = Math.floor((Date.now() - sentAt) / 1000);
        const remaining = Math.max(0, COUNTDOWN - elapsed);

        if (remaining > 0) {
            countdownEl.textContent = remaining + 's';
            countdownWrap.style.display = 'flex';
            resendBtn.style.display = 'none';
        } else {
            countdownWrap.style.display = 'none';
            resendBtn.style.display = 'inline';
        }
    }

    tick();
    setInterval(tick, 1000);

    resendBtn.addEventListener('click', () => {
        try {
            sessionStorage.setItem(KEY, Date.now().toString());
        } catch (e) {
        }
    });

    function formatMMSS(totalSeconds) {
        const sec = Math.max(0, Math.floor(totalSeconds));
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    let expiredShown = false;
    async function openExpiredModal() {
        if (expiredShown) return;
        expiredShown = true;

        if (otpInput) otpInput.disabled = true;
        if (otpForm) {
            otpForm.querySelectorAll('button, input').forEach(el => { el.disabled = true; });
        }

        if (TIPO === 'registro') {
            overlayMsg.textContent = 'Tiempo expirado. Vuelve a registrarte para recibir un nuevo código.';
            overlayAction.textContent = 'Volver a registrarme';
            overlayAction.onclick = () => { window.location.href = <?= json_encode($basePath . '/register') ?>; };

            if (OTP_USER_ID > 0) {
                fetch(<?= json_encode($basePath . '/api/usuarios/cancelar_registro') ?>, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ id: OTP_USER_ID })
                }).catch(() => {});
            }
        } else {
            overlayMsg.textContent = 'Tiempo expirado. Reenvía el código para continuar.';
            overlayAction.textContent = 'Reenviar código';
            overlayAction.onclick = () => { window.location.href = <?= json_encode($basePath . '/otp?reenviar=1') ?>; };
        }

        overlay.classList.add('visible');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeExpiredModal() {
        overlay.classList.remove('visible');
        overlay.setAttribute('aria-hidden', 'true');
    }

    overlayClose.addEventListener('click', closeExpiredModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeExpiredModal(); });

    const expiryStartAt = Date.now();
    function tickExpiry() {
        const elapsed = Math.floor((Date.now() - expiryStartAt) / 1000);
        const remaining = Math.max(0, OTP_REMAINING_SECS - elapsed);
        if (OTP_EXPIRED_MODAL || OTP_REMAINING_SECS <= 0) {
            expiryWrap.style.display = 'none';
            openExpiredModal();
            return;
        }
        expiryWrap.style.display = 'flex';
        expiryBadge.textContent = formatMMSS(remaining);
        if (remaining <= 0) {
            openExpiredModal();
        }
    }

    tickExpiry();
    setInterval(tickExpiry, 1000);
</script>
</body>
</html>
