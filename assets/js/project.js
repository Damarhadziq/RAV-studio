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

  document.getElementById("booking-form").addEventListener("submit", function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        fetch("", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            document.getElementById("successModal").style.display = "flex";
            document.body.classList.add("modal-open");

            form.reset();
        })
    });

    document.getElementById("closeModal").addEventListener("click", function () {
        document.getElementById("successModal").style.display = "none";
        document.body.classList.remove("modal-open");
    });
});

