import { AsyncRouter } from "./core/router.js";
import { HookManager } from "./hooks/index.js";
import { appContext } from "./state.js";

async function bootstrap() {
    const mountNode = document.getElementById("app-main");
    if (!mountNode) {
        return;
    }

    const hookManager = new HookManager();
    const hooks = await hookManager.init();

    appContext.hookManager = hookManager;
    appContext.hooks = hooks;

    hooks.theme.applySavedTheme();

    function syncThemeToggle() {
        const btn = document.getElementById("theme-toggle");
        if (!btn) return;
        const dark = document.body.classList.contains("theme-dark");
        btn.setAttribute("aria-pressed", dark ? "true" : "false");
        btn.dataset.mode = dark ? "dark" : "light";
        btn.setAttribute("aria-label", dark ? "Cambiar a modo día" : "Cambiar a modo noche");
    }

    syncThemeToggle();

    document.getElementById("theme-toggle")?.addEventListener("click", () => {
        hooks.theme.toggleTheme();
        syncThemeToggle();
        if (typeof window !== "undefined" && typeof window.showToast === "function") {
            window.showToast("Tema actualizado", "info", 1800);
        }
    });

    const router = new AsyncRouter({
        mountNode,
        onAfterNavigate: async () => {
            if (appContext.hookManager.flags.useTheme) {
                appContext.hooks.theme.applySavedTheme();
            }
            syncThemeToggle();
            if (typeof window !== "undefined" && typeof window.showToast === "function") {
                const pageTitle = document.getElementById("app-page-title")?.textContent?.trim();
                if (pageTitle) {
                    window.showToast(`Vista cargada: ${pageTitle}`, "info", 1600);
                }
            }
        }
    });

    router.start();

    const initialPath = window.location.pathname;
    router.highlightActive(initialPath);

    const initialPage = document.body.getAttribute("data-initial-page") || "home";
    if (initialPath !== "/" || initialPage !== "home") {
        await router.navigate(initialPath, { push: false });
    }
}

bootstrap();
