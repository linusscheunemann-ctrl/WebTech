document.addEventListener("DOMContentLoaded", () => {
    // Startet das Laden der Produkte
    loadProducts();
});
//  Funktion zum Laden der Produkte aus der JSON-Datei
async function loadProducts() {

    // Lädt die Datei "product.json"
    const response = await fetch("product.json");
    // Wandelt die Antwort in ein JavaScript-Objekt um
    const data = await response.json();

    // Sucht das HTML-Element mit der Klasse "shop"
    const container = document.querySelector(".shop");

    // Geht alle Produkte aus der JSON-Datei durch
    data.products.forEach(product => {

        // Erstellt ein neues <div>-Element
        const productDiv = document.createElement("div");

        // Fügt dem div die CSS-Klasse "product" hinzu
        productDiv.classList.add("product");

        // Fügt den HTML-Inhalt für ein Produkt ein
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

        // Fügt das fertige Produkt in den Shop-Container ein
        container.appendChild(productDiv);
    });


    attachBuyEvents();
}

function goToProduct(id) {
    window.location.href = "product.php?pid=" + id;
}

function formatPrice(num) {
    return Number(num).toFixed(2).replace(".", ",") + " €";
}

function attachBuyEvents() {
    document.querySelectorAll(".buy-btn").forEach(button => {
        button.addEventListener("click", function () {
            const id = Number(this.dataset.id);
            const name = this.dataset.name;
            const price = this.dataset.price;
            const image = this.dataset.image;
            

            addToCart(id,name, price, image);
        });
    });
}