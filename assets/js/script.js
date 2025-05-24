document.addEventListener("DOMContentLoaded", () => {

  const scroll = new LocomotiveScroll({
    el: document.querySelector('[data-scroll-container]'),
    smooth: true,
    lerp: 0.1,
    multiplier: 1
  });


  window.addEventListener('load', () => {
    document.body.classList.add('loaded');
    scroll.update();

    document.querySelectorAll('img').forEach(img => {
      img.addEventListener('load', () => scroll.update());
    });
  });

  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  const REDIRECT_URL = 'redirect.html';
  const MIN_DESKTOP = 1024;

  function guardDesktop() {
    if (window.innerWidth < MIN_DESKTOP) {
      if (!location.pathname.endsWith(REDIRECT_URL)) {
        location.href = REDIRECT_URL;
      }
    }
  }

  guardDesktop();
  window.addEventListener('resize', () => {
    guardDesktop();
    scroll.update(); 
  });

  // --- Typed.js Setup ---
  new Typed(".typedText1", {
    strings: ["Dreams", "Futures", "Visions"],
    loop: true,
    typeSpeed: 100,
    backSpeed: 80,
    backDelay: 2000
  });

  new Typed(".typedText2", {
    strings: ["Memories.", "Legacies.", "Experiences."],
    loop: true,
    typeSpeed: 100,
    backSpeed: 40,
    backDelay: 2000
  });

  // --- ScrollReveal ---
  const sr = ScrollReveal({
    origin: 'top',
    distance: '100px',
    duration: 2000,
    reset: false
  });

  sr.reveal('.header-text', { delay: 100 });
  sr.reveal('.card-right', { delay: 300 });
  

  // --- FAQ Toggle ---
  document.querySelectorAll(".card-faq-content").forEach(faq => {
    faq.addEventListener("click", () => {
      faq.classList.toggle("active");
    });
  });
});
