import { getJson } from "./http.js";
import { lazyLoadModule } from "./lazyLoader.js";

export class AsyncRouter {
    constructor({ mountNode, onAfterNavigate }) {
        this.mountNode = mountNode;
        this.onAfterNavigate = onAfterNavigate;
        this.basePath = document.body?.dataset?.basePath || "";
        this.routeMap = {
            "/": "home",
            "/dashboard": "dashboard",
            "/products": "products",
            "/users": "users",
            "/settings": "settings"
        };
        this.pageTitles = {
            home: "Inicio",
            dashboard: "Dashboard",
            products: "Productos",
            users: "Usuarios",
            settings: "Configuración"
        };
    }

    register(path, pageName) {
        this.routeMap[path] = pageName;
    }

    start() {
        this.bindLinks();
        window.addEventListener("popstate", () => {
            this.navigate(window.location.pathname, { push: false });
        });
    }

    bindLinks() {
        document.addEventListener("click", (event) => {
            const target = event.target.closest("a[data-link]");
            if (!target) {
                return;
            }

            event.preventDefault();
            this.navigate(target.getAttribute("href"));
        });
    }

    async navigate(path, options = { push: true }) {
        const internalPath = this.normalizePath(path);
        const page = this.routeMap[internalPath] || "home";
        const fullPath = this.basePath + internalPath;

        if (options.push) {
            history.pushState({}, "", fullPath);
        }

        this.mountNode.innerHTML = "<p>Cargando vista...</p>";

        const payload = await getJson(`${this.basePath}/api/view/${page}`);
        this.mountNode.innerHTML = payload.html;
        this.highlightActive(internalPath);
        this.updateTopbar(page);

        const module = await lazyLoadModule(page);
        if (module && typeof module.mount === "function") {
            await module.mount();
        }

        if (typeof this.onAfterNavigate === "function") {
            await this.onAfterNavigate(page, path);
        }
    }

    updateTopbar(page) {
        const title = this.pageTitles[page] || "Panel";
        document.title = title;

        const titleNode = document.getElementById("app-page-title");
        if (titleNode) {
            titleNode.textContent = title;
        }

        const cleanup = document.getElementById("dashboard-cleanup");
        if (cleanup) {
            cleanup.hidden = page !== "dashboard";
        }
    }

    highlightActive(path) {
        document.querySelectorAll("a[data-link]").forEach((link) => {
            const href = link.getAttribute("href") || "";
            const active = this.normalizePath(href) === path;
            link.classList.toggle("is-active", active);
        });
    }

    normalizePath(path) {
        if (!path) {
            return "/";
        }
        const withoutQuery = String(path).split("?")[0].split("#")[0];
        if (this.basePath && withoutQuery.startsWith(this.basePath)) {
            const sliced = withoutQuery.slice(this.basePath.length);
            return sliced ? (sliced.startsWith("/") ? sliced : `/${sliced}`) : "/";
        }
        return withoutQuery || "/";
    }
}
