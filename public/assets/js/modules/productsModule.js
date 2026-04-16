import { appContext } from "../state.js";

export async function mount() {
    const statusNode = document.getElementById("products-status");
    const listNode = document.getElementById("products-list");

    if (!statusNode || !listNode) {
        return;
    }

    statusNode.textContent = "Consultando inventario...";

    try {
        const result = await appContext.hooks.data.fetchProducts();
        listNode.innerHTML = result.items
            .map((item) => `<li>${item.name} - ${item.category} - Stock: ${item.stock}</li>`)
            .join("");
        statusNode.textContent = `Total: ${result.total}`;
    } catch (error) {
        statusNode.textContent = "Error al cargar productos";
    }
}
