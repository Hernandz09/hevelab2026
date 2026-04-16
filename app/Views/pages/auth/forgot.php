<?php

$basePath = (string) ($basePath ?? '');
$error = $error ?? null;
$info = $info ?? null;
$sent = (bool) ($sent ?? false);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEVELAB | Recuperar Contraseña</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/public/assets/images/icons/favicon.png', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/auth/auth.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/toast.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/layouts/light_theme.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">
<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/theme.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/toast.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<?php if (is_string($info) && $info !== ''): ?>
<script>showToast(<?= json_encode($info) ?>, 'info', 7000);</script>
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
                <p class="auth-label-tag">Recuperación</p>
                <h2>Recupera tu <span>acceso.</span></h2>
                <p>Te enviaremos un código de verificación a tu correo electrónico para restablecer tu contraseña.</p>
            </div>

            <img src="<?= htmlspecialchars($basePath . '/public/assets/images/illustrations/enviando.png', ENT_QUOTES, 'UTF-8') ?>" class="hevy-mascot" alt="Mascota Hevy Enviando">

            <div class="auth-left-bottom">
                &copy; <?= date('Y') ?> HEVELAB · VIISION ERP
            </div>
        </div>
    </div>

    <div class="auth-right">
        <p class="auth-form-label">Contraseña olvidada</p>
        <h1>Ingresa tu <span>correo.</span></h1>
        <p class="auth-sub">Te enviaremos un código de 6 dígitos.</p>

        <?php if (!$sent): ?>
        <form method="POST" id="form-forgot" action="<?= htmlspecialchars($basePath . '/forgot', ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-submit">Enviar código</button>
        </form>
        <?php endif; ?>

        <a class="back-link" href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">← Volver al inicio de sesión</a>
    </div>
</div>
<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/splash.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
    document.querySelector('form')?.addEventListener('submit', () => window.hvShowSplash?.());
</script>
</body>
</html>
