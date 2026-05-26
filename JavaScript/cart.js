"use strict";

console.log("CART.JS GELADEN");

// -------------------------------------------------------
// GLOBAL EXPORT (WICHTIGSTER FIX)
// -------------------------------------------------------

window.getCart = function () {
    return JSON.parse(localStorage.getItem('cart')) || [];
};

window.saveCart = function (cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
};

// -------------------------------------------------------
// SHOP INTEGRATION
// -------------------------------------------------------

window.addToCart = function (id,name, price, image = "") {
    const cart = getCart();

    const parsedPrice = parseFloat(price);

    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.menge += 1;
    } else {
        cart.push({
            id: id,
            name: name,
            price: parsedPrice,
            image: image,
            menge: 1
        });
    }

    saveCart(cart);
    showToast(`${name} wurde zum Warenkorb hinzugefügt!`);
};

// -------------------------------------------------------
// TOAST
// -------------------------------------------------------

window.showToast = function (message) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
};

// -------------------------------------------------------
// IMAGE FALLBACK
// -------------------------------------------------------

window.getProductImage = function (name) {
    const images = {
        'Museumsgutschein': 'images/products/Museumsgtuschein.png',
        'Museumsführung': 'images/products/Führung durch das Museum.png',
        'Führung durch das Museum': 'images/products/Führung durch das Museum.png',
    };

    if (images[name]) return images[name];

    const normalized = name.toLowerCase();

    if (normalized.includes('museumsgutschein')) {
        return images['Museumsgutschein'];
    }

    if (normalized.includes('führung') || normalized.includes('museum')) {
        return images['Museumsführung'];
    }

    return '';
};

// -------------------------------------------------------
// PRICE HELPERS
// -------------------------------------------------------

window.parsePrice = function (price) {
    return parseFloat(price);
};

window.formatPrice = function (num) {
    return Number(num).toFixed(2).replace('.', ',') + ' €';
};

// -------------------------------------------------------
// CART RENDER
// -------------------------------------------------------

window.renderCart = function () {
    const cart = getCart();

    const tbody = document.getElementById('cart-body');
    const emptyMsg = document.getElementById('empty-msg');
    const cartTable = document.getElementById('cart-table');

    if (!tbody || !cartTable || !emptyMsg) return;

    tbody.innerHTML = '';

    if (cart.length === 0) {
        cartTable.style.display = 'none';
        emptyMsg.style.display = 'block';
        return;
    }

    cartTable.style.display = 'table';
    emptyMsg.style.display = 'none';

    let total = 0;
    let updated = false;

    cart.forEach((item, index) => {

        const price = parseFloat(item.price);
        const sum = price * item.menge;
        total += sum;

        const img = item.image || getProductImage(item.name) || '';

        if (!item.image && img) {
            item.image = img;
            updated = true;
        }

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${index + 1}</td>

            <td>
                <div class="cart-product">
                    ${img ? `<img src="${img}" alt="${item.name}">` : ''}
                    <span>${item.name}</span>
                </div>
            </td>

            <td>${formatPrice(price)}</td>

            <td>
                <button onclick="changeQty(${index}, -1)">−</button>
                <span>${item.menge}</span>
                <button onclick="changeQty(${index}, 1)">+</button>
            </td>

            <td>${formatPrice(sum)}</td>

            <td>
                <button onclick="removeItem(${index})">Entfernen</button>
            </td>
        `;

        tbody.appendChild(row);
    });

    if (updated) saveCart(cart);

    const netto = total / 1.19;
    const mwst = total - netto;

    document.getElementById('total-price').textContent = formatPrice(total);
    document.getElementById('netto-price').textContent = formatPrice(netto);
    document.getElementById('mwst-price').textContent = formatPrice(mwst);
};

// -------------------------------------------------------
// ACTIONS
// -------------------------------------------------------

window.changeQty = function (index, delta) {
    const cart = getCart();

    cart[index].menge += delta;

    if (cart[index].menge <= 0) {
        cart.splice(index, 1);
    }

    saveCart(cart);
    renderCart();
};

window.removeItem = function (index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    renderCart();
};

window.clearCart = function () {
    if (confirm('Warenkorb wirklich leeren?')) {
        localStorage.removeItem('cart');
        renderCart();
    }
};

window.checkout = function () {
    alert('Bestellung erfolgreich! 🎉');
    localStorage.removeItem('cart');
    renderCart();
};

// -------------------------------------------------------
// INIT
// -------------------------------------------------------

document.addEventListener("DOMContentLoaded", renderCart);