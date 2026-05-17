const toDark = ["🌕","🌖","🌗","🌘","🌑"];
const toLight = ["🌑","🌒","🌓","🌔","🌕"];

// Prüft, ob im localStorage bereits das dunkle Theme gespeichert wurde
// Ergebnis ist true oder false
let isDark = localStorage.getItem("theme") === "dark";

// Variable verhindert, dass mehrere Animationen gleichzeitig laufen
let isAnimating = false;

// Speichert das <html>-Element der Seite
const html = document.documentElement;

// Variable für den Theme-Button
// Anfangs noch null, bis der Button gefunden wird
let themeButton = null;


// ===== BUTTON SUCHEN =====
// Funktion sucht den Button mit der ID "theme-toggle"
function findThemeButton() {

    // Speichert das gefundene Element in der Variable themeButton
    themeButton = document.getElementById("theme-toggle");
}


// ===== BUTTON AKTUALISIEREN =====
// Funktion aktualisiert das Emoji im Button
function updateThemeButton() {

    // Wenn kein Button existiert, wird die Funktion beendet
    if (!themeButton) return;
    themeButton.textContent = isDark ? "🌑" : "🌕";
}


// ===== THEME ANWENDEN =====

// Funktion setzt das aktuelle Theme auf der Webseite
function applyTheme() {

    // Wenn Darkmode aktiv ist
    if (isDark) {

        // Setzt das Attribut data-theme="dark" im HTML-Element
        html.setAttribute("data-theme", "dark");

    } else {

        // Entfernt das data-theme-Attribut → Lightmode
        html.removeAttribute("data-theme");
    }

    // Aktualisiert anschließend den Button
    updateThemeButton();
}

// ===== THEME INITIALISIEREN =====
// Funktion wird beim Laden der Seite ausgeführt
function initTheme() {

    // Sucht den Theme-Button
    findThemeButton();

    // Aktualisiert das Button-Emoji
    updateThemeButton();
}


// Wendet direkt beim Start das gespeicherte Theme an
applyTheme();


// Prüft, ob die Seite noch lädt
if (document.readyState === "loading") {

    // Wartet bis das HTML vollständig geladen wurde
    document.addEventListener("DOMContentLoaded", initTheme);

} else {

    // Falls die Seite bereits geladen ist:
    // Initialisierung sofort ausführen
    initTheme();
}


// ===== THEME WECHSELN =====
// Funktion wird beim Klick auf den Theme-Button aufgerufen
function myFunction() {

    // Verhindert mehrfaches Starten während der Animation
    if (isAnimating) return;

    // Markiert Animation als aktiv
    isAnimating = true;

    // Nutzt den bereits gespeicherten Button
    // Falls nicht vorhanden, wird er erneut gesucht
    const btn = themeButton || document.getElementById("theme-toggle");

    // Wenn kein Button gefunden wurde
    if (!btn) {

        // Animation zurücksetzen
        isAnimating = false;

        // Funktion beenden
        return;
    }

    // Wählt die passende Emoji-Animation aus
    // Wenn aktuell Darkmode aktiv ist → Animation zu Hellmode
    // Sonst → Animation zu Darkmode
    let sequence = isDark ? toLight : toDark;

    // Startindex für die Animation
    let i = 0;

    // Startet ein Intervall für die Emoji-Animation
    let interval = setInterval(() => {

        // Setzt das aktuelle Emoji im Button
        btn.textContent = sequence[i];

        // Erhöht den Index
        i++;

        // Wenn alle Emojis abgespielt wurden
        if (i >= sequence.length) {

            // Stoppt das Intervall
            clearInterval(interval);

            // Wechselt den Theme-Status
            isDark = !isDark;

            // Speichert das neue Theme im localStorage
            localStorage.setItem("theme", isDark ? "dark" : "light");

            // Wendet das neue Theme an
            applyTheme();

            // Animation ist beendet
            isAnimating = false;
        }

    // Geschwindigkeit der Animation:
    // alle 80 Millisekunden
    }, 80);
}