<?php
// ## Beginn Code von Linus

// Startet die aktuelle Session, damit auf die Session-Daten zugegriffen werden kann
session_start();

// Löscht alle gespeicherten Session-Variablen
$_SESSION = [];

// Prüft, ob PHP Session-Cookies verwendet
if (ini_get('session.use_cookies')) {

    // Holt die aktuellen Cookie-Einstellungen der Session
    $params = session_get_cookie_params();

    // Löscht das Session-Cookie im Browser,
    // indem ein Ablaufdatum in der Vergangenheit gesetzt wird
    setcookie(
        session_name(),       // Name des Session-Cookies
        '',                   // Leerer Wert
        time() - 42000,       // Ablaufzeit in der Vergangenheit
        $params['path'],      // Cookie-Pfad
        $params['domain'],    // Cookie-Domain
        $params['secure'],    // Nur über HTTPS senden?
        $params['httponly']   // Kein Zugriff über JavaScript?
    );
}

// Löscht die Session auf dem Server vollständig
session_destroy();

// Leitet den Benutzer zur Login-Seite weiter
header('Location: login.php');

// Beendet die Skriptausführung sofort
exit;
// ## Schluss Code von Linus