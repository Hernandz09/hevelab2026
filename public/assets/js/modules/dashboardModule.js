import { getJson, postJson } from "../core/http.js";

function formatDate(value) {
    if (!value) {
        return "-";
    }
    const dt = new Date(value);
    return Number.isNaN(dt.getTime()) ? String(value) : dt.toLocaleString("es-ES");
}

function setText(id, value) {
    const node = document.getElementById(id);
    if (node) {
        node.textContent = String(value ?? "");
    }
}

function toast(message, type = "info", duration = 3000) {
    if (typeof window !== "undefined" && typeof window.showToast === "function") {
        window.showToast(message, type, duration);
    }
}

function formatShortDate(value) {
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return String(value || "");
    const parts = dt.toLocaleDateString("es-ES", { day: "2-digit", month: "short" });
    return parts.replace(".", "");
}

function drawLineChart(canvas, series) {
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const width = canvas.clientWidth || canvas.width || 640;
    const height = canvas.height || 220;
    canvas.width = width * devicePixelRatio;
    canvas.height = height * devicePixelRatio;
    ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);

    ctx.clearRect(0, 0, width, height);

    const padL = 44;
    const padR = 14;
    const padT = 14;
    const padB = 34;
    const plotW = Math.max(1, width - padL - padR);
    const plotH = Math.max(1, height - padT - padB);
    const max = Math.max(1, ...series.map((p) => Number(p.count) || 0));
    const stepX = plotW / Math.max(1, series.length - 1);

    const css = typeof window !== "undefined" ? getComputedStyle(document.body) : null;
    const muted = (css?.getPropertyValue("--muted") || "rgba(100, 116, 139, 0.95)").trim();
    const grid = (css?.getPropertyValue("--border") || "rgba(148, 163, 184, 0.28)").trim();
    ctx.font = "12px Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial";
    ctx.textBaseline = "middle";

    const yTicks = 4;
    for (let t = 0; t < yTicks; t++) {
        const value = (max / (yTicks - 1)) * t;
        const y = padT + plotH - (value / max) * plotH;
        ctx.strokeStyle = grid.includes("rgba") ? grid : "rgba(148, 163, 184, 0.28)";
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padL, y);
        ctx.lineTo(width - padR, y);
        ctx.stroke();

        ctx.fillStyle = muted;
        ctx.textAlign = "right";
        ctx.fillText(String(Math.round(value)), padL - 10, y);
    }

    const tickCount = 7;
    const idxSet = new Set();
    for (let i = 0; i < tickCount; i++) {
        const idx = Math.round((series.length - 1) * (i / (tickCount - 1)));
        idxSet.add(idx);
    }
    const idxs = Array.from(idxSet).sort((a, b) => a - b);

    ctx.textBaseline = "top";
    idxs.forEach((idx) => {
        const x = padL + idx * stepX;
        ctx.strokeStyle = grid.includes("rgba") ? grid : "rgba(148, 163, 184, 0.24)";
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x, padT);
        ctx.lineTo(x, padT + plotH);
        ctx.stroke();

        const label = formatShortDate(series[idx]?.date);
        ctx.fillStyle = muted;
        ctx.textAlign = "center";
        ctx.fillText(label, x, padT + plotH + 10);
    });

    const points = series.map((p, i) => {
        const x = padL + i * stepX;
        const y = padT + plotH - ((Number(p.count) || 0) / max) * plotH;
        return { x, y, v: Number(p.count) || 0 };
    });

    ctx.fillStyle = "rgba(0, 196, 212, 0.14)";
    ctx.beginPath();
    points.forEach((pt, i) => {
        if (i === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
    });
    ctx.lineTo(padL + (series.length - 1) * stepX, padT + plotH);
    ctx.lineTo(padL, padT + plotH);
    ctx.closePath();
    ctx.fill();

    ctx.strokeStyle = "#00c4d4";
    ctx.lineWidth = 3;
    ctx.lineJoin = "round";
    ctx.lineCap = "round";
    ctx.beginPath();
    points.forEach((pt, i) => {
        if (i === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
    });
    ctx.stroke();

    const last = points[points.length - 1];
    if (last) {
        ctx.fillStyle = "#00c4d4";
        ctx.beginPath();
        ctx.arc(last.x, last.y, 4.5, 0, Math.PI * 2);
        ctx.fill();
    }
}

function drawDonut(canvas, face, pass) {
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const size = Math.min(canvas.width || 220, canvas.height || 220);
    canvas.width = size * devicePixelRatio;
    canvas.height = size * devicePixelRatio;
    ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);

    const cx = size / 2;
    const cy = size / 2;
    const r = size / 2 - 10;
    const w = Math.max(12, Math.round(size * 0.08));
    const total = Math.max(1, (Number(face) || 0) + (Number(pass) || 0));
    const start = -Math.PI / 2;
    const faceAng = (Number(face) || 0) / total * Math.PI * 2;

    ctx.clearRect(0, 0, size, size);

    ctx.lineWidth = w;
    ctx.lineCap = "round";

    ctx.strokeStyle = "rgba(148, 163, 184, 0.25)";
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.stroke();

    ctx.strokeStyle = "#00c4d4";
    ctx.beginPath();
    ctx.arc(cx, cy, r, start, start + faceAng);
    ctx.stroke();

    ctx.strokeStyle = "#22c55e";
    ctx.beginPath();
    ctx.arc(cx, cy, r, start + faceAng, start + Math.PI * 2);
    ctx.stroke();
}

