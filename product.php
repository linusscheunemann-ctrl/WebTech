<?php
if (!isset($_GET["pid"]) || $_GET["pid"] === "") {
    die("Parameter is missing!");
}

$pid = (int) $_GET["pid"];

$pid2 = null;
if (isset($_GET["id2"]) && $_GET["id2"] !== "") {
    $pid2 = (int) $_GET["id2"];
}

$json = file_get_contents(__DIR__ . "/config/product.json");

if (!$json) {
    die("JSON file not found!");
}

$data = json_decode($json, true);

if (!$data || !isset($data["products"])) {
    die("Invalid JSON structure!");
}

function findProduct($data, $id) {
    foreach ($data["products"] as $item) {
        if ((int) $item["id"] === $id) {
            return $item;
        }
    }

    return null;
}

$product1 = findProduct($data, $pid);

if (!$product1) {
    die("Product not found for ID: " . $pid);
}

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
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

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
                <?php echo (int) $product1["id"]; ?>,
                <?php echo json_encode($product1["name"]); ?>,
                <?php echo json_encode((string) $product1["price"]); ?>,
                <?php echo json_encode($product1["image"]); ?>
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
                <?php echo (int) $product2["id"]; ?>,
                <?php echo json_encode($product2["name"]); ?>,
                <?php echo json_encode((string) $product2["price"]); ?>,
                <?php echo json_encode($product2["image"]); ?>
            )">
            Kaufen
        </button>

        <p>Kategorie: <?php echo htmlspecialchars($product2["category"]); ?></p>
        <p>Unterkategorie: <?php echo htmlspecialchars($product2["subcategory"]); ?></p>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
