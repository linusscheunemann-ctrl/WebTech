document.addEventListener("DOMContentLoaded", () => {
    loadProducts();
});

async function loadProducts() {
    const response = await fetch("product.json");
    const data = await response.json();

    const container = document.querySelector(".shop");
    container.innerHTML = "";

    data.products.forEach(product => {

        const productDiv = document.createElement("div");
        productDiv.classList.add("product");

        productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}">

            <h3 class="product-name">${product.name}</h3>

            <div class="price">${formatPrice(product.price)}</div>

            <div class="product-buttons">

                <button class="buy-btn"
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
            const name = this.dataset.name;
            const price = this.dataset.price;
            const image = this.dataset.image;

            addToCart(name, price, image);
        });
    });
}