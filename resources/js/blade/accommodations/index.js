const grid = document.getElementById("accommodationsGrid");
const emptyState = document.getElementById("emptyState");
const searchInput = document.getElementById("searchInput");
const styleSelect = document.getElementById("styleSelect");
const budgetSelect = document.getElementById("budgetSelect");
const reloadBtn = document.getElementById("reloadBtn");
const aiMatchPanel = document.getElementById("aiMatchPanel");
const aiMatchSummary = document.getElementById("aiMatchSummary");

const budgetToNightlyCap = {
    backpacker: 40,
    budget: 90,
    mid: 180,
    premium: 350,
    luxury: Infinity,
};

function getTripProfile() {
    try {
        const raw = localStorage.getItem("smartBookingTripProfile");
        return raw ? JSON.parse(raw) : null;
    } catch (_e) {
        return null;
    }
}

function calcAiScore(row, profile, selectedBudget) {
    let score = 0;
    const style = (row.style || "").toLowerCase();
    const desc = (row.description || "").toLowerCase();
    const budget = selectedBudget !== "any" ? selectedBudget : profile?.budget;
    const preferredStyle = profile?.accommodation;
    const mood = (profile?.mood || "").toLowerCase();
    const feeling = (profile?.feeling_note || "").toLowerCase();

    if (preferredStyle && preferredStyle !== "any" && style === preferredStyle) score += 32;
    if (mood === "relaxed" && (style.includes("resort") || style.includes("villa"))) score += 18;
    if (mood === "adventurous" && (style.includes("hostel") || style.includes("glamping"))) score += 18;
    if (mood === "romantic" && (style.includes("boutique") || style.includes("villa"))) score += 16;
    if (mood === "foodie" && desc.includes("restaurant")) score += 10;
    if (mood === "eco-travel" && (style.includes("glamping") || desc.includes("eco"))) score += 14;
    if (feeling && /calm|relax|peace|tired|burnout/.test(feeling) && (style.includes("resort") || style.includes("villa"))) score += 10;

    const rate = Number(row.nightly_rate || 0);
    const cap = budgetToNightlyCap[budget] ?? Infinity;
    if (rate <= cap) score += 24;
    else if (rate <= cap * 1.3) score += 10;

    const rating = Number(row.rating || 0);
    score += Math.round(rating * 4);

    return Math.max(0, Math.min(100, score));
}

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
    const selectedBudget = budgetSelect?.value || "any";
    const profile = getTripProfile();
    const enrichedRows = rows
        .map((row) => ({
            ...row,
            aiScore: calcAiScore(row, profile, selectedBudget),
        }))
        .filter((row) => {
            if (selectedBudget === "any") return true;
            const cap = budgetToNightlyCap[selectedBudget] ?? Infinity;
            return Number(row.nightly_rate || 0) <= cap * 1.5;
        })
        .sort((a, b) => b.aiScore - a.aiScore || Number(a.nightly_rate) - Number(b.nightly_rate));

    if (!enrichedRows.length) {
        grid.innerHTML = "";
        emptyState.style.display = "block";
        if (aiMatchPanel) aiMatchPanel.style.display = "none";
        return;
    }

    emptyState.style.display = "none";
    if (aiMatchPanel && aiMatchSummary) {
        aiMatchPanel.style.display = "block";
        const top = enrichedRows[0];
        aiMatchSummary.textContent = `Top match: ${top.name} (${top.city}) with AI score ${top.aiScore}/100 based on your trip mood, budget, and accommodation preference.`;
    }

    grid.innerHTML = enrichedRows
        .map(
            (row) => `
            <article class="card">
                <h3>${row.name}</h3>
                <div class="muted">${row.city}, ${row.country}</div>
                <div class="muted">${row.description || ""}</div>
                <div><strong>${row.currency} ${Number(row.nightly_rate).toFixed(2)}</strong> / night</div>
                <div class="ai-score"><i class="fas fa-wand-magic-sparkles"></i> AI Match ${row.aiScore}/100</div>
                <span class="tag">${row.style}</span>
            </article>
        `,
        )
        .join("");
}

reloadBtn?.addEventListener("click", loadAccommodations);
styleSelect?.addEventListener("change", loadAccommodations);
budgetSelect?.addEventListener("change", loadAccommodations);
searchInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") loadAccommodations();
});

document.addEventListener("DOMContentLoaded", loadAccommodations);
