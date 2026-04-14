/**
 * splash.js — Pantalla de carga + slogan HEVELAB
 * El splash NO se muestra automáticamente.
 * Se invoca manualmente con window.hvShowSplash() antes de un submit.
 */
(function () {
    const _src = document.currentScript ? document.currentScript.src : '';
    const BASE  = _src ? _src.replace(/js\/splash\.js.*$/, '') : '../../assets/';

    // ── Crear splash (oculto por defecto) ────────────────────────
    const splash = document.createElement('div');
    splash.id = 'hv-splash';
    splash.innerHTML = `<img src="${BASE}img/logo_carga.png" id="hv-splash-logo" alt="">`;
    document.body.prepend(splash);

    // ── Crear slogan ──────────────────────────────────────────────
    const sloganEl = document.createElement('img');
    sloganEl.src   = `${BASE}img/SLOGAN.png`;
    sloganEl.id    = 'hv-slogan';
    sloganEl.alt   = '';
    sloganEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(sloganEl);

    // ── API pública: mostrar splash ───────────────────────────────
    window.hvShowSplash = function () {
        splash.classList.add('hv-splash-active');
    };
})();
