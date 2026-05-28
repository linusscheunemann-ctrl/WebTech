<?php
$pageRoot = __DIR__;
require_once $pageRoot . '/includes/app.php';

$contactSuccessMessage = '';
$contactErrorMessage = '';
$contactName = '';
$contactEmail = '';
$contactSubject = '';
$contactMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_contact_message') {
    $contactName = trim((string) ($_POST['name'] ?? ''));
    $contactEmail = trim((string) ($_POST['email'] ?? ''));
    $contactSubject = trim((string) ($_POST['subject'] ?? ''));
    $contactMessage = trim((string) ($_POST['message'] ?? ''));

    if ($contactName === '' || $contactEmail === '' || $contactSubject === '' || $contactMessage === '') {
        $contactErrorMessage = 'Bitte fülle alle Felder aus.';
    } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $contactErrorMessage = 'Bitte gib eine gültige E-Mail-Adresse an.';
    } else {
        $recipient = 'moritz.parleiten@gmail.com';
        $mailSubject = 'Kontaktanfrage: ' . $contactSubject;
        $body = "Name: {$contactName}\n";
        $body .= "E-Mail: {$contactEmail}\n";
        $body .= "Betreff: {$contactSubject}\n\n";
        $body .= "Nachricht:\n{$contactMessage}\n";

        $sentResult = appSendMailSmtp(
            appGetMailConfig(),
            $recipient,
            'Moritz Parleiten',
            $mailSubject,
            $body,
            $contactEmail,
            $contactName
        );

        if ($sentResult['success']) {
            $contactSuccessMessage = 'Deine Nachricht wurde erfolgreich an uns gesendet.';
            $contactName = '';
            $contactEmail = '';
            $contactSubject = '';
            $contactMessage = '';
        } else {
            $contactErrorMessage = $sentResult['error'] !== null
                ? 'Die Nachricht konnte nicht gesendet werden: ' . $sentResult['error']
                : 'Die Nachricht konnte nicht gesendet werden. Bitte versuche es später erneut.';
        }
    }
}
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
    <title>Über uns</title>
</head>
<body>
    <a href="index.php">
        <img src="images/logo.png" alt="Autohaus" class="logo">
    </a>

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
            <a href="cart.php" class="cart-icon">
                <img src="images/cart.webp" class="cart-img" alt="Warenkorb">
            </a>
            <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>
            <a href="login.php">
                <img src="images/login.png" alt="Login" class="login-icon">
            </a>
        </div>
    </nav>

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

        <section class="contact-form-section" id="contact">
            <div class="contact-form-card">
                <h2>Kontaktanfrage</h2>
                <p>Schreib uns direkt eine Nachricht. Sie wird an <strong>moritz.parleiten@gmail.com</strong> gesendet.</p>

                <?php if ($contactSuccessMessage !== ''): ?>
                    <p class="form-message success-message"><?php echo htmlspecialchars($contactSuccessMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($contactErrorMessage !== ''): ?>
                    <p class="form-message error-message"><?php echo htmlspecialchars($contactErrorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <form class="contact-form" action="about.php#contact" method="post">
                    <input type="hidden" name="action" value="send_contact_message">

                    <label for="contact-name">Name</label>
                    <input
                        type="text"
                        id="contact-name"
                        name="name"
                        value="<?php echo htmlspecialchars($contactName, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >

                    <label for="contact-email">E-Mail</label>
                    <input
                        type="email"
                        id="contact-email"
                        name="email"
                        value="<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >

                    <label for="contact-subject">Betreff</label>
                    <input
                        type="text"
                        id="contact-subject"
                        name="subject"
                        value="<?php echo htmlspecialchars($contactSubject, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >

                    <label for="contact-message">Nachricht</label>
                    <textarea
                        id="contact-message"
                        name="message"
                        rows="6"
                        required
                    ><?php echo htmlspecialchars($contactMessage, ENT_QUOTES, 'UTF-8'); ?></textarea>

                    <button type="submit">Nachricht senden</button>
                </form>
            </div>
        </section>
    </main>

    <footer id="footer-wrapper">
        <div id="footersocial">
            <ul>
                <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
                <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
            </ul>
        </div>

        <div id="claim-footer">
            <span id="head-footer">Kontakt</span>
            <p>Erny-Singler-Straße, 85053 Ingolstadt</p>
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
