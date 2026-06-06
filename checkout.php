
<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}
// Beginn Code von Linus

// Prüfen, ob der Benutzer eingeloggt ist (Session-Variablen vorhanden)
if (empty($_SESSION['username']) || empty($_SESSION['user_id'])) {
    // Wenn nicht eingeloggt: Weiterleitung zur Login-Seite
    header('Location: login.php?return_to=cart.php');
    exit;
}

// Prüfen, ob die Datenbankverbindung (PDO) existiert
if (!isset($pdo)) {
    // Falls kein DB-Zugriff möglich ist: Fehler zurückgeben
    header('Location: cart.php?booking=database_error');
    exit;
}

// Aktuelle Benutzerdaten aus der Datenbank laden
$currentUser = appLoadCurrentUser($pdo);

// Falls kein Benutzer gefunden wurde (z. B. ungültige Session)
if (!$currentUser) {
    // Zur Login-Seite weiterleiten
    header('Location: login.php?return_to=cart.php');
    exit;
}

// Prüfen, ob der Benutzer gesperrt ist
if ((int) ($currentUser['is_blocked'] ?? 0) === 1) {
    // Wenn gesperrt: Zurück zum Warenkorb mit Fehlermeldung
    header('Location: cart.php?booking=blocked');
    exit;
}
// Schluss Code von Linus
// Beginn KI generierter Code
// Warenkorb-Daten aus dem POST-Request holen (JSON-String)
$cartPayload = $_POST['cart_payload'] ?? '';

// JSON in ein PHP-Array umwandeln
$cartItems = json_decode($cartPayload, true);

// Prüfen, ob gültige Warenkorb-Daten vorhanden sind
if (!is_array($cartItems) || count($cartItems) === 0) {
    header('Location: cart.php?booking=empty');
    exit;
}

// Initialisierung für normalisierte Artikel und Zwischensumme
$normalizedItems = [];
$subtotalAmount = 0.0;

// Jeden Warenkorb-Artikel validieren und bereinigen
foreach ($cartItems as $item) {

    // Produktdaten sicher extrahieren und typisieren
    $productId = isset($item['id']) ? (int) $item['id'] : null;
    $productName = trim((string) ($item['name'] ?? ''));
    $unitPrice = (float) ($item['price'] ?? 0);
    $quantity = (int) ($item['menge'] ?? 0);
    $image = trim((string) ($item['image'] ?? ''));

    // Ungültige Einträge überspringen
    if ($productName === '' || $unitPrice <= 0 || $quantity <= 0) {
        continue;
    }

    // Positionswert berechnen (Preis * Menge)
    $lineTotal = $unitPrice * $quantity;

    // Zur Gesamtsumme hinzufügen
    $subtotalAmount += $lineTotal;

    // Bereinigte Artikelstruktur speichern
    $normalizedItems[] = [
        'product_id' => $productId,
        'product_name' => $productName,
        'unit_price' => $unitPrice,
        'quantity' => $quantity,
        'image' => $image !== '' ? $image : null,
    ];
}

// Falls nach Validierung keine gültigen Produkte übrig sind
if ($normalizedItems === []) {
    header('Location: cart.php?booking=empty');
    exit;
}

try {
    // Transaktion starten (Datenbank-Integrität sichern)
    $pdo->beginTransaction();

    // Neue Buchung in der Datenbank anlegen (Initialwerte ohne Rabatt)
    $bookingInsert = $pdo->prepare(
        'INSERT INTO bookings (user_id, subtotal_amount, total_amount, discount_percent, discount_amount, discount_label, status)
         VALUES (:user_id, :subtotal_amount, :total_amount, :discount_percent, :discount_amount, :discount_label, :status)'
    );

    $bookingInsert->execute([
        'user_id' => (int) $_SESSION['user_id'],
        'subtotal_amount' => $subtotalAmount,
        'total_amount' => $subtotalAmount,
        'discount_percent' => 0,
        'discount_amount' => 0,
        'discount_label' => null,
        'status' => 'new',
    ]);

    // ID der neu erstellten Buchung holen
    $bookingId = (int) $pdo->lastInsertId();

    // Rabattkonfiguration laden
    $discountConfig = appGetDiscountConfig($pdo);

    // Automatischen Rabatt berechnen (z. B. Mengenrabatt, Aktionen)
    $automaticDiscount = appCalculateBookingDiscount($bookingId, $subtotalAmount, $discountConfig);

    // Gesamten Rabatt berechnen
    $discountAmount = (float) ($automaticDiscount['amount'] ?? 0)
                    ;

    // Rabatt in Prozent berechnen
    $discountPercent = $subtotalAmount > 0
        ? round(($discountAmount / $subtotalAmount) * 100, 2)
        : 0.0;

    // Rabatt-Beschreibungen sammeln
    $discountLabels = [];

    if (($automaticDiscount['amount'] ?? 0) > 0 && !empty($automaticDiscount['label'])) {
        $discountLabels[] = (string) $automaticDiscount['label'];
    }

    // Finalen Gesamtbetrag berechnen (nicht negativ zulassen)
    $finalTotal = max(0, round($subtotalAmount - $discountAmount, 2));

    // Rabatt-Label zusammensetzen
    $discountLabel = $discountLabels !== [] ? implode(' + ', $discountLabels) : null;

    // Buchung mit finalen Rabattdaten aktualisieren
    $updateBooking = $pdo->prepare(
        'UPDATE bookings
         SET total_amount = :total_amount,
             discount_percent = :discount_percent,
             discount_amount = :discount_amount,
             discount_label = :discount_label
         WHERE id = :booking_id'
    );

    $updateBooking->execute([
        'total_amount' => $finalTotal,
        'discount_percent' => $discountPercent,
        'discount_amount' => $discountAmount,
        'discount_label' => $discountLabel,
        'booking_id' => $bookingId,
    ]);

    // SQL-Statement für einzelne Buchungspositionen vorbereiten
    $itemInsert = $pdo->prepare(
        'INSERT INTO booking_items (
            booking_id,
            product_id,
            product_name,
            unit_price,
            quantity,
            image
        ) VALUES (
            :booking_id,
            :product_id,
            :product_name,
            :unit_price,
            :quantity,
            :image
        )'
    );

    // Alle Warenkorb-Items in die Datenbank schreiben
    foreach ($normalizedItems as $item) {
        $itemInsert->execute([
            'booking_id' => $bookingId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'],
        ]);
    }

    // Transaktion erfolgreich abschließen
    $pdo->commit();

} catch (Throwable $throwable) {

    // Bei Fehler: Änderungen zurückrollen
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Fehlerseite / Fehlermeldung zurückgeben
    header('Location: cart.php?booking=error');
    exit;
}

// Erfolgreiche Buchung -> Warenkorb im Browser leeren und dann weiterleiten
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=cart.php?booking=success">
    <title>Buchung abgeschlossen</title>
</head>
<body>
    <script>
        try {
            localStorage.removeItem('cart');
        } catch (error) {
            // Falls localStorage nicht verfuegbar ist, wird trotzdem weitergeleitet.
        }
        window.location.replace('cart.php?booking=success');
    </script>
    <noscript>
        <p>Die Buchung wurde abgeschlossen. <a href="cart.php?booking=success">Weiter</a>.</p>
    </noscript>
</body>
</html>
<?php
exit;
// Schluss KI generierter Code
