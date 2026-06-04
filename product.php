<?php
// ## Beginn Code von Linus
// Prüft, ob die Produkt-ID (pid) über die URL übergeben wurde.
// Beispiel: product.php?pid=1
if (!isset($_GET["pid"]) || $_GET["pid"] === "") {
    die("Parameter is missing!");
}

// Wandelt die übergebene Produkt-ID in eine Ganzzahl um
$pid = (int) $_GET["pid"];

// Zweite Produkt-ID standardmäßig auf null setzen
$pid2 = null;

// Prüft, ob eine zweite Produkt-ID (id2) übergeben wurde
// Beispiel: product.php?pid=1&id2=2
if (isset($_GET["id2"]) && $_GET["id2"] !== "") {
    $pid2 = (int) $_GET["id2"];
}

// Liest den Inhalt der JSON-Datei mit den Produktdaten ein
$json = file_get_contents(__DIR__ . "/config/product.json");

// Fehlerbehandlung, falls die Datei nicht gelesen werden kann
if (!$json) {
    die("JSON file not found!");
}

// Wandelt den JSON-Text in ein PHP-Array um
$data = json_decode($json, true);

// Prüft, ob die JSON-Daten gültig sind
// und ob der Schlüssel "products" existiert
if (!$data || !isset($data["products"])) {
    die("Invalid JSON structure!");
}

// Sucht ein Produkt anhand seiner ID.
 
function findProduct($data, $id) {

    // Durchläuft alle Produkte
    foreach ($data["products"] as $item) {

        // Vergleicht die Produkt-ID mit der gesuchten ID
        if ((int) $item["id"] === $id) {
            return $item; // Produkt gefunden
        }
    }

    // Kein Produkt gefunden
    return null;
}

// Sucht das erste Produkt anhand der übergebenen pid
$product1 = findProduct($data, $pid);

// Falls kein Produkt gefunden wurde, Skript beenden
if (!$product1) {
    die("Product not found for ID: " . $pid);
}

// Zweites Produkt standardmäßig auf null setzen
$product2 = null;

// Falls eine zweite ID vorhanden ist,
// wird auch das zweite Produkt gesucht
if ($pid2 !== null) {
    $product2 = findProduct($data, $pid2);
}
// ## Schluss Code von Linus
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

<!-- Beginn KI generierter Code -->
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
<!-- Schluss KI generierter Code -->
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
