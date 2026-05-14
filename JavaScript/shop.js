
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
    function addToCart(name, price) {
        const cart = getCart();
        const existingItem = cart.find(item => item.name === name);

        if (existingItem) {
            existingItem.menge += 1;
        } else {
            cart.push({name, price, menge: 1});
        }
        saveCart(cart);
        updateCartBagde();
        showToast(`${name} wurde zum Warenkorb hinzugefügt!`);
    }
    //Event Listener für alle Kauf-Buttons
    document.querySelectorAll('.buy-btn').forEach(function(button){
        button.addEventListener('click', function(){
            const name = this.getAttribute('data-name');
            const price =this.getAttribute('data-price');
            addToCart(name, price);
        });
    });

    updateCartBagde(); //Warenkorb Badge wird beim Laden der Seite aktualisiert