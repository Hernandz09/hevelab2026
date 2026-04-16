export function useAuth(enabled = true) {
    if (!enabled) {
        return {
            enabled,
            isAuthenticated: () => true
        };
    }

    return {
        enabled,
        isAuthenticated: () => true,
        currentUser: () => ({
            id: 1,
            name: "Admin"
        })
    };
}
