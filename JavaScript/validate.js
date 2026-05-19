// Wartet, bis das gesamte HTML-Dokument vollständig geladen wurde
document.addEventListener("DOMContentLoaded", function () {

    // ===== BENUTZERNAME =====
    // Holt das Eingabefeld mit der ID "username"
    const username = document.getElementById("username");
    // Holt das Element, in dem die Validierungsnachrichten angezeigt werden
    const msgUser = document.getElementById("msg-user");
    // Prüft, ob beide Elemente existieren
    if (username && msgUser) {
        // Reagiert auf jede Eingabe im Benutzernamen-Feld
        username.addEventListener("input", function () {
            // Speichert den aktuellen Wert des Eingabefeldes
            const value = username.value;
            // Prüft, ob mindestens 5 Zeichen vorhanden sind
            const hasLength = value.length >= 5;
            // Prüft, ob mindestens ein Großbuchstabe vorhanden ist
            const hasUpper = /[A-Z]/.test(value);
            // Prüft, ob mindestens ein Kleinbuchstabe vorhanden ist
            const hasLower = /[a-z]/.test(value);

            // Wenn das Feld leer ist
            if (value.length === 0) {
                // Löscht die Anzeige der Meldungen
                msgUser.innerHTML = "";
                // Beendet die Funktion
                return;
            }

            // Erstellt die HTML-Ausgabe mit den Prüfergebnissen
            msgUser.innerHTML = `
                <ul class="validation-list">

                    <!-- Regel: mindestens 5 Zeichen -->
                    <li class="${hasLength ? 'ok' : 'fail'}">

                        <!-- Zeigt ✓ oder ✗ abhängig vom Ergebnis -->
                        <span class="icon">${hasLength ? '✓' : '✗'}</span>

                        Mindestens 5 Zeichen
                    </li>

                    <!-- Regel: mindestens ein Großbuchstabe -->
                    <li class="${hasUpper ? 'ok' : 'fail'}">

                        <!-- Zeigt ✓ oder ✗ -->
                        <span class="icon">${hasUpper ? '✓' : '✗'}</span>

                        Mindestens ein Großbuchstabe
                    </li>

                    <!-- Regel: mindestens ein Kleinbuchstabe -->
                    <li class="${hasLower ? 'ok' : 'fail'}">

                        <!-- Zeigt ✓ oder ✗ -->
                        <span class="icon">${hasLower ? '✓' : '✗'}</span>

                        Mindestens ein Kleinbuchstabe
                    </li>

                </ul>
            `;
        });
    }


    // ===== PASSWORT =====
    // Holt das Passwortfeld
    const password = document.getElementById("password");

    // Holt das Feld zur Passwort-Bestätigung
    const confirm = document.getElementById("confirm_password");

    // Holt das Element für Passwortmeldungen
    const msgPw = document.getElementById("msg-pw");

    // Funktion zur Passwortprüfung
    function validatePassword() {

        // Prüft, ob Passwortfeld und Meldungsfeld existieren
        if (!password || !msgPw) {
            // Beendet die Funktion, wenn etwas fehlt
            return;
        }

        // Speichert das aktuelle Passwort
        const pw = password.value;

        // Speichert das Bestätigungspasswort
        // Falls kein confirm-Feld existiert, wird ein leerer String gesetzt
        const cpw = confirm ? confirm.value : "";

        // Prüft, ob das Passwort mindestens 10 Zeichen lang ist
        const hasLength = pw.length >= 10;

        // Prüft, ob beide Passwörter identisch sind
        // Wenn kein confirm-Feld existiert, gilt die Prüfung automatisch als true
        const matches = confirm ? pw === cpw && pw.length > 0 : true;

        // Wenn beide Felder leer sind
        if (pw.length === 0 && (!confirm || cpw.length === 0)) {
            // Löscht die Meldungen
            msgPw.innerHTML = "";
            // Beendet die Funktion
            return;
        }

        // Erstellt den Beginn der HTML-Ausgabe
        let message = `
            <ul class="validation-list">

                <!-- Regel: mindestens 10 Zeichen -->
                <li class="${hasLength ? 'ok' : 'fail'}">

                    <!-- Zeigt ✓ oder ✗ -->
                    <span class="icon">${hasLength ? '✓' : '✗'}</span>

                    Mindestens 10 Zeichen
                </li>
        `;

        // Wenn ein Bestätigungsfeld existiert
        if (confirm) {

            // Fügt die Passwortvergleichs-Regel hinzu
            message += `
                <li class="${matches ? 'ok' : 'fail'}">

                    <!-- Zeigt ✓ oder ✗ -->
                    <span class="icon">${matches ? '✓' : '✗'}</span>

                    Passwörter stimmen überein
                </li>
            `;
        }

        message += `</ul>`;

        // Gibt die fertige HTML-Ausgabe im Meldungsfeld aus
        msgPw.innerHTML = message;
    }

    // Prüft, ob Passwortfeld und Meldungsbereich existieren
    if (password && msgPw) {

        // Führt die Passwortprüfung bei jeder Eingabe aus
        password.addEventListener("input", validatePassword);

        // Wenn ein Bestätigungsfeld existiert
        if (confirm) {

            // Führt die Prüfung auch dort bei jeder Eingabe aus
            confirm.addEventListener("input", validatePassword);
        }
    }
});