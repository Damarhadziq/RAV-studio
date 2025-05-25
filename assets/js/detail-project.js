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

    setTimeout(() => {
        scroll.update();
    }, 100);

    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('load', () => {
            setTimeout(() => {
                scroll.update();
            }, 50);
        });
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
});
