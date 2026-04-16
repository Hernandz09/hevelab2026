<section class="page page-users">
    <header class="page-header">
        <div class="page-header-left">
            <h2 class="page-title">Gestión de Usuarios</h2>
            <p class="page-subtitle">Administración de cuentas y accesos</p>
        </div>
    </header>

    <div class="stats-grid stats-grid-compact">
        <article class="stat-card stat-card-compact">
            <div class="stat-meta">
                <div class="stat-value" id="users-stat-total">0</div>
                <div class="stat-label">Total usuarios</div>
            </div>
        </article>
        <article class="stat-card stat-card-compact">
            <div class="stat-meta">
                <div class="stat-value" id="users-stat-verificados">0</div>
                <div class="stat-label">Verificados</div>
            </div>
        </article>
        <article class="stat-card stat-card-compact">
            <div class="stat-meta">
                <div class="stat-value" id="users-stat-facial">0</div>
                <div class="stat-label">Con biometría</div>
            </div>
        </article>
        <article class="stat-card stat-card-compact">
            <div class="stat-meta">
                <div class="stat-value" id="users-stat-pendientes">0</div>
                <div class="stat-label">Pendientes OTP</div>
            </div>
        </article>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-actions users-filters">
                <input id="users-search" class="input" type="search" placeholder="Buscar por nombre o email...">
                <select id="users-status-filter" class="select" aria-label="Filtrar por estado">
                    <option value="all">Todos los estados</option>
                    <option value="verified">Verificados</option>
                    <option value="pending">Pendientes</option>
                </select>
                <select id="users-method-filter" class="select" aria-label="Filtrar por método">
                    <option value="all">Todos los métodos</option>
                    <option value="face">Biometría facial</option>
                    <option value="password">Contraseña + OTP</option>
                </select>
            </div>
            <div class="panel-actions">
                <button id="users-refresh" class="btn btn-warning" type="button">Refrescar</button>
                <button id="users-create" class="btn btn-primary" type="button">Nuevo usuario</button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Autenticación</th>
                    <th>Último acceso</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody id="users-tbody">
                <tr><td colspan="7">Cargando usuarios...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="users-dialog" class="users-dialog">
        <form id="users-form" method="dialog" class="users-dialog-card">
            <header class="users-dialog-header">
                <h3 id="users-dialog-title" class="users-dialog-title">Nuevo usuario</h3>
                <button id="users-dialog-close" class="users-dialog-close" type="button" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M18.3 5.7a1 1 0 0 0-1.4 0L12 10.6L7.1 5.7a1 1 0 0 0-1.4 1.4l4.9 4.9l-4.9 4.9a1 1 0 0 0 1.4 1.4l4.9-4.9l4.9 4.9a1 1 0 0 0 1.4-1.4L13.4 12l4.9-4.9a1 1 0 0 0 0-1.4Z"/>
                    </svg>
                </button>
            </header>

            <input type="hidden" id="user-id">

            <div class="form-field">
                <label class="field-label" for="user-name">Nombre de usuario</label>
                <input id="user-name" class="input" type="text" placeholder="Ej: juan.perez" autocomplete="username" required>
            </div>

            <div class="form-field">
                <label class="field-label" for="user-email">Correo electrónico</label>
                <input id="user-email" class="input" type="email" placeholder="correo@ejemplo.com" autocomplete="email" required>
            </div>

            <div class="form-field" id="user-pass-wrap">
                <label class="field-label" for="user-pass">Contraseña</label>
                <input id="user-pass" class="input" type="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                <div id="user-pass-help" class="field-help">El usuario podrá cambiarla más adelante.</div>
            </div>

            <div class="users-dialog-actions">
                <button id="users-cancel" class="btn btn-ghost" type="button">Cancelar</button>
                <button id="users-save" class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </dialog>
</section>
