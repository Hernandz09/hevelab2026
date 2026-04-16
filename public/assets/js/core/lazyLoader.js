const registry = {
    home: () => import("../modules/homeModule.js"),
    dashboard: () => import("../modules/dashboardModule.js"),
    products: () => import("../modules/productsModule.js"),
    users: () => import("../modules/usersModule.js"),
    settings: () => import("../modules/settingsModule.js")
};

export async function lazyLoadModule(pageName) {
    const factory = registry[pageName];
    if (!factory) {
        return null;
    }

    return factory();
}
