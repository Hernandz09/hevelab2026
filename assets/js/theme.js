(function() {
    // 1. Cargar preferencia guardada o sistema
    const savedTheme = localStorage.getItem('hv_theme') || localStorage.getItem('viision-theme');
    const systemPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
    
    const currentTheme = savedTheme || (systemPrefersLight ? 'light' : 'dark');
    
    // Asignar inmediatamente para evitar flash
    if (currentTheme === 'light') {
        document.body.classList.add('light-theme');
    }

    // 2. Lógica principal al cargar el DOM
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const isLight = body.classList.contains('light-theme');
        
        // Determinar el path base según la profundidad de la URL
        const path = window.location.pathname.toLowerCase();
        const isRoot = !path.includes('/views/');
        const basePath = isRoot ? 'assets/img/' : '../../assets/img/';

        // Función para actualizar todos los logos del sistema
        function updateAllLogos(light) {
            // Logo del Dashboard (sidebar)
            const dashLogo = document.getElementById('dash-logo');
            if (dashLogo) {
                dashLogo.src = light
                    ? basePath + 'logo_light_01.png'
                    : basePath + 'logodark_02.png';
            }

            // Logo en vistas de auth (.brand-logo img)
            const brandLogo = document.querySelector('.brand-logo img');
            if (brandLogo) {
                // En auth usamos los logos horizontales
                brandLogo.src = light
                    ? basePath + 'horizo_logo_02.png'
                    : basePath + 'horizo_logo_01new.png';
            }

            // Splash logos si existen
            const splashLogo = document.getElementById('hv-splash-logo');
            if (splashLogo) {
                splashLogo.src = light ? basePath + 'only_logo_01.png' : basePath + 'logo_carga.png';
            }
            const splashSlogan = document.getElementById('hv-slogan');
            if (splashSlogan) {
                splashSlogan.src = light ? basePath + 'SLOGANmode.png' : basePath + 'SLOGAN.png';
            }
        }

        // Aplicamos logos iniciales
        updateAllLogos(isLight);

        // BUSCAR SWITCH EXISTENTE (ej: Dashboard)
        const existingSwitch = document.querySelector('.theme-switch');
        
        if (existingSwitch) {
            // Vincular botones existentes
            const exBtnDark = document.getElementById('btn-dark') || existingSwitch.querySelector('.btn-dark');
            const exBtnLight = document.getElementById('btn-light') || existingSwitch.querySelector('.btn-light');

            if (exBtnDark) {
                exBtnDark.addEventListener('click', () => {
                    body.classList.remove('light-theme');
                    localStorage.setItem('hv_theme', 'dark');
                    localStorage.setItem('viision-theme', 'dark');
                    updateAllLogos(false);
                    // Sincronizar clases activas si es necesario
                    if (exBtnLight) exBtnLight.classList.remove('active');
                    exBtnDark.classList.add('active');
                });
            }
            if (exBtnLight) {
                exBtnLight.addEventListener('click', () => {
                    body.classList.add('light-theme');
                    localStorage.setItem('hv_theme', 'light');
                    localStorage.setItem('viision-theme', 'light');
                    updateAllLogos(true);
                    if (exBtnDark) exBtnDark.classList.remove('active');
                    exBtnLight.classList.add('active');
                });
            }
            return; // No inyectar nada más
        }

        // SI NO HAY SWITCH, INYECTAR EL FLOTANTE (Solo para pantallas de Auth)
        const switchWrap = document.createElement('div');
        switchWrap.className = 'theme-switch-wrap';
        switchWrap.innerHTML = `
            <div class="theme-switch" role="group" aria-label="Theme switcher">
                <button type="button" class="btn-dark ${!isLight ? 'active' : ''}" title="Modo oscuro">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <button type="button" class="btn-light ${isLight ? 'active' : ''}" title="Modo claro">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </button>
            </div>
        `;
        body.appendChild(switchWrap);

        const btnDark = switchWrap.querySelector('.btn-dark');
        const btnLight = switchWrap.querySelector('.btn-light');

        btnDark.addEventListener('click', () => {
            body.classList.remove('light-theme');
            localStorage.setItem('hv_theme', 'dark');
            btnDark.classList.add('active');
            btnLight.classList.remove('active');
            updateAllLogos(false);
        });

        btnLight.addEventListener('click', () => {
            body.classList.add('light-theme');
            localStorage.setItem('hv_theme', 'light');
            btnLight.classList.add('active');
            btnDark.classList.remove('active');
            updateAllLogos(true);
        });
    });
})();
