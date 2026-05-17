const toDark = ["🌕","🌖","🌗","🌘","🌑"];
const toLight = ["🌑","🌒","🌓","🌔","🌕"];
let isDark = localStorage.getItem("theme") === "dark";
let isAnimating = false;
const html = document.documentElement;
let themeButton = null;

function findThemeButton() {
    themeButton = document.getElementById("theme-toggle");
}

function updateThemeButton() {
    if (!themeButton) return;
    themeButton.textContent = isDark ? "🌑" : "🌕";
}

function applyTheme() {
    if (isDark) {
        html.setAttribute("data-theme", "dark");
    } else {
        html.removeAttribute("data-theme");
    }
    updateThemeButton();
}

function initTheme() {
    findThemeButton();
    updateThemeButton();
}

applyTheme();

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTheme);
} else {
    initTheme();
}

function myFunction() {
    if (isAnimating) return;
    isAnimating = true;

    const btn = themeButton || document.getElementById("theme-toggle");
    if (!btn) {
        isAnimating = false;
        return;
    }

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