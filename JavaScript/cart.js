"use strict";

console.log("CART.JS GELADEN");
window.getCouponCatalog = function () {
    return window.COUPON_CATALOG || {};
};

window.getCart = function () {
    return JSON.parse(localStorage.getItem('cart')) || [];
};

window.saveCart = function (cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
};

window.normalizeCouponCode = function (code) {
    return String(code || '').trim().toUpperCase();
};

window.getCouponCode = function () {
    return window.normalizeCouponCode(localStorage.getItem('cart_coupon_code') || '');
};

window.saveCouponCode = function (code) {
    const normalizedCode = window.normalizeCouponCode(code);

    if (normalizedCode === '') {
        localStorage.removeItem('cart_coupon_code');
        return '';
    }

    localStorage.setItem('cart_coupon_code', normalizedCode);
    return normalizedCode;
};

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

window.applyCouponCode = function () {
    const couponInput = document.getElementById('discount-code');
    const couponMessage = document.getElementById('coupon-message');

    if (!couponInput) {
        return;
    }

    const code = window.normalizeCouponCode(couponInput.value);
    const subtotal = window.getCartTotals().subtotal;
    const discount = window.getCouponDiscount(subtotal, code);

    if (!code) {
        window.clearCouponCode();
        if (couponMessage) {
            couponMessage.textContent = 'Bitte einen Rabattcode eingeben.';
            couponMessage.className = 'cart-coupon-message is-invalid';
        }
        return;
    }

    if (!discount.valid || discount.amount <= 0) {
        localStorage.removeItem('cart_coupon_code');
        renderCart();
        renderCartDrawer();
        if (couponMessage) {
            couponMessage.textContent = 'Dieser Rabattcode ist ungültig.';
            couponMessage.className = 'cart-coupon-message is-invalid';
        }
        return;
    }

    window.saveCouponCode(code);

    if (couponMessage) {
        couponMessage.textContent = `${discount.label} aktiviert: -${formatPrice(discount.amount)} (${discount.percent}%)`;
        couponMessage.className = 'cart-coupon-message is-valid';
    }

    renderCart();
    renderCartDrawer();
};

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

window.updateCartBadge = function () {
    const cartIcons = document.querySelectorAll('.cart-icon');

    if (!cartIcons.length) return;

    const cart = getCart();
    const quantityTotal = cart.reduce((sum, item) => sum + (item.menge || 0), 0);

    cartIcons.forEach((icon) => {
        let badge = icon.querySelector('.cart-badge');

        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.setAttribute('aria-hidden', 'true');
            icon.appendChild(badge);
        }

        badge.textContent = quantityTotal;
        badge.style.display = quantityTotal > 0 ? 'inline-flex' : 'none';
    });
};

