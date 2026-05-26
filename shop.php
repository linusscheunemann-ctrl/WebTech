<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/shop.js" defer></script>
    
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
        <a href="about.php" class="nav-button">ABOUT</a>
    </div>
    <div class="nav-right">
        <a href="cart.php" class="cart-icon"> <img src="images/cart.webp" class="cart-img"></a>
        <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>
        <a href="login.php"><img src="images/login.png" alt="Login" class="login-icon"></a>
    </div>
</nav>
    <!-- Navigation Ende -->


<div class="wrapper">
<div class="shop">

<div class="product">
        <img src="images/products/Museumsgtuschein.png" alt="Museumsgutschein">
        <h3 class="product-name">Museumsgutschein</h3>
        <div class="price">100,00 €</div>
        <button class="buy-btn" data-name="Museumsgutschein 100 €" data-price="100.00" data-image="images/products/Museumsgtuschein.png">Kaufen</button>
    </div>

    <div class="product">
        <img src="images/products/Museumsgtuschein.png" alt="Museumsgutschein">
        <h3 class="product-name">Museumsgutschein</h3>
        <div class="price">50,00 €</div>
        <button class="buy-btn" data-name="Museumsgutschein 50 €" data-price="50.00" data-image="images/products/Museumsgtuschein.png">Kaufen</button>
    </div>

    <div class="product">
        <img src="images/products/Museumsgtuschein.png" alt="Museumsgutschein">
        <h3 class="product-name">Museumsgutschein</h3>
        <div class="price">20,00 €</div>
        <button class="buy-btn" data-name="Museumsgutschein 20 €" data-price="20.00" data-image="images/products/Museumsgtuschein.png">Kaufen</button>
    </div>

<div class="product">
        <img src="images/products/Museumsgtuschein.png" alt="">
        <h3 class="product-name">Museumgutschein</h3>
        <div class="price">10,00 €</div>
        <button class="buy-btn" data-name="Museumgutschein 10 €" data-price="10.00" data-image="">Kaufen</button>
    </div>

    
    <div class="product">
        <img src="images/products/Führung durch das Museum.png" alt="Führung durch das Museum">
        <h3 class="product-name">Museumsführung</h3>
        <div class="price">29,99 €</div>
        <button class="buy-btn" data-name="Museumsführung" data-price="29.99" data-image="images/products/Führung durch das Museum.png">Kaufen</button>
    </div>

        
    
    <!---

        <div class="product">
        <img src="" alt="">
        <h3 class="product-name">Produkt 1</h3>
        <div class="price">19,99 €</div>
        <button class="buy-btn" data-name="Produkt 1" data-price="19.99" data-image="">Kaufen</button>

    </div>

    <div class="product">
        <img src="" alt="">
        <h3 class="product-name">Produkt 2</h3>
        <div class="price">29,99 €</div>
        <button class="buy-btn" data-name="Produkt 2" data-price="29.99" data-image="">Kaufen</button>
    </div>

    <div class="product">
        <img src="" alt="">
        <h3 class="product-name">Produkt 6</h3>
        <div class="price">15,49 €</div>
        <button class="buy-btn" data-name="Produkt 6" data-price="15.49" data-image="">Kaufen</button>
    </div>
--->




    
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

</body>
</html>