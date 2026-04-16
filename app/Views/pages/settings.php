<section class="page page-settings">
    <header class="page-header">
        <div class="page-header-left">
            <h2 class="page-title">Configuración del Sistema</h2>
            <p class="page-subtitle">HEVELAB · Parámetros de seguridad y autenticación biométrica</p>
        </div>
    </header>

    <div class="settings-grid">
        <form id="system-config-form" class="settings-form-stack">
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Reconocimiento Facial</h3>
                        <p class="panel-subtitle">A mayor porcentaje, mayor seguridad: el sistema exige una similitud más alta entre el rostro capturado y el registrado.</p>
                    </div>
                </div>

                <div class="face-meter">
                    <div class="face-meter-title">Nivel de seguridad del reconocimiento facial</div>
                    <div class="face-meter-hint">Controla qué tan estricto debe ser el sistema al comparar rostros. 100% = máxima seguridad, 10% = mínima seguridad.</div>

                    <div class="face-meter-center">
                        <div class="face-percent"><span id="cfg-face-percent">0</span>%</div>
                        <div class="face-level-main" id="cfg-face-level">Alta seguridad</div>
                        <div class="face-tag-row">
                            <span class="pill pill-cyan" id="cfg-face-tag">
                                <span class="pill-dot" aria-hidden="true"></span>
                                Muy estricto
                            </span>
                        </div>
                    </div>
                </div>

                <div class="range-wrap">
                    <div class="range-scale" aria-hidden="true">
                        <span>10% — Muy permisivo</span>
                        <span>100% — Muy estricto</span>
                    </div>
                    <input id="cfg-face-threshold" type="range" min="0.10" max="1.00" step="0.01" value="0.40" aria-label="Umbral facial">
                    <div class="range-bar" aria-hidden="true"></div>
                    <div class="range-bar-labels" aria-hidden="true">
                        <span class="range-label range-red">Muy permisivo<br><small>10%–35%</small></span>
                        <span class="range-label range-amber">Permisivo<br><small>35%–55%</small></span>
                        <span class="range-label range-green">Equilibrado<br><small>55%–75%</small></span>
                        <span class="range-label range-cyan">Muy estricto<br><small>75%–100%</small></span>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Seguridad y OTP</h3>
                        <p class="panel-subtitle">Configura la caducidad del código OTP y la política de intentos de inicio de sesión.</p>
                    </div>
                </div>

                <div class="otp-grid">
                    <div class="field-block">
                        <div class="field-title">Tiempo de expiración del OTP</div>
                        <div class="field-hint">Número de minutos que el código OTP permanece válido. Recomendado: 5 minutos.</div>
                        <div class="field-inline">
                            <input id="cfg-otp-min" class="input" type="number" min="1" max="60" step="1" required>
                            <span class="field-unit">minutos</span>
                        </div>
                    </div>
                    <div class="field-block">
                        <div class="field-title">Máximo de intentos de inicio de sesión</div>
                        <div class="field-hint">Intentos fallidos permitidos antes de revisión. Recomendado: 5 intentos.</div>
                        <div class="field-inline">
                            <input id="cfg-max-login" class="input" type="number" min="1" max="20" step="1" required>
                            <span class="field-unit">intentos</span>
                        </div>
                    </div>
                </div>

                <button id="cfg-save-btn" class="btn btn-primary btn-save-wide" type="submit">
                    <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4Zm-5 16a3 3 0 1 1 0-6a3 3 0 0 1 0 6ZM6 8V5h9v3H6Z"/>
                    </svg>
                    Guardar configuración
                </button>
            </section>
        </form>

        <aside class="settings-aside">
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Guía de valores</h3>
                        <p class="panel-subtitle">Recomendaciones rápidas</p>
                    </div>
                </div>
                <div class="guide-list">
                    <div class="guide-item guide-cyan">
                        <strong>75% – 100% · Muy estricto</strong>
                        <div>Ideal para alta seguridad. Puede rechazar usuarios en condiciones de luz variable.</div>
                    </div>
                    <div class="guide-item guide-green">
                        <strong>55% – 75% · Equilibrado</strong>
                        <div>Balance óptimo entre seguridad y usabilidad. Recomendado.</div>
                    </div>
                    <div class="guide-item guide-amber">
                        <strong>35% – 55% · Permisivo</strong>
                        <div>Admite más variaciones de iluminación y posición; aumenta el riesgo de falsos positivos.</div>
                    </div>
                    <div class="guide-item guide-red">
                        <strong>10% – 35% · Muy permisivo</strong>
                        <div>No recomendado para entornos de producción.</div>
                    </div>
                </div>
            </section>

            <section class="panel saved-panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Estado actual guardado</h3>
                        <p class="panel-subtitle">Resumen de la última configuración</p>
                    </div>
                </div>
                <div class="saved-rows">
                    <div class="saved-row"><span>Umbral facial</span><strong id="cfg-saved-face">-</strong></div>
                    <div class="saved-row"><span>Expiración OTP</span><strong id="cfg-saved-otp">-</strong></div>
                    <div class="saved-row"><span>Máx. intentos</span><strong id="cfg-saved-max">-</strong></div>
                    <div class="saved-row"><span>Última modificación</span><strong id="cfg-saved-updated">-</strong></div>
                </div>
            </section>
        </aside>
    </div>
</section>
