export function useTheme(enabled = true) {
    const key = "hevelab-theme";
    const legacyKeys = ["hv_theme", "viision-theme"];

    function updateDashLogo(dark) {
        const logo = document.getElementById("dash-logo");
        if (!logo) {
            return;
        }
        const basePath = document.body?.dataset?.basePath || "";
        const imgBase = `${basePath}/public/assets/images/logos/`;
        logo.src = dark ? imgBase + "logodark_02.png" : imgBase + "logo_light_01.png";
    }

    function resolveStoredTheme() {
        const direct = localStorage.getItem(key);
        if (direct === "dark" || direct === "light") {
            return direct;
        }
        for (const k of legacyKeys) {
            const v = localStorage.getItem(k);
            if (v === "dark" || v === "light") {
                return v;
            }
        }
        return "light";
    }

    function persistTheme(value) {
        localStorage.setItem(key, value);
        for (const k of legacyKeys) {
            localStorage.setItem(k, value);
        }
    }

    function applySavedTheme() {
        if (!enabled) {
            return;
        }

        const value = resolveStoredTheme();
        const dark = value === "dark";
        document.body.classList.toggle("theme-dark", dark);
        updateDashLogo(dark);
    }

    function setTheme(mode) {
        if (!enabled) {
            return;
        }

        const value = mode === "dark" ? "dark" : "light";
        const dark = value === "dark";
        document.body.classList.toggle("theme-dark", dark);
        persistTheme(value);
        updateDashLogo(dark);
    }

    function toggleTheme() {
        if (!enabled) {
            return;
        }

        const dark = document.body.classList.toggle("theme-dark");
        persistTheme(dark ? "dark" : "light");
        updateDashLogo(dark);
    }

    return {
        enabled,
        applySavedTheme,
        setTheme,
        toggleTheme
    };
}
