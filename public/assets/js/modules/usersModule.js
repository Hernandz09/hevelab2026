import { getJson, postJson } from "../core/http.js";

function toast(message, type = "info", duration = 3000) {
    if (typeof window !== "undefined" && typeof window.showToast === "function") {
        window.showToast(message, type, duration);
    }
}

function formatDate(value) {
    if (!value) {
        return "-";
    }
    const dt = new Date(value);
    return Number.isNaN(dt.getTime()) ? String(value) : dt.toLocaleString("es-ES");
}

function rowTemplate(user) {
    const verified = Number(user.verificado) ? true : false;
    const hasFace = Number(user.tiene_facial) ? true : false;
    const status = verified
        ? `<span class="badge badge-success"><span class="badge-dot"></span>Verificado</span>`
        : `<span class="badge badge-warning"><span class="badge-dot"></span>Pendiente</span>`;
    const auth = hasFace
        ? `<span class="badge badge-info"><span class="badge-dot"></span>Facial</span>`
        : `<span class="badge"><span class="badge-dot"></span>OTP</span>`;

    const faceAction = hasFace
        ? `<button class="btn btn-warning" data-action="face-reset" type="button">Reset facial</button>`
        : `<button class="btn btn-warning" data-action="face-add" type="button">Agregar facial</button>`;

    return `<tr data-id="${user.id}">
        <td>${user.id ?? ""}</td>
        <td>${user.usuario ?? ""}<div class="muted">${user.email ?? ""}</div></td>
        <td>${status}</td>
        <td>${auth}</td>
        <td>${formatDate(user.ultimo_login)}</td>
        <td>${formatDate(user.created_at)}</td>
        <td>
            <button class="btn btn-info" data-action="edit" type="button">Editar</button>
            <button class="btn btn-warning" data-action="toggle" type="button">${verified ? "Suspender" : "Verificar"}</button>
            ${faceAction}
            <button class="btn btn-danger" data-action="delete" type="button">Eliminar</button>
        </td>
    </tr>`;
}

function loadStyleOnce(href) {
    const url = String(href || "");
    if (!url) return Promise.reject(new Error("href requerido"));
    const existing = document.querySelector(`link[rel="stylesheet"][href="${CSS.escape(url)}"]`);
    if (existing) return Promise.resolve();
    return new Promise((resolve, reject) => {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = url;
        link.onload = () => resolve();
        link.onerror = () => reject(new Error("No se pudo cargar CSS"));
        document.head.appendChild(link);
    });
}

function loadScriptOnce(src) {
    const url = String(src || "");
    if (!url) return Promise.reject(new Error("src requerido"));
    const existing = document.querySelector(`script[src="${CSS.escape(url)}"]`);
    if (existing) return Promise.resolve();
    return new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = url;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error("No se pudo cargar script"));
        document.head.appendChild(script);
    });
}

async function ensureFaceCaptureReady() {
    const basePath = document.body?.dataset?.basePath || "";
    window.FACE_MODELS_URL = `${basePath}/public/assets/models/face-api`;
    window.FACE_API_UPDATE_STAGE = `${basePath}/api/usuarios/actualizar_etapa`;
    await loadStyleOnce(`${basePath}/public/assets/css/pages/auth/face_auth.css`);
    await loadScriptOnce("https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js");
    await loadScriptOnce(`${basePath}/public/assets/js/legacy/face_auth.js`);
    if (!window.FaceAuth || typeof window.FaceAuth.openRegisterCapture !== "function") {
        throw new Error("FaceAuth no disponible");
    }
}