function userRowTemplate(index, user) {
    const verified = Number(user.verificado) ? true : false;
    const hasFace = Number(user.tiene_facial) ? true : false;
    const status = verified
        ? `<span class="badge badge-success"><span class="badge-dot"></span>Verificado</span>`
        : `<span class="badge badge-warning"><span class="badge-dot"></span>Pendiente</span>`;
    const auth = hasFace
        ? `<span class="badge badge-info"><span class="badge-dot"></span>Facial</span>`
        : `<span class="badge"><span class="badge-dot"></span>OTP</span>`;

    return `<tr>
        <td>${index}</td>
        <td>${user.usuario ?? ""}</td>
        <td>${status}</td>
        <td>${auth}</td>
        <td>${formatDate(user.ultimo_login)}</td>
        <td>${formatDate(user.created_at)}</td>
    </tr>`;
}

export async function mount() {
    const dateNode = document.getElementById("dash-date");
    if (dateNode) {
        dateNode.textContent = new Date().toLocaleDateString("es-ES", { year: "numeric", month: "long", day: "2-digit" });
    }

    const cleanupBtn = document.getElementById("dashboard-cleanup");

    const refresh = document.getElementById("dash-refresh");
    const usersRefresh = document.getElementById("dash-users-refresh");
    const usersSearch = document.getElementById("dash-users-search");
    const usersTbody = document.getElementById("dash-users-tbody");

    const lineCanvas = document.getElementById("dash-line");
    const donutCanvas = document.getElementById("dash-donut");

    let users = [];

    async function loadOverview({ notify = false } = {}) {
        const data = await getJson("/api/dashboard/overview");
        const stats = data.stats || {};
        const series = data.series || [];
        const auth = data.auth || {};

        setText("dash-total", stats.total ?? 0);
        setText("dash-semana", `${stats.week ?? 0} esta semana`);
        setText("dash-facial", stats.facial ?? 0);
        const rate = stats.total ? Math.round((Number(stats.facial) || 0) / Number(stats.total) * 100) : 0;
        setText("dash-facial-rate", `${rate}% del total`);
        setText("dash-hoy", stats.todayVerified ?? 0);
        setText("dash-pendientes", stats.pendingOtp ?? 0);
        setText("dash-eliminados", stats.deletedSession ?? 0);

        setText("dash-donut-total", (Number(auth.face) || 0) + (Number(auth.password) || 0));
        setText("dash-legend-facial", auth.face ?? 0);
        setText("dash-legend-pass", auth.password ?? 0);

        drawLineChart(lineCanvas, series);
        drawDonut(donutCanvas, auth.face ?? 0, auth.password ?? 0);

        if (notify) {
            toast("Dashboard actualizado", "success", 2200);
        }
    }

    async function loadUsers({ notify = false } = {}) {
        const data = await getJson("/api/usuarios/listar");
        users = Array.isArray(data.usuarios) ? data.usuarios : [];
        renderUsers();
        if (notify) {
            toast("Usuarios refrescados", "success", 1800);
        }
    }

    function renderUsers() {
        if (!usersTbody) return;
        const term = String(usersSearch?.value || "").trim().toLowerCase();
        const filtered = users
            .filter((u) => {
                if (!term) return true;
                return String(u.usuario || "").toLowerCase().includes(term) || String(u.email || "").toLowerCase().includes(term);
            })
            .slice(0, 12);

        if (!filtered.length) {
            usersTbody.innerHTML = '<tr><td colspan="6">Sin resultados.</td></tr>';
            return;
        }
        usersTbody.innerHTML = filtered.map((u, i) => userRowTemplate(i + 1, u)).join("");
    }

    refresh?.addEventListener("click", async () => {
        try {
            await loadOverview({ notify: true });
        } catch (e) {
            toast("Error al actualizar el dashboard", "error");
        }
    });
    usersRefresh?.addEventListener("click", async () => {
        try {
            await loadUsers({ notify: true });
        } catch (e) {
            toast("Error al cargar usuarios", "error");
        }
    });
    usersSearch?.addEventListener("input", renderUsers);

    cleanupBtn?.addEventListener("click", async () => {
        cleanupBtn.disabled = true;
        try {
            const res = await postJson("/api/dashboard/cleanup-expired-otp", {});
            toast(`Limpieza completada: ${res.deleted ?? 0} eliminados`, "success", 2600);
            await loadOverview();
        } finally {
            cleanupBtn.disabled = false;
        }
    });

    try {
        await Promise.all([loadOverview(), loadUsers()]);
    } catch (e) {
        toast("Error al cargar datos del dashboard", "error");
    }
}
