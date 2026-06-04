// ## Beginn Code von Moritz

// Einfache Carousel-Logik: die Folien laufen automatisch durch und koennen auch manuell angesprungen werden.
  let currentSlide = 0;
  const slides = document.querySelectorAll('.slideshow .slide');
  const slidesWrapper = document.querySelector('.slideshow .slides');
  const dots = document.querySelectorAll('.slideshow .slide-dots button');

  // Verschiebt den sichtbaren Bereich und markiert den aktiven Punkt.
  function updateSlide() {
    slidesWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => dot.classList.toggle('active', index === currentSlide));
  }

  // Wechselt zur naechsten Folie und springt am Ende wieder an den Anfang.
  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlide();
  }

  // Wechselt zur vorherigen Folie und laesst die Reihenfolge zirkulaer laufen.
  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    updateSlide();
  }

  // Springt direkt zu einer bestimmten Folie, zum Beispiel ueber die Punkt-Navigation.
  function goToSlide(index) {
    currentSlide = index;
    updateSlide();
  }

  // Automatischer Wechsel alle zehn Sekunden.
  setInterval(nextSlide, 10000);
// ## Schluss Code von Moritz