export async function mount() {
    const tbody = document.getElementById("users-tbody");
    const search = document.getElementById("users-search");
    const statusFilter = document.getElementById("users-status-filter");
    const methodFilter = document.getElementById("users-method-filter");
    const refreshBtn = document.getElementById("users-refresh");
    const createBtn = document.getElementById("users-create");
    const dialog = document.getElementById("users-dialog");
    const form = document.getElementById("users-form");
    const idInput = document.getElementById("user-id");
    const nameInput = document.getElementById("user-name");
    const emailInput = document.getElementById("user-email");
    const passInput = document.getElementById("user-pass");
    const passWrap = document.getElementById("user-pass-wrap");
    const passHelp = document.getElementById("user-pass-help");
    const title = document.getElementById("users-dialog-title");
    const closeBtn = document.getElementById("users-dialog-close");
    const cancelBtn = document.getElementById("users-cancel");
    const statTotal = document.getElementById("users-stat-total");
    const statVerified = document.getElementById("users-stat-verificados");
    const statFace = document.getElementById("users-stat-facial");
    const statPending = document.getElementById("users-stat-pendientes");

    if (!tbody || !search || !statusFilter || !methodFilter || !refreshBtn || !createBtn || !dialog || !form || !idInput || !nameInput || !emailInput || !passInput || !passWrap || !title || !closeBtn || !cancelBtn) {
        return;
    }

    let users = [];

    async function load({ notify = false } = {}) {
        try {
            const data = await getJson("/api/usuarios/listar");
            users = data.usuarios || [];
            renderStats();
            render();
            if (notify) {
                toast("Usuarios actualizados", "success", 2000);
            }
        } catch (e) {
            toast("Error al cargar usuarios", "error");
        }
    }

    function render() {
        const term = search.value.trim().toLowerCase();
        const status = String(statusFilter.value || "all");
        const method = String(methodFilter.value || "all");

        const filtered = users.filter((u) => {
            const name = String(u.usuario || "").toLowerCase();
            const email = String(u.email || "").toLowerCase();
            const matchesTerm = !term || name.includes(term) || email.includes(term);
            const matchesStatus = status === "all"
                || (status === "verified" && Number(u.verificado))
                || (status === "pending" && !Number(u.verificado));
            const matchesMethod = method === "all"
                || (method === "face" && Number(u.tiene_facial))
                || (method === "password" && !Number(u.tiene_facial));
            return matchesTerm && matchesStatus && matchesMethod;
        });

        if (!filtered.length) {
            tbody.innerHTML = '<tr><td colspan="7">Sin resultados.</td></tr>';
            return;
        }
        tbody.innerHTML = filtered.map(rowTemplate).join("");
    }

    function renderStats() {
        const total = users.length;
        const verified = users.filter((u) => Number(u.verificado)).length;
        const face = users.filter((u) => Number(u.tiene_facial)).length;
        const pending = users.filter((u) => !Number(u.verificado)).length;

        if (statTotal) statTotal.textContent = String(total);
        if (statVerified) statVerified.textContent = String(verified);
        if (statFace) statFace.textContent = String(face);
        if (statPending) statPending.textContent = String(pending);
    }

    function openCreate() {
        idInput.value = "";
        nameInput.value = "";
        emailInput.value = "";
        passInput.value = "";
        passInput.required = true;
        passInput.placeholder = "Mínimo 8 caracteres";
        if (passHelp) {
            passHelp.textContent = "El usuario podrá cambiarla más adelante.";
        }
        passWrap.style.display = "grid";
        title.textContent = "Nuevo usuario";
        dialog.showModal();
    }

    function openEdit(userId) {
        const user = users.find((u) => Number(u.id) === userId);
        if (!user) return;
        idInput.value = String(user.id);
        nameInput.value = String(user.usuario || "");
        emailInput.value = String(user.email || "");
        passInput.value = "";
        passInput.required = false;
        passInput.placeholder = "Dejar vacío para no cambiar";
        if (passHelp) {
            passHelp.textContent = "Déjala vacía si no quieres cambiarla.";
        }
        passWrap.style.display = "grid";
        title.textContent = "Editar usuario";
        dialog.showModal();
    }

    search.addEventListener("input", render);
    statusFilter.addEventListener("change", render);
    methodFilter.addEventListener("change", render);
    refreshBtn.addEventListener("click", () => load({ notify: true }));
    createBtn.addEventListener("click", () => {
        openCreate();
        toast("Crear usuario", "info", 1600);
    });
    closeBtn.addEventListener("click", () => dialog.close());
    cancelBtn.addEventListener("click", () => dialog.close());

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const password = passInput.value.trim();
        const payload = {
            id: idInput.value ? Number(idInput.value) : undefined,
            usuario: nameInput.value.trim(),
            email: emailInput.value.trim()
        };
        const isEdit = Boolean(idInput.value);
        if (!payload.usuario || !payload.email) {
            toast("Completa usuario y correo electrónico", "error");
            return;
        }
        if (password) {
            if (password.length < 8) {
                toast("La contraseña debe tener mínimo 8 caracteres", "error");
                return;
            }
            payload.password = password;
        } else if (!isEdit) {
            toast("La contraseña es obligatoria para crear el usuario", "error");
            return;
        }
        try {
            const res = await postJson(isEdit ? "/api/usuarios/editar" : "/api/usuarios/crear", payload);
            toast(String(res.message || (isEdit ? "Usuario actualizado." : "Usuario creado.")), "success", 2400);
            dialog.close();
            await load();
        } catch (e) {
            toast("Error al guardar usuario", "error");
        }
    });

    tbody.addEventListener("click", async (event) => {
        const button = event.target.closest("button[data-action]");
        if (!button) return;
        const row = button.closest("tr[data-id]");
        if (!row) return;
        const id = Number(row.dataset.id);
        const action = button.dataset.action;
        const user = users.find((u) => Number(u.id) === id);
        if (!user) return;

        if (action === "edit") {
            openEdit(id);
            toast("Editar usuario", "info", 1600);
            return;
        }
        if (action === "toggle") {
            try {
                const res = await postJson("/api/usuarios/toggle_verificado", { id, verificado: Number(user.verificado) ? 0 : 1 });
                toast(String(res.message || "Estado actualizado."), "success", 2200);
                await load();
            } catch (e) {
                toast("Error al cambiar estado", "error");
            }
            return;
        }
        if (action === "face-reset") {
            try {
                const res = await postJson("/api/usuarios/resetear_facial", { id });
                toast(String(res.message || "Biometría reseteada."), "success", 2200);
                await load();
            } catch (e) {
                toast("Error al resetear biometría", "error");
            }
            return;
        }
        if (action === "face-add") {
            try {
                toast("Abriendo cámara...", "info", 1800);
                await ensureFaceCaptureReady();
                window.currentUserId = id;
                window.FaceAuth.openRegisterCapture(async (descriptor) => {
                    try {
                        const res = await postJson("/api/usuarios/guardar_facial", { id, descriptor });
                        toast(String(res.message || "Biometría facial registrada."), "success", 2600);
                        await load();
                    } catch (e) {
                        toast("Error al guardar biometría facial", "error");
                    }
                });
            } catch (e) {
                toast("No se pudo iniciar el reconocimiento facial", "error");
            }
            return;
        }
        if (action === "delete") {
            if (!window.confirm("Deseas eliminar este usuario?")) return;
            try {
                const res = await postJson("/api/usuarios/eliminar", { id });
                toast(String(res.message || "Usuario eliminado."), "success", 2400);
                await load();
            } catch (e) {
                toast("Error al eliminar usuario", "error");
            }
        }
    });

    await load();
}
