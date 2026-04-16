<?php $basePath = rtrim(str_replace('\\', '/', (string) dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'); ?>
<?php $basePath = $basePath === '/' ? '' : $basePath; ?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="<?= htmlspecialchars($basePath . '/dashboard', ENT_QUOTES, 'UTF-8') ?>" data-link class="sidebar-logo">
            <img
                id="dash-logo"
                class="sidebar-logo-img"
                src="<?= htmlspecialchars($basePath . '/public/assets/images/logos/logo_light_01.png', ENT_QUOTES, 'UTF-8') ?>"
                data-src-light="<?= htmlspecialchars($basePath . '/public/assets/images/logos/logo_light_01.png', ENT_QUOTES, 'UTF-8') ?>"
                data-src-dark="<?= htmlspecialchars($basePath . '/public/assets/images/logos/logodark_02.png', ENT_QUOTES, 'UTF-8') ?>"
                alt="HEVELAB"
            >
        </a>
    </div>

    <nav class="menu" aria-label="Principal">
        <p class="menu-section">PRINCIPAL</p>
        <a href="<?= htmlspecialchars($basePath . '/dashboard', ENT_QUOTES, 'UTF-8') ?>" data-link class="menu-link">
            <span class="menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 17h8v4H3v-4z"/>
                </svg>
            </span>
            Dashboard
        </a>
        <a href="<?= htmlspecialchars($basePath . '/users', ENT_QUOTES, 'UTF-8') ?>" data-link class="menu-link">
            <span class="menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </span>
            Usuarios
        </a>
        <a href="<?= htmlspecialchars($basePath . '/products', ENT_QUOTES, 'UTF-8') ?>" data-link class="menu-link">
            <span class="menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7h-9"/>
                    <path d="M14 17H5"/>
                    <circle cx="17" cy="17" r="3"/>
                    <circle cx="7" cy="7" r="3"/>
                </svg>
            </span>
            Productos
        </a>

        <p class="menu-section">SISTEMA</p>
        <a href="<?= htmlspecialchars($basePath . '/settings', ENT_QUOTES, 'UTF-8') ?>" data-link class="menu-link">
            <span class="menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33h0A1.65 1.65 0 0 0 9 3.09V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0A1.65 1.65 0 0 0 20.91 11H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </span>
            Configuración
        </a>
        <a href="<?= htmlspecialchars($basePath . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="menu-link">
            <span class="menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="M16 17l5-5-5-5"/>
                    <path d="M21 12H9"/>
                </svg>
            </span>
            Cerrar sesión
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="sidebar-avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr((string) ($userName ?? 'U'), 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="sidebar-user-meta">
            <div class="sidebar-user-name"><?= htmlspecialchars((string) ($userName ?? 'Usuario'), ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (is_string($userEmail ?? null) && $userEmail !== ''): ?>
                <div class="sidebar-user-email"><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>
    </div>
</aside>
