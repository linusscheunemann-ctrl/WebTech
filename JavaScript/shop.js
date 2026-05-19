function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || []; //Warenkorb wird ausgelesen
    }
    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart)); //Warenkorb wird gespeichert
    }
    function updateCartBagde(){
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.menge, 0);
        const badge = document.getElementById('cart-count');

        if (!badge) return; // Safeguard: some pages may not include the badge

        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';   
        }
    }
    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
    function getProductImage(name) {
        const images = {
            'Museumsgutschein': 'images/products/Museumsgtuschein.png',
            'Museumsführung': 'images/products/Führung durch das Museum.png',
            'Führung durch das Museum': 'images/products/Führung durch das Museum.png',
        };
        if (images[name]) {
            return images[name];
        }
        const normalized = name.toLowerCase();
        if (normalized.includes('museumsgutschein')) {
            return images['Museumsgutschein'];
        }
        if (normalized.includes('führung') || normalized.includes('führung durch das museum') || normalized.includes('museum')) {
            return images['Museumsführung'];
        }
        return '';
    }
    function addToCart(name, price, image) {
        const cart = getCart();
        const existingItem = cart.find(item => item.name === name);
        const imagePath = image || getProductImage(name) || '';

        if (existingItem) {
            existingItem.menge += 1;
            if (!existingItem.image && imagePath) {
                existingItem.image = imagePath;
            }
        } else {
            cart.push({name, price, image: imagePath, menge: 1});
        }
        saveCart(cart);
        updateCartBagde();
        showToast(`${name} wurde zum Warenkorb hinzugefügt!`);
    }
    //Event Listener für alle Kauf-Buttons
    document.querySelectorAll('.buy-btn').forEach(function(button){
        button.addEventListener('click', function(){
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const image = this.getAttribute('data-image') || '';
            addToCart(name, price, image);
        });
    });

    updateCartBagde(); //Warenkorb Badge wird beim Laden der Seite aktualisiert