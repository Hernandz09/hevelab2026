
(function () {
    const basePath = document.body?.dataset?.basePath || '';
    const imgBase = `${basePath}/public/assets/images/logos/`;

    
    const splash = document.createElement('div');
    splash.id = 'hv-splash';
    splash.innerHTML = `<img src="${imgBase}logo_carga.png" id="hv-splash-logo" alt="">`;
    document.body.prepend(splash);

    
    const sloganEl = document.createElement('img');
    sloganEl.src   = `${imgBase}SLOGAN.png`;
    sloganEl.id    = 'hv-slogan';
    sloganEl.alt   = '';
    sloganEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(sloganEl);

    
    window.hvShowSplash = function () {
        splash.classList.add('hv-splash-active');
    };
})();
