window.addEventListener('load', () => {
    document.body.classList.add('loaded');
  });
const fadeImage = document.getElementById("fade-image");

const observer = new IntersectionObserver((entries) => {
entries.forEach(entry => {
    if (entry.isIntersecting) {
    fadeImage.classList.add("show");
    }
});
}, {
threshold: 0.5 // aktif jika 50% terlihat
});

observer.observe(fadeImage);
const sr = ScrollReveal({
    origin: 'top',
    distance: '80px',
    duration: 2000,
    reset: false     
})
/* -- ABOUT -- */
sr.reveal('.nav-logo',{delay: 100})
sr.reveal('.short-text',{delay: 100})
sr.reveal('.image-box', {
    delay: 200,
    afterReveal: function(el) {
        VanillaTilt.init(el, {
            max: 5,
            speed: 400,
            glare: true,
            perspective: 2000,
            "max-glare": 0.4,
            easing: "ease-out"
        });
    }
  });
sr.reveal('.top-title',{delay: 100})
sr.reveal('.bottom-title',{delay: 200})
sr.reveal('.Masterpieces',{delay: 240})
sr.reveal('.btn-explore p',{delay: 300})
sr.reveal('.btn-explore',{delay: 100})
sr.reveal('.marquee-left',{delay: 300})
sr.reveal('.marquee-right',{delay: 300})

// ABOUT US
sr.reveal('.about-us-content',{interval: 200})
sr.reveal('.about-us-text',{delay: 200})
sr.reveal('.about-deskripsi',{delay: 200})
sr.reveal('.about-us-img',{delay: 200})
sr.reveal('.partnership-img',{interval: 100})


//OTHER ELEMENT
sr.reveal('.curve-title',{delay: 200})
sr.reveal('.curve-deskripsi',{delay: 300})
sr.reveal('.curve-logo',{delay: 200})
sr.reveal('.title-inovasi',{delay: 200})
sr.reveal('.deskripsi-inovasi',{delay: 300})
sr.reveal('.inovasi-box',{interval: 100})
sr.reveal('.main-containt-testi',{interval: 100})
sr.reveal('.card-title',{interval: 100})
sr.reveal('.name-title',{interval: 100})

//FOUNDER
sr.reveal('.title-founder',{delay: 100})
sr.reveal('.deskripsi-founder',{delay: 200})
sr.reveal('.founder-logo',{delay: 200})
sr.reveal('.founder-information', {
    delay: 200,
    afterReveal: function(el) {
        VanillaTilt.init(el, {
            max: 5,
            speed: 400,
            glare: true,
            perspective: 1500,
            "max-glare": 0.4,
            easing: "cubic-bezier(.03,.98,.52,.99)"
        });
    }
  });
sr.reveal('.name-founder',{delay: 200})
sr.reveal('.deskripsi-information',{delay: 300})
sr.reveal('.founder-img',{delay: 200})
