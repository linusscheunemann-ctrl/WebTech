<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <script src="JavaScript/shop.js" defer></script>
    <title>Warenkorb</title>
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
    <div class="cart-container"> 
        <h1>Warenkorb</h1>
        <p id="empty-msg">
            Ihr Warenkorb ist leer. Fügen Sie Produkte hinzu, um fortzufahren. </p>
        <table id="cart-table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Produkt</th>
                    <th>Einzelpreis</th>
                    <th>Anzahl</th>
                    <th>Gesamt</th>
                    <th></th>
                    
                </tr>
            </thead>
            <tbody id="cart-body"> </tbody>
                <!-- Dynamisch generierte Zeilen für Produkte im Warenkorb -->
                <tfoot>
                    <tr>
                        <td colspan="4">Nettobetrag</td>
                        <td id="netto-price" colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="4">zzgl. MwSt. (19%)</td>
                        <td id="mwst-price" colspan="2"></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4">Gesamt (inkl.MwSt.):</td>
                        <td id="total-price" colspan="2">0,00 €</td>
                    </tr>
                </tfoot>
        </table>
        <div class="cart-actions">
            <button type="button" class="btn-back" onclick="location.href='shop.php'">Weiter einkaufen</button>
            <button class="btn-clear" onclick="clearCart()">Warenkorb leeren</button>
            <button class="btn-checkout" onclick="checkout()">Zur Kasse</button>

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