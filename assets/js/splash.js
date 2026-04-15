
(function () {
    const _src = document.currentScript ? document.currentScript.src : '';
    const BASE  = _src ? _src.replace(/js\/splash\.js.*$/, '') : '../../assets/';

    
    const splash = document.createElement('div');
    splash.id = 'hv-splash';
    splash.innerHTML = `<img src="${BASE}img/logo_carga.png" id="hv-splash-logo" alt="">`;
    document.body.prepend(splash);

    
    const sloganEl = document.createElement('img');
    sloganEl.src   = `${BASE}img/SLOGAN.png`;
    sloganEl.id    = 'hv-slogan';
    sloganEl.alt   = '';
    sloganEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(sloganEl);

    
    window.hvShowSplash = function () {
        splash.classList.add('hv-splash-active');
    };
})();
