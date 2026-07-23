<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Autos</title>
    <style>
        .autos-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px 56px;
        }

        .autos-section h1 {
            text-align: center;
            margin-bottom: 28px;
        }

        .autos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .autos-grid .vehicle-card {
            background: #ffffff;
            color: #111111;
        }

        .autos-grid .vehicle-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .autos-grid .vehicle-card-content {
            padding: 18px;
        }

        .autos-grid .vehicle-card-content h3 {
            margin: 0 0 8px;
        }

        .autos-grid .vehicle-card-content p {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation Anfang -->
   <?php require_once __DIR__ . '/includes/navbar.php'; ?>
    <!-- Navigation Ende -->
<!--## Beginn Code von Moritz -->
    <main class="autos-section">
        <h1>Autos</h1>
        <div class="autos-grid">
            <article class="vehicle-card">
                <img src="images/classic-cars-ingolstadt-11.jpg" alt="Ferrari F40">
                <div class="vehicle-card-content">
                    <h3>Ferrari F40</h3>
                    <p>620 PS</p>
                </div>
            </article>

            <article class="vehicle-card">
                <img src="images/bg9.jpg" alt="Ferrari Enzo">
                <div class="vehicle-card-content">
                    <h3>Ferrari Enzo</h3>
                    <p>660 PS</p>
                </div>
            </article>
        </div>
    </main>
<!--## Schluss Code von Moritz -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
