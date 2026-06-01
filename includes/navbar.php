<header class="site-header">
    <a href="index.php" class="nav-brand" aria-label="Zur Startseite">
        <img src="images/logo.png" alt="Autohaus" class="logo">
    </a>

    <button
        type="button"
        class="nav-toggle"
        aria-controls="site-navigation"
        aria-expanded="false"
        aria-label="Menü öffnen"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="site-nav" id="site-navigation" aria-label="Hauptnavigation">
        <div class="site-nav__primary">
            <details class="nav-dropdown">
                <summary class="nav-link nav-link--dropdown">CARS & BIKES</summary>
                <div class="nav-dropdown-menu">
                    <a href="bestand.php">Alle Fahrzeuge</a>
                    <a href="autos.php">Autos</a>
                    <a href="motorraeder.php">Motorräder</a>
                </div>
            </details>

            <a href="shop.php" class="nav-link">SHOP</a>
            <a href="about.php" class="nav-link">ABOUT</a>
        </div>

        <div class="site-nav__actions">
            <a href="cart.php" class="cart-icon nav-icon" aria-label="Warenkorb">
                <img src="images/cart.webp" class="cart-img" alt="">
            </a>

            <button onclick="myFunction()" id="theme-toggle" class="theme-toggle" type="button" aria-label="Theme wechseln">🌕</button>

            <a href="login.php" class="nav-icon" aria-label="Login">
                <img src="images/login.png" alt="Login" class="login-icon">
            </a>
        </div>
    </nav>
</header>

<script src="JavaScript/navbar.js"></script>
