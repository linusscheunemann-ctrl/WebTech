"use strict";
// ## Beginn Code von Linus
// Dieser Warenkorb-Helper stellt mehrere globale Funktionen bereit, weil sie
// von unterschiedlichen Seiten, Formularen und Inline-Handlern gemeinsam genutzt werden.
console.log("CART.JS GELADEN");

// Liest den aktuell im Browser gespeicherten Warenkorb aus dem localStorage.
window.getCart = function () {
    return JSON.parse(localStorage.getItem('cart')) || [];
};

// Speichert den kompletten Warenkorb wieder im localStorage.
window.saveCart = function (cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
};

// ## Schluss Code von Linus

// ## Beginn Code von Nils
// Zeigt eine kurze Toast-Meldung an, zum Beispiel nach dem Hinzufuegen eines Produkts.
window.showToast = function (message) {
    const toast = document.getElementById('toast');

    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');

    window.clearTimeout(window.toastTimer);
    window.toastTimer = window.setTimeout(() => {
        toast.classList.remove('show');
    }, 2400);
};

// Ermittelt Zwischensumme, Rabatt und Gesamtbetrag fuer den aktuellen Warenkorb.
window.getCartTotals = function () {
    const cart = getCart();

    const subtotal = cart.reduce((sum, item) => {
        const price = parseFloat(item.price) || 0;
        const quantity = item.menge || 0;
        return sum + (price * quantity);
    }, 0);

    const vat = Math.round(subtotal * 0.19 * 100) / 100;
    const total = Math.round((subtotal + vat) * 100) / 100;

    return {
        subtotal,
        vat,
        total,
    };
};

// Escaped Texte sicher fuer HTML-Ausgabe in Tabellen und Drawer.
window.escapeHtml = function (value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => {
        switch (char) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#39;';
            default: return char;
        }
    });
};

