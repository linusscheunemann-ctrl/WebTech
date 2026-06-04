<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Über uns</title>
</head>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>
<!--## Beginn Code von Moritz -->
    <main class="about-page">
        <section class="about-intro">
            <h1>Unser Standort</h1>
            <p>Hier findest du uns und kannst direkt eine Route von deinem Startpunkt aus planen.</p>
        </section>

        <section class="location-card">
            <div class="location-card-content">
                <h2>Anfahrt planen</h2>
                <p>Zieladresse: Erny-Singler-Straße, 85053 Ingolstadt</p>

                <form class="location-form" action="https://www.google.com/maps/dir/" method="get" target="_blank">
                    <input type="hidden" name="api" value="1">
                    <input type="hidden" name="destination" value="Erny-Singler-Straße, 85053 Ingolstadt">

                    <label for="origin">Dein Standort</label>
                    <input
                        type="text"
                        id="origin"
                        name="origin"
                        placeholder="Straße, Ort oder PLZ"
                        autocomplete="street-address"
                        required
                    >

                    <button type="submit">Route starten</button>
                </form>
            </div>

            <div class="location-map">
                <iframe
                    title="Karte zum Standort"
                    src="https://www.google.com/maps?q=Erny-Singler-Straße,+85053+Ingolstadt&z=15&output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
        </section>

        <section class="contact-grid">
            <div class="contact-card">
                <h2>Kontakt</h2>
                <p>Autohaus Dein Autohaus</p>
                <p>Erny-Singler-Straße, 85053 Ingolstadt</p>
                <p>Telefon: 01234-567890</p>
                <p>Email: info@deinautohaus.de</p>
            </div>

            <div class="contact-card">
                <h2>Öffnungszeiten</h2>
                <p>Mo-Fr: 9-18 Uhr</p>
                <p>Sa: 10-14 Uhr</p>
                <p>So: geschlossen</p>
            </div>
        </section>

    </main>
<!--## Schluss Code von Moritz -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
