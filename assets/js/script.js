document.addEventListener("DOMContentLoaded", () => {
  // Inisialisasi Locomotive Scroll
  const scroll = new LocomotiveScroll({
    el: document.querySelector('[data-scroll-container]'),
    smooth: true
  });

  // Pastikan scroll update setelah halaman dan gambar dimuat
  window.addEventListener('load', () => {
    document.body.classList.add('loaded');
    scroll.update();

    // Update scroll setiap gambar selesai dimuat
    document.querySelectorAll('img').forEach(img => {
      img.addEventListener('load', () => scroll.update());
    });
  });

  // Optional: Jika scrollRestoration mengganggu
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  // Guard untuk redirect ke mobile
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
    scroll.update(); // penting saat resize
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

  // --- Manual Typewriter ---
  // const sentences = [
  //   "Find the harmony between aesthetics, functionality and character that reflects your vision."
  // ];

  // const el = document.getElementById("typewriter-text");
  // let sentenceIndex = 0;
  // let charIndex = 0;
  // let hasAnimated = false;

  // function typeSentence() {
  //   if (charIndex < sentences[sentenceIndex].length) {
  //     el.innerHTML += sentences[sentenceIndex].charAt(charIndex);
  //     charIndex++;
  //     setTimeout(typeSentence, 40);
  //   } else {
  //     sentenceIndex++;
  //     if (sentenceIndex < sentences.length) {
  //       setTimeout(() => {
  //         el.innerHTML += "<br>";
  //         charIndex = 0;
  //         typeSentence();
  //       }, 500);
  //     }
  //   }
  // }

  // const observer = new IntersectionObserver((entries) => {
  //   entries.forEach(entry => {
  //     if (entry.isIntersecting && !hasAnimated) {
  //       hasAnimated = true;
  //       setTimeout(typeSentence, 300);
  //     }
  //   });
  // }, {
  //   threshold: 0.5
  // });

  // observer.observe(document.querySelector('.deskripsi-title'));

  // --- FAQ Toggle ---
  document.querySelectorAll(".card-faq-content").forEach(faq => {
    faq.addEventListener("click", () => {
      faq.classList.toggle("active");
    });
  });
});
