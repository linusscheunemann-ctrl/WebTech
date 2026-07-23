<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Motorräder</title>
    <style>
        .motorbikes-section {
            max-width: 1320px;
            margin: 0 auto;
            padding: 24px 20px 60px;
        }

        .motorbikes-section h1 {
            text-align: center;
            margin-bottom: 28px;
        }

        .motorbikes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 22px;
        }

        .motorbike-card {
            background: #ffffff;
            color: #111111;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .motorbike-card-media {
            padding: 14px 16px 0;
            box-sizing: border-box;
        }

        .motorbike-card-media img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            object-position: center;
            display: block;
            border-radius: 12px;
            margin: 0;
        }

        .motorbike-card-content {
            padding: 16px 18px 20px;
            text-align: center;
        }

        .motorbike-card-content h3 {
            margin: 0 0 8px;
            font-size: 1.1rem;
        }

        .motorbike-card-content p {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navigation Anfang -->
   <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <!-- Navigation Ende -->
    <!-- ## Beginn Code von Moritz-->
    <?php
    $motorbikeImages = glob(__DIR__ . '/images/motorrad/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
    sort($motorbikeImages, SORT_NATURAL | SORT_FLAG_CASE);
    $motorbikeImages = array_slice($motorbikeImages, 0, 58);

    // Hier kannst du für einzelne Bilder feste Namen und Preise hinterlegen.
    // Schlüssel ist der Dateiname im Ordner `images/motorrad/`.
    $motorbikeMeta = [
         '5b356b64-f229-4ec5-90fa-0484d77d93e5.jpeg' => ['title' => 'BMW R 1250 GS', 'price' => 15490],
        '456cdffe-56f4-410d-9d49-5252ecdc84ad.jpeg' => ['title' => 'Ducati Monster', 'price' => 13250],
    ];

    $motorbikeCards = [];
    foreach ($motorbikeImages as $index => $imagePath) {
        $fileName = basename($imagePath);
        $meta = $motorbikeMeta[$fileName] ?? [];

        $motorbikeCards[] = [
            'image' => substr($imagePath, strlen(__DIR__) + 1),
            'title' => $meta['title'] ?? sprintf('Motorrad %02d', $index + 1),
            'price' => number_format((float) ($meta['price'] ?? (6990 + ($index * 180))), 0, ',', '.') . ' €',
        ];
    }
    ?>

    <main class="motorbikes-section">
        <h1>Motorräder</h1>
        <div class="motorbikes-grid">
            <?php foreach ($motorbikeCards as $card): ?>
                <article class="motorbike-card">
                    <div class="motorbike-card-media">
                        <img
                            src="<?php echo htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                    <div class="motorbike-card-content">
                        <h3><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($card['price'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
    <!-- ## Schluss Code von Moritz-->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
