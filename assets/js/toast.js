
(function () {
    
    const container = document.createElement('div');
    container.id = 'hv-toast-container';
    document.body.appendChild(container);

    const ICONS = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    };

    
    window.showToast = function (message, type = 'info', duration = 4500) {
        const toast = document.createElement('div');
        toast.className = `hv-toast hv-toast-${type}`;
        toast.setAttribute('role', 'alert');

        toast.innerHTML = `
            <div class="hv-toast-body">
                <span class="hv-toast-icon">${ICONS[type] || ICONS.info}</span>
                <span class="hv-toast-msg">${message}</span>
                <button class="hv-toast-close" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="hv-toast-progress"></div>
        `;

        container.appendChild(toast);

        
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('hv-toast-visible'));
        });

        
        const bar = toast.querySelector('.hv-toast-progress');
        bar.style.transitionDuration = duration + 'ms';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => bar.classList.add('hv-toast-progress-run'));
        });

        
        let dismissTimer = setTimeout(() => dismiss(toast), duration);

        
        toast.addEventListener('mouseenter', () => {
            clearTimeout(dismissTimer);
            bar.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            dismissTimer = setTimeout(() => dismiss(toast), 1200);
            bar.style.animationPlayState = 'running';
        });

        
        toast.querySelector('.hv-toast-close').addEventListener('click', () => {
            clearTimeout(dismissTimer);
            dismiss(toast);
        });

        function dismiss(el) {
            el.classList.add('hv-toast-out');
            setTimeout(() => el.remove(), 380);
        }

        return toast;
    };
})();
