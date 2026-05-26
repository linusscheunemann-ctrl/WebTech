<?php
if (!isset($_GET["pid"])) {
    die("Parameter is missing!");
}

if (empty($_GET["pid"])) {
    die("No value for the parameter!");
}

$pid = (int) $_GET["pid"];

// JSON laden (Pfad wichtig!)
$json = file_get_contents("product.json");
if (!$json) {
    die("JSON file not found!");
}

$data = json_decode($json, true);

if (!$data || !isset($data["products"])) {
    die("Invalid JSON structure!");
}

$product = null;

foreach ($data["products"] as $item) {
    if ((int)$item["id"] === $pid) {
        $product = $item;
        break;
    }
}

if (!$product) {
    die("Product not found for ID: " . $pid);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/cart.js" defer></script>
    <title><?php echo $product["name"]; ?></title>
</head>
<body>
    
      <a href="index.php" class="logo-link"><img src="images/logo.png" alt="Autohaus" class="logo"></a>
      
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
<br>
<h1><?php echo $product["name"]; ?></h1>
<div class="product-image-wrapper">
    <img class="product-detail-image" src="<?php echo $product["image"]; ?>">
</div>
<p><?php echo $product["description"]; ?></p>

<h3>
    Preis:
    <?php echo number_format($product["price"], 2, ',', '.'); ?> €
</h3>

<button
    class="buy-btn"
    onclick="addToCart(
        '<?php echo addslashes($product["name"]); ?>',
        '<?php echo $product["price"]; ?>',
        '<?php echo $product["image"]; ?>'
    )"
>
    Kaufen
</button>



<p>Kategorie: <?php echo $product["category"]; ?></p>
<p>Unterkategorie: <?php echo $product["subcategory"]; ?></p>

<br>

<footer id="footer-wrapper">
  

    <div id="footersocial">
      <ul>
        <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
        <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
        <li><a href="#"><img src="images/footer-telefon.png" alt="Email"></a></li>
        <li><a href="#"><img src="images/footer-anfahrt.png" alt="Telefon"></a></li>
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