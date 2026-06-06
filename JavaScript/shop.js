// ## Beginn KI generierter Code (CoPilot)
const productCatalogUrl = new URL("../api/products.php", document.currentScript?.src || window.location.href).href;

// Die Shop-Seite laedt Produkte erst dann, wenn der entsprechende Container vorhanden ist.
document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector(".shop")) {
        loadProducts();
    }
});

// Laedt die Produktdaten aus der JSON-Datei und baut daraus die sichtbaren Produktkarten.
async function loadProducts() {
    // Dieser Container nimmt spaeter alle Produktkarten auf.
    const container = document.querySelector(".shop");

    if (!container) {
        return;
    }

    try {
        // Die Produktliste kommt aus der zentralen Konfigurationsdatei.
        const response = await fetch(productCatalogUrl);

        if (!response.ok) {
            throw new Error(`Produktdaten konnten nicht geladen werden (${response.status})`);
        }

        // JSON wird in ein normales JavaScript-Objekt umgewandelt.
        const data = await response.json();

        // Alte Karten entfernen, falls die Seite neu gerendert wird.
        container.innerHTML = "";

        // Jedes Produkt wird als eigene Karte gerendert.
        (data.products || []).forEach((product) => {
            const productDiv = document.createElement("div");
            productDiv.classList.add("product");

            const productImage = document.createElement("img");
            productImage.src = product.image || "images/image.png";
            productImage.alt = product.name || "Produktbild";

            const title = document.createElement("h3");
            title.className = "product-name";
            title.textContent = product.name || "";

            const price = document.createElement("div");
            price.className = "price";
            price.textContent = formatPrice(product.price);

            const buttons = document.createElement("div");
            buttons.className = "product-buttons";

            const buyButton = document.createElement("button");
            buyButton.type = "button";
            buyButton.className = "buy-btn";
            buyButton.textContent = "Kaufen";
            buyButton.addEventListener("click", () => {
                window.addToCart(
                    Number(product.id),
                    product.name || "",
                    product.price,
                    product.image || ""
                );
            });

            const detailButton = document.createElement("button");
            detailButton.type = "button";
            detailButton.className = "detail-btn";
            detailButton.textContent = "Details";
            detailButton.addEventListener("click", () => {
                goToProduct(product.id);
            });

            buttons.appendChild(buyButton);
            buttons.appendChild(detailButton);

            productDiv.appendChild(productImage);
            productDiv.appendChild(title);
            productDiv.appendChild(price);
            productDiv.appendChild(buttons);

            // Die fertige Karte wird an den Shop-Container angehaengt.
            container.appendChild(productDiv);
        });
    } catch (error) {
        console.error("Shop konnte nicht geladen werden:", error);
        container.innerHTML = '<p class="form-message error-message">Die Produkte konnten gerade nicht geladen werden. Bitte versuche es erneut.</p>';
    }
}

// Wechselt zur Detailseite eines Produkts anhand seiner ID.
function goToProduct(id) {
    window.location.href = "product.php?pid=" + id;
}
//## Schluss Code von Linus
//## Beginn KI genertierter Code
// Formatiert Preise in Schreibweise mit Euro-Symbol.
function formatPrice(num) {
    return Number(num).toFixed(2).replace(".", ",") + " €";
}
//## Schluss KI genertierter Code (CoPilot)