window.renderCartDrawer = function () {
    const drawer = document.getElementById('cart-drawer');
    const items = document.getElementById('cart-drawer-items');
    const total = document.getElementById('cart-drawer-total');
    const count = document.getElementById('cart-drawer-count');

    if (!drawer || !items || !total || !count) return;

    const cart = getCart();

    items.innerHTML = '';

    if (cart.length === 0) {
        items.innerHTML = '<p class="cart-drawer-empty">Dein Warenkorb ist noch leer.</p>';
    } else {
        cart.forEach((item) => {
            const price = parseFloat(item.price) || 0;
            const image = item.image || getProductImage(item.name) || '';
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

    const totals = getCartTotals();
    const quantityTotal = cart.reduce((sum, item) => sum + (item.menge || 0), 0);

    total.textContent = formatPrice(totals.total);
    count.textContent = `${quantityTotal} Artikel`;
    updateCartBadge();
};

window.openCartDrawer = function () {
    const drawer = document.getElementById('cart-drawer');

    if (!drawer) return;

    renderCartDrawer();
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('drawer-open');
    updateCartBadge();
};

window.closeCartDrawer = function () {
    const drawer = document.getElementById('cart-drawer');

    if (!drawer) return;

    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('drawer-open');
    updateCartBadge();
};

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

window.updateDrawerQty = function (id, delta) {
    const cart = getCart();
    const item = cart.find(entry => entry.id === id);

    if (!item) return;

    item.menge += delta;

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


// SHOP INTEGRATION
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
    renderCartDrawer();
    openCartDrawer();
    showToast(`${name} wurde zum Warenkorb hinzugefügt!`);
    updateCartBadge();
};

// IMAGE FALLBACK
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

// PRICE HELPERS
window.parsePrice = function (price) {
    return parseFloat(price);
};

window.formatPrice = function (num) {
    return Number(num).toFixed(2).replace('.', ',') + ' €';
};

// CART RENDER
window.renderCart = function () {
    const cart = getCart();

    const tbody = document.getElementById('cart-body');
    const emptyMsg = document.getElementById('empty-msg');
    const cartTable = document.getElementById('cart-table');
    const couponSummary = document.getElementById('coupon-summary');
    const couponMessage = document.getElementById('coupon-message');
    const couponInput = document.getElementById('discount-code');

    if (!tbody || !cartTable || !emptyMsg) return;

    tbody.innerHTML = '';

    let total = 0;
    let updated = false;

    if (cart.length === 0) {
        cartTable.style.display = 'none';
        emptyMsg.style.display = 'block';
    } else {
        cartTable.style.display = 'table';
        emptyMsg.style.display = 'none';

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
                    <input
                        type="number"
                        min="1"
                        step="1"
                        class="cart-qty-input"
                        value="${item.menge}"
                        onchange="setCartQty(${index}, this.value)"
                        aria-label="Anzahl für ${item.name}"
                    >
                </td>

                <td>${formatPrice(sum)}</td>

                <td>
                    <button onclick="removeItem(${index})">Entfernen</button>
                </td>
            `;

            tbody.appendChild(row);
        });
    }

    if (updated) saveCart(cart);

    const totals = getCartTotals();
    const netto = totals.subtotal / 1.19;
    const mwst = totals.subtotal - netto;

    if (couponInput) {
        const savedCode = getCouponCode();
        couponInput.value = savedCode;
    }

    if (couponSummary) {
        if (totals.coupon.valid && totals.coupon.amount > 0) {
            couponSummary.textContent = `${totals.coupon.code} (-${formatPrice(totals.coupon.amount)})`;
        } else {
            couponSummary.textContent = '-';
        }
    }

    if (couponMessage) {
        if (totals.coupon.valid && totals.coupon.amount > 0) {
            couponMessage.textContent = `${totals.coupon.label} aktiv: -${formatPrice(totals.coupon.amount)} (${totals.coupon.percent}%)`;
            couponMessage.className = 'cart-coupon-message is-valid';
        } else if (cart.length > 0 && getCouponCode() !== '') {
            couponMessage.textContent = 'Gespeicherter Rabattcode ist ungültig.';
            couponMessage.className = 'cart-coupon-message is-invalid';
        } else {
            couponMessage.textContent = '';
            couponMessage.className = 'cart-coupon-message';
        }
    }

    document.getElementById('total-price').textContent = formatPrice(totals.total);
    document.getElementById('netto-price').textContent = formatPrice(netto);
    document.getElementById('mwst-price').textContent = formatPrice(mwst);
};

// ACTIONS
window.changeQty = function (index, delta) {
    const cart = getCart();

    cart[index].menge += delta;

    if (cart[index].menge <= 0) {
        cart.splice(index, 1);
    }

    saveCart(cart);
    renderCart();
    renderCartDrawer();
};

window.setCartQty = function (index, value) {
    const cart = getCart();
    const quantity = Math.max(1, parseInt(value, 10) || 1);

    if (!cart[index]) {
        return;
    }

    cart[index].menge = quantity;

    saveCart(cart);
    renderCart();
    renderCartDrawer();
};

window.removeItem = function (index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    renderCart();
    renderCartDrawer();
};

window.clearCart = function () {
    if (confirm('Warenkorb wirklich leeren?')) {
        localStorage.removeItem('cart');
        renderCart();
        renderCartDrawer();
    }
};

window.checkout = function () {
    const cart = getCart();
    const form = document.getElementById('checkout-form');
    const payloadField = document.getElementById('cart-payload');
    const discountField = document.getElementById('discount-code-payload');
    const couponInput = document.getElementById('discount-code');

    if (!form || !payloadField) {
        window.location.href = 'login.php?return_to=cart.php';
        return;
    }

    if (cart.length === 0) {
        alert('Dein Warenkorb ist leer.');
        return;
    }

    payloadField.value = JSON.stringify(cart);
    if (discountField) {
        const code = couponInput ? window.normalizeCouponCode(couponInput.value) : getCouponCode();
        discountField.value = code;
        if (code) {
            window.saveCouponCode(code);
        }
    }
    form.submit();
};

window.saveCartAsList = function () {
    const cart = getCart();
    const form = document.getElementById('save-list-form');
    const payloadField = document.getElementById('list-cart-payload');
    const nameField = document.getElementById('list-name');

    if (!form || !payloadField || !nameField) {
        return;
    }

    if (cart.length === 0) {
        alert('Dein Warenkorb ist leer.');
        return;
    }

    if (nameField.value.trim() === '') {
        alert('Bitte einen Namen für die Sammelliste eingeben.');
        nameField.focus();
        return;
    }

    payloadField.value = JSON.stringify(cart);
    form.submit();
};

// INIT
document.addEventListener("DOMContentLoaded", () => {
    renderCart();
    renderCartDrawer();
    updateCartBadge();

    const couponInput = document.getElementById('discount-code');
    if (couponInput) {
        couponInput.value = getCouponCode();
        couponInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyCouponCode();
            }
        });
    }

    const url = new URL(window.location.href);
    const bookingStatus = url.searchParams.get('booking');

    if (bookingStatus === 'success') {
        localStorage.removeItem('cart');
        localStorage.removeItem('cart_coupon_code');
        renderCart();
        renderCartDrawer();
        updateCartBadge();
        closeCartDrawer();
    }

    const drawer = document.getElementById('cart-drawer');
    const cartIcons = document.querySelectorAll('.cart-icon');

    if (drawer) {
        drawer.addEventListener('click', (event) => {
            if (event.target.matches('[data-cart-close]') || event.target.classList.contains('cart-drawer-backdrop')) {
                closeCartDrawer();
            }
        });

        cartIcons.forEach((icon) => {
            icon.addEventListener('click', (event) => {
                event.preventDefault();
                openCartDrawer();
            });
        });

        updateCartBadge();
    }
});
