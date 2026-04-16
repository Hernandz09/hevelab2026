<?php $basePath = rtrim(str_replace('\\', '/', (string) dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'); ?>
<?php $basePath = $basePath === '/' ? '' : $basePath; ?>
<header class="topbar">
    <div class="topbar-left">
        <div class="topbar-title-wrap">
            <h1 id="app-page-title" class="topbar-title"><?= htmlspecialchars($title ?? 'Panel', ENT_QUOTES, 'UTF-8') ?></h1>
            <p id="app-page-subtitle" class="topbar-subtitle">VIISION ERP · <?= date('d \\d\\e F \\d\\e Y') ?></p>
        </div>
    </div>
    <div class="topbar-right">
        <span class="status-pill" title="Estado">
            <span class="status-dot" aria-hidden="true"></span>
            En línea
        </span>
        <button id="dashboard-cleanup" class="btn btn-danger-soft btn-with-icon" type="button" hidden>
            <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M9 3h6l1 2h5v2H3V5h5l1-2Zm1 7h2v9h-2v-9Zm4 0h2v9h-2v-9ZM7 10h2v9H7v-9Z"/>
            </svg>
            Limpiar no verificados
        </button>
        <button id="theme-toggle" class="theme-mode theme-mode-icon-only" type="button" aria-label="Cambiar tema" aria-pressed="false">
            <span class="theme-mode-knob" aria-hidden="true">
                <svg class="theme-mode-icon theme-mode-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M12 18a6 6 0 1 1 0-12a6 6 0 0 1 0 12Zm0-16h2v3h-2V2ZM12 19h2v3h-2v-3ZM4.22 5.64l1.42-1.42l2.12 2.12L6.34 7.76L4.22 5.64ZM16.24 17.66l1.42-1.42l2.12 2.12l-1.42 1.42l-2.12-2.12ZM2 12h3v2H2v-2Zm19 0h3v2h-3v-2ZM4.22 18.36l2.12-2.12l1.42 1.42l-2.12 2.12l-1.42-1.42ZM16.24 6.34l2.12-2.12l1.42 1.42l-2.12 2.12l-1.42-1.42Z"/>
                </svg>
                <svg class="theme-mode-icon theme-mode-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M21 14.5A8.5 8.5 0 0 1 9.5 3a6.5 6.5 0 1 0 11.5 11.5Z"/>
                </svg>
            </span>
        </button>
    </div>
</header>
