<?php
$pageRoot = __DIR__;
require_once $pageRoot . '/includes/app.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Impressum</title>
</head>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="about-page">
        <section class="about-intro">
            <h1>Impressum</h1>
            <p>Angaben gemäß § 5 TMG.</p>
        </section>

        <section class="contact-card">
            <h2>Betreiber</h2>
            <p><strong>Autohaus Dein Autohaus</strong></p>
            <p>Auto Union Straße 1</p>
            <p>85053 Ingolstadt</p>
            <p>Telefon: 01234-567890</p>
            <p>E-Mail: info@deinautohaus.de</p>
        </section>

        <section class="contact-card">
            <h2>Vertretungsberechtigt</h2>
            <p>#</p>
        </section>

        <section class="contact-card">
            <h2>Verantwortlich für den Inhalt</h2>
            <p>Autohaus Dein Autohaus</p>
            <p>Auto Union Straße 1</p>
            <p>85053 Ingolstadt</p>
            <p>E-Mail: info@deinautohaus.de</p>
        </section>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
