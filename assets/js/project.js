window.addEventListener('load', () => {
  document.body.classList.add('loaded');
});

const REDIRECT_URL   = 'redirect.html';
  const MIN_DESKTOP    = 1024;

  function guardDesktop () {
    if (window.innerWidth < MIN_DESKTOP) {
      if (!location.pathname.endsWith(REDIRECT_URL)) {
        location.href = REDIRECT_URL;
      }
    }
  }

  guardDesktop();
  window.addEventListener('resize', guardDesktop);
window.addEventListener('load', () => {
  document.body.classList.add('loaded');
});

const sr = ScrollReveal({
    origin: 'top',
    distance: '100px',
    duration: 2000,
    reset: true     
})

sr.reveal('.nav-logo',{delay: 100})
sr.reveal('.first-title',{delay: 100})
sr.reveal('.sec-title',{delay: 200})
sr.reveal('.top-grafik-bg',{delay: 100})
sr.reveal('.img-contain',{delay: 100})
sr.reveal('.svg-img img',{interval: 300})
sr.reveal('.blur-card-text',{delay: 100})
sr.reveal('.blur-svg img',{delay: 200})
sr.reveal('.elipse-contain',{delay: 200})
