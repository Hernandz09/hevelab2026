import { getJson, postJson } from "../core/http.js";

function toast(message, type = "info", duration = 3000) {
    if (typeof window !== "undefined" && typeof window.showToast === "function") {
        window.showToast(message, type, duration);
    }
}

function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function toPercent(threshold) {
    const t = clamp(Number(threshold) || 0.4, 0.1, 1);
    return Math.round(t * 100);
}

function describePercent(p) {
    if (p >= 75) {
        return { level: "Alta seguridad", tag: "Muy estricto", tone: "cyan" };
    }
    if (p >= 55) {
        return { level: "Seguridad equilibrada", tag: "Equilibrado", tone: "green" };
    }
    if (p >= 35) {
        return { level: "Seguridad media", tag: "Permisivo", tone: "amber" };
    }
    return { level: "Baja seguridad", tag: "Muy permisivo", tone: "red" };
}

function renderFaceUI(thresholdValue) {
    const percent = toPercent(thresholdValue);
    const percentNode = document.getElementById("cfg-face-percent");
    const levelNode = document.getElementById("cfg-face-level");
    const tagNode = document.getElementById("cfg-face-tag");
    if (percentNode) percentNode.textContent = String(percent);
    const desc = describePercent(percent);
    if (levelNode) levelNode.textContent = desc.level;
    if (tagNode) {
        tagNode.className = `pill pill-${desc.tone}`;
        const dot = tagNode.querySelector(".pill-dot") || document.createElement("span");
        dot.className = "pill-dot";
        tagNode.textContent = "";
        tagNode.appendChild(dot);
        tagNode.appendChild(document.createTextNode(desc.tag));
    }
}

function setSaved(cfg) {
    const face = document.getElementById("cfg-saved-face");
    const otp = document.getElementById("cfg-saved-otp");
    const max = document.getElementById("cfg-saved-max");
    const updated = document.getElementById("cfg-saved-updated");
    if (face) face.textContent = `${toPercent(cfg.face_threshold ?? 0.4)}%`;
    if (otp) otp.textContent = `${cfg.otp_expiracion_min ?? "-"} min`;
    if (max) max.textContent = String(cfg.max_intentos_login ?? "-");
    if (updated) updated.textContent = String(cfg.updated_at ?? "-");
}

export async function mount() {
    const form = document.getElementById("system-config-form");
    const threshold = document.getElementById("cfg-face-threshold");
    const otp = document.getElementById("cfg-otp-min");
    const maxLogin = document.getElementById("cfg-max-login");
    if (!form || !threshold || !otp || !maxLogin) {
        return;
    }

    try {
        const read = await getJson("/api/system-config");
        const cfg = read.config || {};
        threshold.value = String(cfg.face_threshold ?? 0.4);
        otp.value = String(cfg.otp_expiracion_min ?? 5);
        maxLogin.value = String(cfg.max_intentos_login ?? 5);
        renderFaceUI(threshold.value);
        setSaved(cfg);
    } catch (e) {
        toast("Error al cargar la configuración", "error");
    }

    threshold.addEventListener("input", () => {
        renderFaceUI(threshold.value);
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const payload = {
            face_threshold: Number(threshold.value),
            otp_expiracion_min: Number(otp.value),
            max_intentos_login: Number(maxLogin.value)
        };
        try {
            const response = await postJson("/api/system-config", payload);
            const next = response.config || payload;
            setSaved(next);
            toast(String(response.message || "Configuración guardada."), "success", 2400);
        } catch (e) {
            toast("Error al guardar la configuración", "error");
        }
    });
}
