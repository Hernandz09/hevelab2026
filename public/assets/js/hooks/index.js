import { getJson } from "../core/http.js";
import { useAuth } from "./useAuth.js";
import { useData } from "./useData.js";
import { useTheme } from "./useTheme.js";

export class HookManager {
    constructor() {
        this.flags = {
            useAuth: true,
            useData: true,
            useTheme: true
        };
        this.hooks = {};
    }

    async init() {
        try {
            const response = await getJson("/api/config/flags");
            this.flags = { ...this.flags, ...response.flags };
        } catch (error) {
            console.warn("No se pudieron cargar flags, se usan defaults.", error);
        }

        this.hooks = {
            auth: useAuth(this.flags.useAuth),
            data: useData(this.flags.useData),
            theme: useTheme(this.flags.useTheme)
        };

        return this.hooks;
    }

    setFlags(nextFlags) {
        this.flags = { ...this.flags, ...nextFlags };
        this.hooks = {
            auth: useAuth(this.flags.useAuth),
            data: useData(this.flags.useData),
            theme: useTheme(this.flags.useTheme)
        };

        return this.flags;
    }
}
