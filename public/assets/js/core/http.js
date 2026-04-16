export async function getJson(url) {
    const response = await fetch(resolveUrl(url), {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}

function resolveUrl(url) {
    const basePath = document.body?.dataset?.basePath || "";
    const value = String(url || "");

    if (!basePath) {
        return value;
    }

    if (value.startsWith("http://") || value.startsWith("https://")) {
        return value;
    }

    if (value === basePath || value.startsWith(basePath + "/")) {
        return value;
    }

    if (value.startsWith("/")) {
        return basePath + value;
    }

    return basePath + "/" + value;
}

export async function postJson(url, body) {
    const response = await fetch(resolveUrl(url), {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(body)
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}
