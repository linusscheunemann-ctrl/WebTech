// ## Beginn KI generierter Code (Codex)
// JavaScript für die responsive Navigation: Öffnen, Schließen und Accessibility-Features der Navbar
document.addEventListener("DOMContentLoaded", () => {
  // Die drei Elemente steuern das responsive Navigationsmenü
  const header = document.querySelector(".site-header");
  const nav = document.getElementById("site-navigation");
  const toggle = document.querySelector(".nav-toggle");

  // Ohne diese Elemente kann das Menü nicht arbeiten, deshalb hier abbrechen
  if (!header || !nav || !toggle) {
    return;
  }

  // Setzt den sichtbaren Zustand des Menüs und hält ARIA-Angaben aktuell
  const setOpen = (isOpen) => {
    header.classList.toggle("is-open", isOpen);
    toggle.setAttribute("aria-expanded", String(isOpen));
    toggle.setAttribute("aria-label", isOpen ? "Menü schließen" : "Menü öffnen");
  };

  // Klick auf den Burger-Button öffnet oder schließt das Menü
  toggle.addEventListener("click", () => {
    setOpen(!header.classList.contains("is-open"));
  });

  // Klick auf einen Navigationslink schließt das Menü wieder
  nav.addEventListener("click", (event) => {
    if (event.target instanceof HTMLElement && event.target.closest("a")) {
      setOpen(false);
    }
  });

  // Klick außerhalb des Headers schließt das Menü
  document.addEventListener("click", (event) => {
    if (!header.contains(event.target)) {
      setOpen(false);
    }
  });

  // Escape-Taste schließt das Menü als Tastatur-Alternative
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setOpen(false);
    }
  });

  // Bei größeren Bildschirmen wird ein eventuell offenes Mobile-Menü zurückgesetzt
  window.addEventListener("resize", () => {
    if (window.innerWidth > 900) {
      setOpen(false);
    }
  });
});

// ## Schluss KI generierter Code (Codex)