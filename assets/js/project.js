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
    const submitBtn = form.querySelector("button[type='submit']");

    // Disable tombol dan ubah teksnya
    submitBtn.disabled = true;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = "<span>Sending...</span>";

    formData.set('message', formData.get('message').replace(/[\r\n]+/g,' '));
    fetch("", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes("success")) {
            document.getElementById("successModal").style.display = "flex";
            document.body.classList.add("modal-open");
            form.reset();
        } else {
            alert("Terjadi kesalahan saat mengirim:\n" + data);
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    })
    .catch(error => {
        alert("Gagal mengirim data:\n" + error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

document.getElementById("closeModal").addEventListener("click", function () {
    document.getElementById("successModal").style.display = "none";
    document.body.classList.remove("modal-open");
});

  const sr = ScrollReveal({
    origin: 'top',
    distance: '100px',
    duration: 2000,
    reset: false
  });

  sr.reveal('.nav-logo',{delay: 100})

  
document.querySelectorAll('.scroll-link').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const targetSelector = this.getAttribute('data-target');
    const target = document.querySelector(targetSelector);
    
    if (target) {
      scroll.scrollTo(target, {
        offset: 0,
        duration: 1000,
        easing: [0.25, 0.0, 0.35, 1.0]
      });

      history.pushState(null, null, targetSelector);
    }
  });
});

});

