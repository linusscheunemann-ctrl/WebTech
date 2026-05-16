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