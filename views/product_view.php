<?php
// ## Beginn generierter Code von Codex
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/cart.js" defer></script>
    <title><?php echo htmlspecialchars((string) $product1['name'], ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="wrapper">
    <div class="product-detail">
        <h1><?php echo htmlspecialchars((string) $product1['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <img
            src="<?php echo htmlspecialchars((string) ($product1['image_path'] ?: 'images/image.png'), ENT_QUOTES, 'UTF-8'); ?>"
            class="product-detail-image"
            alt="<?php echo htmlspecialchars((string) $product1['name'], ENT_QUOTES, 'UTF-8'); ?>"
        >
        <p><?php echo htmlspecialchars((string) $product1['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        <h3>Preis: <?php echo number_format((float) $product1['price'], 2, ',', '.'); ?> €</h3>

        <button
            class="buy-btn"
            onclick="addToCart(
                <?php echo (int) $product1['id']; ?>,
                <?php echo json_encode((string) $product1['name']); ?>,
                <?php echo json_encode((string) $product1['price']); ?>,
                <?php echo json_encode((string) ($product1['image_path'] ?? '')); ?>
            )"
        >
            Kaufen
        </button>

        <p>Kategorie: <?php echo htmlspecialchars((string) $product1['category'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p>Unterkategorie: <?php echo htmlspecialchars((string) $product1['subcategory'], ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <?php if ($product2): ?>
        <div class="product-detail">
            <h1><?php echo htmlspecialchars((string) $product2['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <img
                src="<?php echo htmlspecialchars((string) ($product2['image_path'] ?: 'images/image.png'), ENT_QUOTES, 'UTF-8'); ?>"
                class="product-detail-image"
                alt="<?php echo htmlspecialchars((string) $product2['name'], ENT_QUOTES, 'UTF-8'); ?>"
            >
            <p><?php echo htmlspecialchars((string) $product2['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h3>Preis: <?php echo number_format((float) $product2['price'], 2, ',', '.'); ?> €</h3>

            <button
                class="buy-btn"
                onclick="addToCart(
                    <?php echo (int) $product2['id']; ?>,
                    <?php echo json_encode((string) $product2['name']); ?>,
                    <?php echo json_encode((string) $product2['price']); ?>,
                    <?php echo json_encode((string) ($product2['image_path'] ?? '')); ?>
                )"
            >
                Kaufen
            </button>

            <p>Kategorie: <?php echo htmlspecialchars((string) $product2['category'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Unterkategorie: <?php echo htmlspecialchars((string) $product2['subcategory'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php
// ## Schluss generierter Code von Codex
