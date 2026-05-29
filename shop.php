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
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<!-- Navigation Ende -->


<!-- SHOP CONTAINER -->
<div class="wrapper">
    <div class="shop">

        <!-- Produkte werden hier per shop.js + config/product.json geladen -->

    </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<!-- FOOTER -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
