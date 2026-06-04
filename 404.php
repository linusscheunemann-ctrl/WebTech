<?php
// HTTP-Status für eine nicht gefundene Seite setzen
http_response_code(404);

// Angeforderte URL auslesen und für die Ausgabe sicher entschärfen
$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$requestedPath = trim((string) $requestedPath);
$requestedPath = $requestedPath !== '' ? htmlspecialchars($requestedPath, ENT_QUOTES, 'UTF-8') : 'Unbekannte Seite';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/navbar.js"></script>
    <title>404 - Seite nicht gefunden</title>
</head>
<body>
    <!-- Gemeinsame Navigation der Seite einbinden -->
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="error-page">
        <section class="error-hero">
            <p class="error-code">404</p>
            <h1>Diese Seite wurde nicht gefunden.</h1>
            <p class="error-text">
                Die angeforderte Adresse <strong><?php echo $requestedPath; ?></strong> existiert nicht oder wurde verschoben.
            </p>

            <div class="error-actions">
                <a href="index.php" class="error-button error-button--primary">Zur Startseite</a>
                <a href="shop.php" class="error-button">Zum Shop</a>
                <a href="javascript:history.back()" class="error-button">Zurück</a>
            </div>
        </section>

        <section class="error-card-grid" aria-label="Schnellzugriff">
            <a href="bestand.php" class="error-card">
                <h2>Fahrzeuge</h2>
                <p>Alle Autos und Motorräder entdecken.</p>
            </a>
            <a href="about.php" class="error-card">
                <h2>Kontakt</h2>
                <p>Mehr über Standort und Erreichbarkeit erfahren.</p>
            </a>
            <a href="impressum.php" class="error-card">
                <h2>Impressum</h2>
                <p>Rechtliche Informationen im Überblick.</p>
            </a>
        </section>
    </main>

    <!-- Gemeinsamen Footer einbinden -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
