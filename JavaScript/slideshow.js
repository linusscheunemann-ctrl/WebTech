  let currentSlide = 0;
  const slides = document.querySelectorAll('.slideshow .slide');
  const slidesWrapper = document.querySelector('.slideshow .slides');
  const dots = document.querySelectorAll('.slideshow .slide-dots button');

  function updateSlide() {
    slidesWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => dot.classList.toggle('active', index === currentSlide));
  }
  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlide();
  }
  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    updateSlide();
  }
  function goToSlide(index) {
    currentSlide = index;
    updateSlide();
  }
  setInterval(nextSlide, 10000);