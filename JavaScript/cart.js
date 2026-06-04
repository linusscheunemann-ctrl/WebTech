"use strict";
// ## Beginn Code von Linus
// Dieser Warenkorb-Helper stellt mehrere globale Funktionen bereit, weil sie
// von unterschiedlichen Seiten, Formularen und Inline-Handlern gemeinsam genutzt werden.
console.log("CART.JS GELADEN");

// Liefert den zentralen Katalog aller gueltigen Rabattcodes.
window.getCouponCatalog = function () {
    return window.COUPON_CATALOG || {};
};

// Liest den aktuell im Browser gespeicherten Warenkorb aus dem localStorage.
window.getCart = function () {
    return JSON.parse(localStorage.getItem('cart')) || [];
};

// Speichert den kompletten Warenkorb wieder im localStorage.
window.saveCart = function (cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
};

// Normalisiert Rabattcodes, damit Gross-/Kleinschreibung und Leerzeichen keine Rolle spielen.
window.normalizeCouponCode = function (code) {
    return String(code || '').trim().toUpperCase();
};

// Gibt den aktuell gespeicherten Rabattcode in normalisierter Form zurueck.
window.getCouponCode = function () {
    return window.normalizeCouponCode(localStorage.getItem('cart_coupon_code') || '');
};

// Speichert einen Rabattcode oder loescht ihn, falls die Eingabe leer war.
window.saveCouponCode = function (code) {
    const normalizedCode = window.normalizeCouponCode(code);

    if (normalizedCode === '') {
        localStorage.removeItem('cart_coupon_code');
        return '';
    }

    localStorage.setItem('cart_coupon_code', normalizedCode);
    return normalizedCode;
};

// Entfernt den Rabattcode aus dem Speicher und aktualisiert alle sichtbaren Warenkorb-Bereiche.
window.clearCouponCode = function () {
    localStorage.removeItem('cart_coupon_code');
    renderCart();
    renderCartDrawer();

    const couponInput = document.getElementById('discount-code');
    const couponMessage = document.getElementById('coupon-message');

    if (couponInput) {
        couponInput.value = '';
    }

    if (couponMessage) {
        couponMessage.textContent = 'Rabattcode wurde entfernt.';
        couponMessage.className = 'cart-coupon-message is-valid';
    }
};
// Berechnet Rabattbetrag und Endsumme fuer eine gegebene Zwischensumme.
window.getCouponDiscount = function (subtotal, code) {
    const normalizedCode = window.normalizeCouponCode(code);
    const coupon = window.getCouponCatalog()[normalizedCode];

    if (!coupon) {
        return {
            code: '',
            label: null,
            percent: 0,
            amount: 0,
            final_total: subtotal,
            valid: false,
        };
    }

    const percent = Math.max(0, Math.min(100, Number(coupon.percent) || 0));
    const amount = Math.round(subtotal * (percent / 100) * 100) / 100;

    return {
        code: normalizedCode,
        label: coupon.label || normalizedCode,
        percent,
        amount,
        final_total: Math.max(0, Math.round((subtotal - amount) * 100) / 100),
        valid: true,
    };
};

// Prüft den eingegebenen Rabattcode und gibt dem Nutzer
// eine passende Rückmeldung aus.
window.applyCouponCode = function () {

    // Eingabefeld für den Rabattcode abrufen
    const couponInput = document.getElementById('discount-code');

    // Element für Status- und Fehlermeldungen abrufen
    const couponMessage = document.getElementById('coupon-message');

    // Falls das Eingabefeld nicht existiert, Funktion beenden
    if (!couponInput) {
        return;
    }

    // Rabattcode bereinigen und normalisieren
    const code = window.normalizeCouponCode(couponInput.value);

    // Aktuellen Warenkorbwert ohne Rabatt ermitteln
    const subtotal = window.getCartTotals().subtotal;

    // Rabatt anhand des Codes berechnen
    const discount = window.getCouponDiscount(subtotal, code);

    // Prüfen, ob überhaupt ein Rabattcode eingegeben wurde
    if (!code) {

        // Gespeicherten Rabattcode entfernen
        window.clearCouponCode();

        // Fehlermeldung anzeigen
        if (couponMessage) {
            couponMessage.textContent = 'Bitte einen Rabattcode eingeben.';
            couponMessage.className = 'cart-coupon-message is-invalid';
        }

        return;
    }

    // Prüfen, ob der Rabattcode ungültig ist
    // oder keinen Rabatt erzeugt
    if (!discount.valid || discount.amount <= 0) {

        // Rabattcode aus dem Local Storage entfernen
        localStorage.removeItem('cart_coupon_code');

        // Warenkorb und Warenkorb-Drawer aktualisieren
        renderCart();
        renderCartDrawer();

        // Fehlermeldung anzeigen
        if (couponMessage) {
            couponMessage.textContent = 'Dieser Rabattcode ist ungültig.';
            couponMessage.className = 'cart-coupon-message is-invalid';
        }

        return;
    }

    // Gültigen Rabattcode speichern
    window.saveCouponCode(code);

    // Erfolgsnachricht mit Rabattinformationen anzeigen
    if (couponMessage) {
        couponMessage.textContent =
            `${discount.label} aktiviert: -${formatPrice(discount.amount)} (${discount.percent}%)`;

        couponMessage.className = 'cart-coupon-message is-valid';
    }

    // Warenkorbansicht aktualisieren,
    // damit der Rabatt direkt sichtbar wird
    renderCart();
    renderCartDrawer();
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

    const couponDiscount = window.getCouponDiscount(subtotal, window.getCouponCode());
    const total = Math.max(0, Math.round((subtotal - couponDiscount.amount) * 100) / 100);

    return {
        subtotal,
        coupon: couponDiscount,
        total,
    };
};
// ## Schluss Code von Nils
// ## Beginn KI genertierter Code
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
            const image = item.image || getProductImage(item.name) || '';

            // Gesamtpreis pro Position
            const lineTotal = price * item.menge;

            const entry = document.createElement('div');

            entry.className = 'cart-drawer-item';

            entry.innerHTML = `
                ${image ? `<img src="${image}" alt="${item.name}">` : '<div class="cart-drawer-placeholder"></div>'}
                <div class="cart-drawer-item-info">
                    <strong>${item.name}</strong>
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


// Produktbild Fallback-Logik
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


// Preis umwandeln
window.parsePrice = function (price) {
    return parseFloat(price);
};


// Preis formatieren (DE Format)
window.formatPrice = function (num) {
    return Number(num).toFixed(2).replace('.', ',') + ' €';
};