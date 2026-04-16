import { getJson } from "../core/http.js";

export function useData(enabled = true) {
    return {
        enabled,
        async fetchProducts() {
            if (!enabled) {
                return { items: [], total: 0 };
            }

            return getJson("/api/products");
        }
    };
}
