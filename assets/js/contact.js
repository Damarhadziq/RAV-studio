document.addEventListener("DOMContentLoaded", () => {
  
  const scroll = new LocomotiveScroll({
    el: document.querySelector('[data-scroll-container]'),
    smooth: true,
    lerp: 0.15,
    multiplier: 1.2
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

  // --- ScrollReveal ---
  const sr = ScrollReveal({
    origin: 'top',
    distance: '100px',
    duration: 2000,
    reset: false
  });

  sr.reveal('.nav-logo',{delay: 100})
  sr.reveal('.top-title-project',{delay: 200})
  sr.reveal('.top-grafik-bg img',{delay: 300})
  sr.reveal('.img-contain img',{delay: 300})
  sr.reveal('.blur-card',{delay: 100})
  sr.reveal('.blur-svg',{delay: 100})
  sr.reveal('.elipse-contain',{delay: 100})
});