// Rendert die komplette Warenkorb-Tabelle auf der cart.php.
window.renderCart = function () {
    const cart = getCart();
    const body = document.getElementById('cart-body');
    const emptyMsg = document.getElementById('empty-msg');
    const table = document.getElementById('cart-table');
    const nettoPrice = document.getElementById('netto-price');
    const mwstPrice = document.getElementById('mwst-price');
    const totalPrice = document.getElementById('total-price');
    const cartPayload = document.getElementById('cart-payload');
    const listCartPayload = document.getElementById('list-cart-payload');

    if (!body || !emptyMsg || !table) {
        updateCartBadge();
        return;
    }

    body.innerHTML = '';

    if (cart.length === 0) {
        emptyMsg.style.display = 'block';
        table.style.display = 'none';

        if (nettoPrice) nettoPrice.textContent = formatPrice(0);
        if (mwstPrice) mwstPrice.textContent = formatPrice(0);
        if (totalPrice) totalPrice.textContent = formatPrice(0);
        if (cartPayload) cartPayload.value = '[]';
        if (listCartPayload) listCartPayload.value = '[]';

        updateCartBadge();
        return;
    }

    emptyMsg.style.display = 'none';
    table.style.display = 'table';

    cart.forEach((item, index) => {
        const price = parseFloat(item.price) || 0;
        const quantity = Math.max(1, parseInt(item.menge, 10) || 1);
        const lineTotal = price * quantity;
        const image = item.image || '';
        const safeName = window.escapeHtml(item.name);
        const safeImage = window.escapeHtml(image);

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="cart-table-product">
                    ${image ? `<img src="${safeImage}" alt="${safeName}">` : ''}
                    <span>${safeName}</span>
                </div>
            </td>
            <td>${formatPrice(price)}</td>
            <td>
                <input
                    type="number"
                    min="1"
                    step="1"
                    value="${quantity}"
                    aria-label="Menge für ${safeName}"
                    onchange="setCartQty(${item.id}, this.value)"
                >
            </td>
            <td>${formatPrice(lineTotal)}</td>
            <td>
                <button type="button" class="btn-clear" onclick="removeCartItem(${item.id})">Entfernen</button>
            </td>
        `;

        body.appendChild(row);
    });

    const totals = window.getCartTotals();
    const subtotal = totals.subtotal || 0;
    const vat = totals.vat || 0;
    const finalTotal = totals.total || 0;

    if (nettoPrice) nettoPrice.textContent = formatPrice(subtotal);
    if (mwstPrice) mwstPrice.textContent = formatPrice(vat);
    if (totalPrice) totalPrice.textContent = formatPrice(finalTotal);
    if (cartPayload) cartPayload.value = JSON.stringify(cart);
    if (listCartPayload) listCartPayload.value = JSON.stringify(cart);

    updateCartBadge();
};
// ## Schluss Code von Nils
// ## Beginn KI genertierter Code (Claude)
// Aktualisiert die kleine Warenkorb-Badge an allen Icons im Layout.
window.updateCartBadge = function () {

    // Alle Warenkorb-Icons im DOM auswählen
    const cartIcons = document.querySelectorAll('.cart-icon');

    // Falls keine Icons existieren, abbrechen
    if (!cartIcons.length) return;

    // Warenkorb laden
    const cart = getCart();

    // Gesamtanzahl aller Produkte im Warenkorb berechnen
    const quantityTotal = cart.reduce((sum, item) => sum + (item.menge || 0), 0);

    // Badge auf allen Icons aktualisieren
    cartIcons.forEach((icon) => {
        let badge = icon.querySelector('.cart-badge');

        // Falls noch kein Badge existiert, erstellen
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.setAttribute('aria-hidden', 'true');
            icon.appendChild(badge);
        }

        // Anzahl anzeigen
        badge.textContent = quantityTotal;

        // Nur anzeigen, wenn Artikel vorhanden sind
        badge.style.display = quantityTotal > 0 ? 'inline-flex' : 'none';
    });
};


// Rendert die Drawer-Ansicht mit allen Warenkorbpositionen neu.
window.renderCartDrawer = function () {

    // DOM-Elemente holen
    const drawer = document.getElementById('cart-drawer');
    const items = document.getElementById('cart-drawer-items');
    const total = document.getElementById('cart-drawer-total');
    const count = document.getElementById('cart-drawer-count');

    // Abbrechen, wenn Elemente fehlen
    if (!drawer || !items || !total || !count) return;

    // Warenkorb laden
    const cart = getCart();

    // Alte Inhalte entfernen
    items.innerHTML = '';

    // Falls leerer Warenkorb
    if (cart.length === 0) {
        items.innerHTML = '<p class="cart-drawer-empty">Dein Warenkorb ist noch leer.</p>';
    } else {

        // Jede Position im Warenkorb darstellen
        cart.forEach((item) => {

            const price = parseFloat(item.price) || 0;

            // Produktbild bestimmen
            const image = item.image || '';

            // Gesamtpreis pro Position
            const lineTotal = price * item.menge;

            const entry = document.createElement('div');

            entry.className = 'cart-drawer-item';

            entry.innerHTML = `
                ${image ? `<img src="${image}" alt="${window.escapeHtml(item.name)}">` : '<div class="cart-drawer-placeholder"></div>'}
                <div class="cart-drawer-item-info">
                    <strong>${window.escapeHtml(item.name)}</strong>
                    <span>${item.menge} x ${formatPrice(price)}</span>
                </div>
                <div class="cart-drawer-item-actions">
                    <div class="cart-drawer-item-total">${formatPrice(lineTotal)}</div>
                    <label class="cart-drawer-qty" aria-label="Menge für ${item.name}">
                        <span class="cart-drawer-qty-label">Anzahl</span>
                        <input
                            type="number"
                            min="1"
                            step="1"
                            class="cart-drawer-qty-input"
                            value="${item.menge}"
                            onchange="setDrawerQty(${item.id}, this.value)"
                            aria-label="Menge für ${item.name}"
                        >
                    </label>
                    <button type="button" class="cart-drawer-remove" onclick="removeDrawerItem(${item.id})">Entfernen</button>
                </div>
            `;

            items.appendChild(entry);
        });
    }

    // Gesamtsummen berechnen
    const totals = getCartTotals();

    // Gesamtanzahl berechnen
    const quantityTotal = cart.reduce((sum, item) => sum + (item.menge || 0), 0);

    // UI aktualisieren
    total.textContent = formatPrice(totals.total);
    count.textContent = `${quantityTotal} Artikel`;

    // Badge aktualisieren
    updateCartBadge();
};


// Öffnet den Warenkorb-Drawer
window.openCartDrawer = function () {

    const drawer = document.getElementById('cart-drawer');

    if (!drawer) return;

    // Inhalt aktualisieren bevor geöffnet wird
    renderCartDrawer();

    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');

    document.body.classList.add('drawer-open');

    updateCartBadge();
};


// Schließt den Warenkorb-Drawer
window.closeCartDrawer = function () {

    const drawer = document.getElementById('cart-drawer');

    if (!drawer) return;

    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');

    document.body.classList.remove('drawer-open');

    updateCartBadge();
};


// Entfernt ein Element aus dem Drawer-Warenkorb
window.removeDrawerItem = function (id) {

    const cart = getCart().filter(item => item.id !== id);

    saveCart(cart);

    renderCart();
    renderCartDrawer();
    updateCartBadge();

    if (cart.length === 0) {
        closeCartDrawer();
    }
};

// Entfernt ein Produkt direkt aus der cart.php-Tabelle.
window.removeCartItem = function (id) {
    const cart = getCart().filter(item => item.id !== id);

    saveCart(cart);
    renderCart();
    renderCartDrawer();

    if (cart.length === 0) {
        closeCartDrawer();
    }
};

// Setzt die Menge eines Produkts in der cart.php-Tabelle auf einen festen Wert.
window.setCartQty = function (id, value) {
    const cart = getCart();
    const item = cart.find(entry => entry.id === id);

    if (!item) return;

    const quantity = Math.max(1, parseInt(value, 10) || 1);
    item.menge = quantity;

    saveCart(cart);
    renderCart();
    renderCartDrawer();
};

// Leert den kompletten Warenkorb.
window.clearCart = function () {
    localStorage.removeItem('cart');

    renderCart();
    renderCartDrawer();
    updateCartBadge();
};

// Sendet die aktuelle Warenkorbansicht an die Checkout-Seite.
window.checkout = function () {
    const cart = getCart();
    const form = document.getElementById('checkout-form');
    const payload = document.getElementById('cart-payload');

    if (!form || !payload) {
        return;
    }

    if (cart.length === 0) {
        showToast('Der Warenkorb ist leer.');
        return;
    }

    payload.value = JSON.stringify(cart);

    form.submit();
};

// Speichert den aktuellen Warenkorb als Sammelliste.
window.saveCartAsList = function () {
    const cart = getCart();
    const form = document.getElementById('save-list-form');
    const payload = document.getElementById('list-cart-payload');
    const listNameInput = document.getElementById('list-name');

    if (!form || !payload || !listNameInput) {
        return;
    }

    if (cart.length === 0) {
        showToast('Der Warenkorb ist leer.');
        return;
    }

    if (listNameInput.value.trim() === '') {
        showToast('Bitte einen Namen für die Sammelliste eingeben.');
        listNameInput.focus();
        return;
    }

    payload.value = JSON.stringify(cart);
    form.submit();
};


// Ändert Menge im Drawer (relativ +/-)
window.updateDrawerQty = function (id, delta) {

    const cart = getCart();

    const item = cart.find(entry => entry.id === id);

    if (!item) return;

    item.menge += delta;

    // Falls Menge 0 oder weniger wird -> entfernen
    if (item.menge <= 0) {

        const filteredCart = cart.filter(entry => entry.id !== id);

        saveCart(filteredCart);

        renderCart();
        renderCartDrawer();
        updateCartBadge();

        if (filteredCart.length === 0) {
            closeCartDrawer();
        }

        return;
    }

    saveCart(cart);

    renderCart();
    renderCartDrawer();
    updateCartBadge();
};


// Setzt eine feste Menge im Drawer
window.setDrawerQty = function (id, value) {

    const cart = getCart();

    const item = cart.find(entry => entry.id === id);

    if (!item) return;

    const quantity = Math.max(1, parseInt(value, 10) || 1);

    item.menge = quantity;

    saveCart(cart);

    renderCart();
    renderCartDrawer();
    updateCartBadge();
};


// Produkt zum Warenkorb hinzufügen
window.addToCart = function (id, name, price, image = "") {

    const cart = getCart();

    const parsedPrice = parseFloat(price);

    const existingItem = cart.find(item => item.id === id);

    // Falls schon vorhanden -> Menge erhöhen
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

    renderCartDrawer();
    openCartDrawer();

    showToast(`${name} wurde zum Warenkorb hinzugefügt!`);

    updateCartBadge();
};


// Preis umwandeln
window.parsePrice = function (price) {
    return parseFloat(price);
};


// Preis formatieren (DE Format)
window.formatPrice = function (num) {
    return Number(num).toFixed(2).replace('.', ',') + ' €';
};

// Initialisiert Warenkorb-UI und Schliessen-Buttons nach dem Laden der Seite.
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    renderCartDrawer();
    updateCartBadge();

    document.querySelectorAll('[data-cart-close]').forEach((button) => {
        button.addEventListener('click', closeCartDrawer);
    });
});
// ## Schluss KI genertierter Code (Claude)
