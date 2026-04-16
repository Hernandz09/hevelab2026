<section class="page page-dashboard">
    <header class="page-header">
        <div class="page-header-left">
            <h2 class="page-title">Análisis del Sistema</h2>
            <p class="page-subtitle">VIISION ERP · <span id="dash-date"></span></p>
        </div>
    </header>

    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-icon stat-icon-cyan" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-meta">
                <div class="stat-value" id="dash-total">0</div>
                <div class="stat-label">Usuarios totales</div>
                <div class="stat-sub" id="dash-semana">0 esta semana</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-green" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 21c4.418 0 8-3.582 8-8 0-4.418-3.582-8-8-8-4.418 0-8 3.582-8 8 0 4.418 3.582 8 8 8z"/>
                    <path d="M8 13a4 4 0 0 0 8 0"/>
                    <path d="M9 10h.01"/>
                    <path d="M15 10h.01"/>
                </svg>
            </div>
            <div class="stat-meta">
                <div class="stat-value" id="dash-facial">0</div>
                <div class="stat-label">Con biometría facial</div>
                <div class="stat-sub" id="dash-facial-rate">0% del total</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-purple" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v8"/>
                    <path d="M8 12h8"/>
                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
            </div>
            <div class="stat-meta">
                <div class="stat-value" id="dash-hoy">0</div>
                <div class="stat-label">Registros hoy</div>
                <div class="stat-sub">Usuarios verificados</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-amber" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="stat-meta">
                <div class="stat-value" id="dash-pendientes">0</div>
                <div class="stat-label">Pendientes de OTP</div>
                <div class="stat-sub">Sin verificar</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-red" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                </svg>
            </div>
            <div class="stat-meta">
                <div class="stat-value" id="dash-eliminados">0</div>
                <div class="stat-label">Eliminados (sesión)</div>
                <div class="stat-sub">No verificados OTP a tiempo</div>
            </div>
        </article>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Registros en los últimos 30 días</h3>
                    <p class="panel-subtitle">Solamente usuarios verificados (OTP validado)</p>
                </div>
                <div class="panel-actions">
                    <button id="dash-refresh" class="btn btn-warning" type="button">Actualizar</button>
                </div>
            </div>
            <canvas id="dash-line" height="190"></canvas>
        </section>

        <aside class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Métodos de autenticación</h3>
                    <p class="panel-subtitle">Distribución de tipo de acceso</p>
                </div>
            </div>
            <div class="donut-wrap">
                <canvas id="dash-donut" width="120" height="120"></canvas>
                <div class="donut-center">
                    <div class="donut-number" id="dash-donut-total">0</div>
                    <div class="donut-label">usuarios</div>
                </div>
            </div>
            <div class="legend">
                <div class="legend-item">
                    <span class="legend-dot legend-dot-cyan" aria-hidden="true"></span>
                    Biometría facial
                    <span class="legend-value" id="dash-legend-facial">0</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot legend-dot-green" aria-hidden="true"></span>
                    Contraseña + OTP
                    <span class="legend-value" id="dash-legend-pass">0</span>
                </div>
            </div>
        </aside>
    </div>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Usuarios registrados</h3>
                <p class="panel-subtitle">Vista rápida</p>
            </div>
            <div class="panel-actions">
                <input id="dash-users-search" class="input" type="search" placeholder="Buscar usuario...">
                <button id="dash-users-refresh" class="btn btn-warning" type="button">Refrescar</button>
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
                </tr>
                </thead>
                <tbody id="dash-users-tbody">
                <tr><td colspan="6">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</section>
