const grid = document.getElementById("accommodationsGrid");
const emptyState = document.getElementById("emptyState");
const searchInput = document.getElementById("searchInput");
const styleSelect = document.getElementById("styleSelect");
const reloadBtn = document.getElementById("reloadBtn");

async function loadAccommodations() {
    const q = encodeURIComponent(searchInput.value.trim());
    const style = encodeURIComponent(styleSelect.value);
    const res = await fetch(`/api/accommodations?q=${q}&style=${style}`, {
        headers: { Accept: "application/json" },
    });
    if (!res.ok) {
        throw new Error("Failed to load accommodations");
    }
    const data = await res.json();
    const rows = data.accommodations || [];

    if (!rows.length) {
        grid.innerHTML = "";
        emptyState.style.display = "block";
        return;
    }

    emptyState.style.display = "none";
    grid.innerHTML = rows
        .map(
            (row) => `
            <article class="card">
                <h3>${row.name}</h3>
                <div class="muted">${row.city}, ${row.country}</div>
                <div class="muted">${row.description || ""}</div>
                <div><strong>${row.currency} ${Number(row.nightly_rate).toFixed(2)}</strong> / night</div>
                <span class="tag">${row.style}</span>
            </article>
        `,
        )
        .join("");
}

reloadBtn?.addEventListener("click", loadAccommodations);
styleSelect?.addEventListener("change", loadAccommodations);
searchInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") loadAccommodations();
});

document.addEventListener("DOMContentLoaded", loadAccommodations);
