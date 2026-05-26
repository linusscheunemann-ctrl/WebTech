<?php
// SNIPPET: Parameter-Check
if (!isset($_GET["pid"])) {
    die("Parameter is missing!");
}

if (empty($_GET["pid"])) {
    die("No value for the parameter!");
}

$pid = (int) $_GET["pid"];

// optional zweites Item
$pid2 = null;
if (isset($_GET["id2"]) && !empty($_GET["id2"])) {
    $pid2 = (int) $_GET["id2"];
}

// JSON laden
$json = file_get_contents("product.json");

if (!$json) {
    die("JSON file not found!");
}

$data = json_decode($json, true);

if (!$data || !isset($data["products"])) {
    die("Invalid JSON structure!");
}

// Produkt-Finder
function findProduct($data, $id) {
    foreach ($data["products"] as $item) {
        if ((int)$item["id"] === $id) {
            return $item;
        }
    }
    return null;
}

// Produkt 1
$product1 = findProduct($data, $pid);

if (!$product1) {
    die("Product not found for ID: " . $pid);
}


// Produkt 2 
$product2 = null;
if ($pid2 !== null) {
    $product2 = findProduct($data, $pid2);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/cart.js" defer></script>
    <title><?php echo htmlspecialchars($product1["name"]); ?></title>
</head>

<body>

<!-- LOGO -->
<a href="index.php">
    <img src="images/logo.png" class="logo" alt="Logo">
</a>

<!-- NAV -->
<nav>
    <div class="nav-center">
        <div class="dropdown">
            <a href="bestand.php" class="nav-button">CARS & BIKES</a>
            <div class="dropdown-content">
                <a href="autos.php">Autos</a>
                <a href="motorraeder.php">Motorräder</a>
            </div>
        </div>

        <a href="shop.php" class="nav-button">SHOP</a>
        <a href="about.php" class="nav-button">ABOUT</a>
    </div>

    <div class="nav-right">
        <a href="cart.php">
            <img src="images/cart.webp" class="cart-img" alt="Cart">
        </a>

        <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>

        <a href="login.php">
            <img src="images/login.png" class="login-icon" alt="Login">
        </a>
    </div>
</nav>

<!-- WRAPPER -->
<div class="wrapper">

    <!-- PRODUKT 1 -->
    <div class="product-detail">
        <h1><?php echo htmlspecialchars($product1["name"]); ?></h1>

        <img src="<?php echo htmlspecialchars($product1["image"]); ?>" class="product-detail-image" alt="Produkt">

        <p><?php echo htmlspecialchars($product1["description"]); ?></p>

        <h3>
            Preis: <?php echo number_format($product1["price"], 2, ',', '.'); ?> €
        </h3>

        <button class="buy-btn"
            onclick="addToCart(
                '<?php echo addslashes($product1["name"]); ?>',
                '<?php echo $product1["price"]; ?>',
                '<?php echo $product1["image"]; ?>'
            )">
            Kaufen
        </button>

        <p>Kategorie: <?php echo htmlspecialchars($product1["category"]); ?></p>
        <p>Unterkategorie: <?php echo htmlspecialchars($product1["subcategory"]); ?></p>
    </div>

    <!-- PRODUKT 2 -->
    <?php if ($product2): ?>
    <div class="product-detail">
        <h1><?php echo htmlspecialchars($product2["name"]); ?></h1>

        <img src="<?php echo htmlspecialchars($product2["image"]); ?>" class="product-detail-image" alt="Produkt">

        <p><?php echo htmlspecialchars($product2["description"]); ?></p>

        <h3>
            Preis: <?php echo number_format($product2["price"], 2, ',', '.'); ?> €
        </h3>

        <button class="buy-btn"
            onclick="addToCart(
                '<?php echo addslashes($product2["name"]); ?>',
                '<?php echo $product2["price"]; ?>',
                '<?php echo $product2["image"]; ?>'
            )">
            Kaufen
        </button>

        <p>Kategorie: <?php echo htmlspecialchars($product2["category"]); ?></p>
        <p>Unterkategorie: <?php echo htmlspecialchars($product2["subcategory"]); ?></p>
    </div>
    <?php endif; ?>

</div>

<!-- FOOTER -->
<footer id="footer-wrapper">

    <div id="footersocial">
        <ul>
            <li><a href="#"><img src="images/footer-facebook.png" alt=""></a></li>
            <li><a href="#"><img src="images/footer-email.png" alt=""></a></li>
            <li><a href="#"><img src="images/footer-telefon.png" alt=""></a></li>
            <li><a href="#"><img src="images/footer-anfahrt.png" alt=""></a></li>
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