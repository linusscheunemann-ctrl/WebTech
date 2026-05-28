<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">

    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <script src="JavaScript/shop.js" defer></script>

    <title>Shop</title>
</head>

<body>

<!-- Navigation Anfang -->
<a href="index.php">
    <img src="images/logo.png" alt="Autohaus" class="logo">
</a>

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
        <a href="cart.php" class="cart-icon">
            <img src="images/cart.webp" class="cart-img">
        </a>

        <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>

        <a href="login.php">
            <img src="images/login.png" alt="Login" class="login-icon">
        </a>
    </div>
</nav>
<!-- Navigation Ende -->


<!-- SHOP CONTAINER -->
<div class="wrapper">
    <div class="shop">

        <!-- Produkte werden hier per shop.js + product.json geladen -->

    </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<!-- CART DRAWER -->
<div id="cart-drawer" class="cart-drawer" aria-hidden="true">
    <div class="cart-drawer-backdrop" data-cart-close></div>
    <aside class="cart-drawer-panel" aria-label="Aktueller Warenkorb">
        <div class="cart-drawer-header">
            <div>
                <p class="cart-drawer-eyebrow">Schnellansicht</p>
                <h2>Dein Warenkorb</h2>
            </div>
            <button type="button" class="cart-drawer-close" data-cart-close aria-label="Warenkorb schließen">×</button>
        </div>

        <div class="cart-drawer-meta">
            <span id="cart-drawer-count">0 Artikel</span>
            <a class="cart-drawer-link" href="cart.php">Zur Warenkorbseite</a>
        </div>

        <div id="cart-drawer-items" class="cart-drawer-items"></div>

        <div class="cart-drawer-footer">
            <div>
                <span>Gesamtsumme</span>
                <strong id="cart-drawer-total">0,00 €</strong>
            </div>
            <div class="cart-drawer-actions">
                <button type="button" class="btn-back" onclick="closeCartDrawer()">Weiter shoppen</button>
                <button type="button" class="btn-checkout" onclick="location.href='cart.php'">Checkout</button>
            </div>
        </div>
    </aside>
</div>

<!-- FOOTER -->
<footer id="footer-wrapper">

    <div id="footersocial">
        <ul>
            <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
            <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
        </ul>
    </div>

    <div id="claim-footer">
        <span id="head-footer">Kontakt</span>
        <p>Auto Union Straße 1</p>
        <p>85053 Ingolstadt</p>
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
