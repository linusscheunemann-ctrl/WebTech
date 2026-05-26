<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="toggle-theme.js"></script>

    
    <title>Shop</title>
</head>
<body>
    <!-- Navigation Anfang -->
   <a href="index.php"> <img src="images/logo.png" alt="Autohaus" class="logo"></a>
<nav>
    <div class="nav-center">
        <div class="dropdown">
            <a href="bestand.php" class="nav-button" id="dropbtn">CARS & BIKES</a>

            <div class="dropdown-content">
                <a href="autos.php">Autos</a>
                <a href="motorraeder.php">Motorräder</a>
            </div>
        </div>
        <a href="shop.php" class="nav-button">SHOP</a>
        </div>  
            
        </div>
        <a href="about.php" class="nav-button">ABOUT</a>
        </div>
    <div class="nav-right">
        <a href="cart.php" class="cart-icon"> <img src="images/cart.png" class="cart-img"></a>
        <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>
        <a href="login.php"><img src="images/login.png" alt="Login" class="login-icon"></a>
    </div>
</nav>
    <!-- Navigation Ende -->


<div class="wrapper">
<div class="shop">
    <div class="product">
        <img src="https://via.placeholder.com/200" alt="Museumsgutschein">
        <h3 class="product-name">Museumsgutschein</h3>
        <div class="price">19,99 €</div>
        <button class="buy-btn" data-name="Museumsgutschein" data-price="19.99">Kaufen</button>

    </div>

    <div class="product">
        <img src="https://via.placeholder.com/200" alt="Führung durch das Museum">
        <h3 class="product-name">Führung durch das Museum</h3>
        <div class="price">29,99 €</div>
        <button class="buy-btn" data-name="Führung durch das Museum" data-price="29.99">Kaufen</button>
    </div>

    <div class="product">
        <img src="https://via.placeholder.com/200" alt="Produkt 3">
        <h3 class="product-name">Produkt 3</h3>
        <div class="price">15,49 €</div>
        <button class="buy-btn" data-name="Produkt 3" data-price="15.49">Kaufen</button>
    </div>

        <div class="product">
        <img src="https://via.placeholder.com/200" alt="Produkt 1">
        <h3 class="product-name">Produkt 1</h3>
        <div class="price">19,99 €</div>
        <button class="buy-btn" data-name="Produkt 1" data-price="19.99">Kaufen</button>

    </div>

    <div class="product">
        <img src="https://via.placeholder.com/200" alt="Produkt 2">
        <h3 class="product-name">Produkt 2</h3>
        <div class="price">29,99 €</div>
        <button class="buy-btn" data-name="Produkt 2" data-price="29.99">Kaufen</button>
    </div>

    <div class="product">
        <img src="https://via.placeholder.com/200" alt="Produkt 6">
        <h3 class="product-name">Produkt 6</h3>
        <div class="price">15,49 €</div>
        <button class="buy-btn" data-name="Produkt 6" data-price="15.49">Kaufen</button>
    </div>
    <!---Toast meldung--->
    <div id="toast"></div>


</div>
</div>
<footer id="footer-wrapper">
    <div id="footersocial">
      <ul>
        <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
        <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
      </ul>
    </div>

    <div id="claim-footer">
     <span id="head-footer">Kontakt</span>
        <p>Auto Union Straße 1 </p>
        <p> 85053 Ingolstadt  </p>
        <p>Email: info@deinautohaus.de</p>
        <p>Telefon: 01234-567890</p>
        <p>Öffnungszeiten: Mo-Fr 9-18 Uhr, Sa 10-14 Uhr</p>
    </div>

    <div id="copyright">
      <p>© 2026 Dein Autohaus. Alle Rechte vorbehalten.</p>
    </div>
</footer>
<!--- Warenkorb Funktion (Nils)--->
<script>
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

</script>


</body>
</html>