<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'HeveLab MVC', ENT_QUOTES, 'UTF-8') ?></title>
    <?php $basePath = rtrim(str_replace('\\', '/', (string) dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'); ?>
    <?php $basePath = $basePath === '/' ? '' : $basePath; ?>
    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $userName = $userName ?? (string) ($_SESSION['user_name'] ?? 'Usuario');
    $userEmail = $userEmail ?? (string) ($_SESSION['user_email'] ?? '');
    ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/public/assets/images/icons/favicon.png', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($basePath . '/public/assets/images/icons/logo.svg', ENT_QUOTES, 'UTF-8') ?>">
    <?php $sidebarCssV = @filemtime(__DIR__ . '/../../../public/assets/css/components/sidebar.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/layouts/base.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/topbar.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/sidebar.css?v=' . $sidebarCssV, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/footer.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/cards.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/components/toast.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/home.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/dashboard.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/products.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/users.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/assets/css/pages/settings.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body data-initial-page="<?= htmlspecialchars($initialPage ?? 'home', ENT_QUOTES, 'UTF-8') ?>" data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">
<script>
    (function () {
        try {
            var v = localStorage.getItem("hevelab-theme") || localStorage.getItem("hv_theme") || localStorage.getItem("viision-theme") || "light";
            if (v === "dark") document.body.classList.add("theme-dark");
        } catch (e) {
        }
    })();
</script>
<div class="app-shell">
    <?php require __DIR__ . '/../components/topbar.php'; ?>
    <?php require __DIR__ . '/../components/sidebar.php'; ?>
    <script>
        (function () {
            var logo = document.getElementById("dash-logo");
            if (!logo) return;
            function syncLogo() {
                var dark = document.body.classList.contains("theme-dark");
                var next = dark ? (logo.dataset.srcDark || "") : (logo.dataset.srcLight || "");
                if (next && logo.getAttribute("src") !== next) {
                    logo.setAttribute("src", next);
                }
            }
            syncLogo();

            if (window.MutationObserver) {
                var mo = new MutationObserver(function () { syncLogo(); });
                mo.observe(document.body, { attributes: true, attributeFilter: ["class"] });
            }
        })();
    </script>

    <main id="app-main" class="app-main" aria-live="polite">
        <?= \App\Core\View::render('pages.' . ($initialPage ?? 'home')) ?>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</div>

<script src="<?= htmlspecialchars($basePath . '/public/assets/js/legacy/toast.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script type="module" src="<?= htmlspecialchars($basePath . '/public/assets/js/app.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
