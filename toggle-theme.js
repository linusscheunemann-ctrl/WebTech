/*const toDark = ["🌕","🌖","🌗","🌘","🌑"];
const toLight = ["🌒","🌓","🌔","🌕"];

let isDark = localStorage.getItem("theme") === "dark";
let isAnimating = false;

const html = document.documentElement;

// Theme anwenden
function applyTheme() {
    if (isDark) {
        html.setAttribute("data-theme", "dark");
    } else {
        html.removeAttribute("data-theme");
    }
}

// Icon setzen
function updateIcon() {
    const btn = document.getElementById("theme-toggle");
    if (!btn) return;

    btn.textContent = isDark ? "🌑" : "🌕";
}

// WICHTIG: erst nach DOM laden
document.addEventListener("DOMContentLoaded", () => {
    applyTheme();
    updateIcon();
});

// Button-Funktion
function myFunction() {
    if (isAnimating) return;
    isAnimating = true;

    const btn = document.getElementById("theme-toggle");
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
            updateIcon();

            isAnimating = false;
        }
    }, 80);
}*/


const toDark = ["🌕","🌖","🌗","🌘","🌑"];
const toLight = ["🌒","🌓","🌔","🌕"];
let isDark = localStorage.getItem("theme") === "dark";
let isAnimating = false;
const html = document.documentElement;

function applyTheme() {
    if (isDark) {
        html.setAttribute("data-theme", "dark");
    } else {
        html.removeAttribute("data-theme");
    }
}
applyTheme();



function myFunction() {
    if (isAnimating) return;
    isAnimating = true;

    const btn = document.getElementById("theme-toggle");
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
    }, 80);
}