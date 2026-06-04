// ## Beginn KI generierter Code
const productCatalogUrl = new URL("../config/product.json", document.currentScript?.src || window.location.href).href;

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

    // Die Produktliste kommt aus der zentralen Konfigurationsdatei.
    const response = await fetch(productCatalogUrl);

    if (!response.ok) {
        throw new Error(`Produktdaten konnten nicht geladen werden (${response.status})`);
    }

    // JSON wird in ein normales JavaScript-Objekt umgewandelt.
    const data = await response.json();

    // Jedes Produkt wird als eigene Karte gerendert.
    (data.products || []).forEach(product => {

        const productDiv = document.createElement("div");

        // Einheitliche Klasse fuer das Kartenlayout.
        productDiv.classList.add("product");

        // Bild, Name, Preis und Aktionen werden als HTML in die Karte geschrieben.
        // ##Schluss KI generierter Code
        // ##Beginn Code von Linus
        productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3 class="product-name">${product.name}</h3>
            <div class="price">
                ${formatPrice(product.price)}
            </div>
            <div class="product-buttons">
                <button class="buy-btn"
                    data-id="${product.id}"
                    data-name="${product.name}"
                    data-price="${product.price}"
                    data-image="${product.image}">
                    Kaufen
                </button>
                <button class="detail-btn"
                    onclick="goToProduct(${product.id})">
                    Details
                </button>
            </div>
        `;

        // Die fertige Karte wird an den Shop-Container angehaengt.
        container.appendChild(productDiv);
    });

    // Danach erhalten die Kauf-Buttons ihre Click-Handler fuer den Warenkorb.
    attachBuyEvents();
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

// Bindet alle Kauf-Buttons an die Warenkorb-Funktion aus cart.js.
function attachBuyEvents() {
    document.querySelectorAll(".buy-btn").forEach(button => {
        button.addEventListener("click", function () {
            const id = Number(this.dataset.id);
            const name = this.dataset.name;
            const price = this.dataset.price;
            const image = this.dataset.image;
            

            // addToCart stammt aus cart.js und uebernimmt das eigentliche Hinzufuegen.
            addToCart(id,name, price, image);
        });
    });
}
//## Schluss KI genertierter Code