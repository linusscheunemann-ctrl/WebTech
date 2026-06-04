// Emoji-Folgen für die kleine Übergangsanimation beim Theme-Wechsel
const toDark = ["🌕","🌖","🌗","🌘","🌑"];
const toLight = ["🌑","🌒","🌓","🌔","🌕"];

// Liest die gespeicherte Theme-Einstellung aus dem Browser-Speicher
let isDark = localStorage.getItem("theme") === "dark";

// Verhindert, dass mehrere Theme-Animationen gleichzeitig starten
let isAnimating = false;

// Das <html>-Element wird genutzt, um das Theme per data-Attribut zu steuern
const html = document.documentElement;

// Referenz auf den Theme-Button, sobald er im DOM gefunden wurde
let themeButton = null;


// Sucht den Button mit der ID "theme-toggle" und speichert die Referenz
function findThemeButton() {
    themeButton = document.getElementById("theme-toggle");
}

// Aktualisiert das sichtbare Symbol im Button passend zum aktuellen Theme
function updateThemeButton() {
    if (!themeButton) return;
    themeButton.textContent = isDark ? "🌑" : "🌕";
}

// Setzt das Theme am HTML-Element und hält den Button dabei synchron
function applyTheme() {
    if (isDark) {
        html.setAttribute("data-theme", "dark");
    } else {
        html.removeAttribute("data-theme");
    }

    updateThemeButton();
}

// Initialisiert den Button, sobald das DOM verfügbar ist
function initTheme() {
    findThemeButton();
    updateThemeButton();
}

// Theme direkt anwenden, damit die Seite ohne sichtbaren Wechsel startet
applyTheme();

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTheme);
} else {
    initTheme();
}

// Wird beim Klick auf den Theme-Button aufgerufen und startet die Animation
function myFunction() {
    if (isAnimating) return;

    isAnimating = true;

    const btn = themeButton || document.getElementById("theme-toggle");

    if (!btn) {
        isAnimating = false;
        return;
    }

    // Je nach aktuellem Zustand die passende Übergangsfolge auswählen
    let sequence = isDark ? toLight : toDark;

    let i = 0;

    let interval = setInterval(() => {
        btn.textContent = sequence[i];
        i++;

        if (i >= sequence.length) {
            clearInterval(interval);
            isDark = !isDark;
            localStorage.setItem("theme", isDark ? "dark" : "light");
            applyTheme();
            isAnimating = false;
        }

    // Geschwindigkeit der Animation in Millisekunden
    }, 80);
}
