// ## Beginn Code von Linus generiert mit Unterstützung von Codex
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    // Das Filterfeld und die Tabellenzeile werden nur auf der Adminseite erwartet.
    const filterInput = document.getElementById("admin-product-filter");
    const tableBody = document.getElementById("admin-products-table-body");
    const countLabel = document.getElementById("admin-product-filter-count");

    if (!filterInput || !tableBody) {
        return;
    }

    const endpoint = filterInput.dataset.productsEndpoint || "api/products.php";
    // Verhindert unnötig viele Requests beim schnellen Tippen.
    let debounceTimer = null;

    // Entfernt unsichere Zeichen, damit wir HTML-Injection in der Tabelle vermeiden.
    const escapeHtml = (value) => String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll("\"", "&quot;")
        .replaceAll("'", "&#039;");

    const formatPrice = (value) => {
        const number = Number(value) || 0;
        return number.toFixed(2).replace(".", ",") + " €";
    };

    // Baut die komplette Produkt-Tabelle aus den API-Daten neu auf.
    const renderProducts = (products) => {
        if (!products.length) {
            // Leerer Zustand, wenn keine Treffer gefunden wurden.
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="account-bookings-empty">Keine passenden Produkte gefunden.</td>
                </tr>
            `;
            return;
        }

        // Jede API-Antwort wird in eine Tabellenzeile umgewandelt.
        tableBody.innerHTML = products.map((product) => {
            const imagePath = product.image || "";
            // Wenn kein Bild vorhanden ist, wird nur ein Platzhalter angezeigt.
            const imageMarkup = imagePath
                ? `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(product.name)}" class="product-thumb">`
                : "-";

            return `
                <tr>
                    <td>${escapeHtml(product.id)}</td>
                    <td>${escapeHtml(product.name)}</td>
                    <td>${imageMarkup}</td>
                    <td>${escapeHtml(formatPrice(product.price))}</td>
                    <td>${escapeHtml(product.category)}</td>
                    <td>${escapeHtml(product.subcategory)}</td>
                    <td>
                        <form action="admin.php" method="post" class="admin-inline-form">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="tab" value="${escapeHtml(new URLSearchParams(window.location.search).get("tab") || "new")}">
                            <input type="hidden" name="product_id" value="${escapeHtml(product.id)}">
                            <button type="submit" class="admin-action-button" onclick="return confirm('Dieses Produkt wirklich löschen?');">Löschen</button>
                        </form>
                    </td>
                </tr>
            `;
        }).join("");
    };

    // Holt die gefilterte Produktliste per AJAX vom Server.
    const loadProducts = async () => {
        const query = filterInput.value.trim();
        const url = new URL(endpoint, window.location.href);

        // Der Suchbegriff wird nur mitgeschickt, wenn wirklich etwas eingegeben wurde.
        if (query !== "") {
            url.searchParams.set("q", query);
        }

        try {
            // Der API-Endpoint liefert JSON statt HTML.
            const response = await fetch(url.toString(), {
                headers: {
                    "Accept": "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            // Die Antwort wird als JSON geparst, Fehler werden im catch-Block behandelt.
            const data = await response.json();
            const products = Array.isArray(data.products) ? data.products : [];
            renderProducts(products);

            // Die Trefferanzahl wird direkt neben dem Filter aktualisiert.
            if (countLabel) {
                countLabel.textContent = `${data.count ?? products.length} Produkte`;
            }
        } catch (error) {
            // Falls der Request fehlschlägt, bleibt die Tabelle nicht leer und der Fehler ist sichtbar.
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="account-bookings-empty">Filter konnte nicht geladen werden.</td>
                </tr>
            `;
            if (countLabel) {
                countLabel.textContent = "0 Produkte";
            }
            console.error("Produktfilter konnte nicht geladen werden:", error);
        }
    };

    // Der Filter reagiert live, aber mit leichter Verzögerung.
    filterInput.addEventListener("input", () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(loadProducts, 180);
    });
});
// ## Schluss Code von Linus generiert mit Unterstützung von Codex
