var typingEffect = new Typed(".typedText1", {
    strings: ["Dreams", "Futures","Visions"],
    loop : true,
    typeSpeed : 100, 
    backSpeed : 80,
    backDelay : 2000
 })
var typingEffect = new Typed(".typedText2", {
    strings: ["Memories.", "Legacies.","Experiences."],
    loop : true,
    typeSpeed : 100, 
    backSpeed : 40,
    backDelay : 2000
 })

 const sr = ScrollReveal({
    origin: 'top',
    distance: '80px',
    duration: 2000,
    reset: false     
})
window.addEventListener('load', () => {
  document.body.classList.add('loaded');
});
/* -- HOME -- */
sr.reveal('.nav-logo',{delay: 100})
sr.reveal('.featured-text-card',{})
sr.reveal('.header',{delay: 300})
sr.reveal('.typedText1',{delay: 200})
sr.reveal('.sec-head',{delay: 100})
sr.reveal('.deskripsi',{delay: 100})
sr.reveal('.btn-home',{delay: 100})
sr.reveal('.icon',{interval: 100})
sr.reveal('.confident-text',{interval: 150})
sr.reveal('.card-confident',{delay: 100})

// CRAFTING BOX
sr.reveal('.header-craft-text',{delay: 100})
sr.reveal('.deskripsi-craft',{delay: 200})
sr.reveal('.content-box-img', {
  interval: 200,
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

sr.reveal('.header-box',{interval: 100})
sr.reveal('.deskripsi-box',{interval: 100})

// ABOUT US
sr.reveal('.about-us-content',{interval: 200})
sr.reveal('.about-us-text',{delay: 200})
sr.reveal('.about-deskripsi',{delay: 200})
sr.reveal('.about-us-img',{delay: 200})
sr.reveal('.partnership-img',{interval: 100})
sr.reveal('.elevating',{interval: 200})
sr.reveal('.elev-text',{interval: 200})

// OTHER ELEMENT
sr.reveal('.first-text',{delay: 200})
sr.reveal('.sec-text-content',{interval: 200})
sr.reveal('.third-text',{delay: 300})
sr.reveal('.quality-box-content',{interval: 100})
sr.reveal('.quality-btn',{delay: 100})
sr.reveal('.quality-img',{delay: 100})
sr.reveal('.main-title',{delay: 200})
sr.reveal('.deskripsi-title',{delay: 200})
sr.reveal('.opening-project',{delay: 300})
sr.reveal('.main-content',{interval: 100})
sr.reveal('.card-title',{interval: 100})
sr.reveal('.name-title',{interval: 100})
sr.reveal('.main-containt-testi',{interval: 100})
sr.reveal('.card-faq-content',{interval: 100})
sr.reveal('.faq-title',{delay: 200})
sr.reveal('.grafik-bottom img',{delay: 100})
sr.reveal('.deskripsi-grafik',{delay: 200})

const sentences = [
    "Find the harmony between aesthetics, functionality and character that reflects your vision."
  ];

  const el = document.getElementById("typewriter-text");
  let sentenceIndex = 0;
  let charIndex = 0;
  let hasAnimated = false;

  function typeSentence() {
    if (charIndex < sentences[sentenceIndex].length) {
      el.innerHTML += sentences[sentenceIndex].charAt(charIndex);
      charIndex++;
      setTimeout(typeSentence, 40); 
    } else {
      sentenceIndex++;
      if (sentenceIndex < sentences.length) {
        setTimeout(() => {
          el.innerHTML += "<br>"; 
          charIndex = 0;
          typeSentence();
        }, 500); 
      }
    }
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !hasAnimated) {
        hasAnimated = true;
        setTimeout(typeSentence, 300);
      }
    });
  }, {
    threshold: 0.5
  });

  observer.observe(document.querySelector('.deskripsi-title'));

  const faqs = document.querySelectorAll(".card-faq-content");

  faqs.forEach((faq) => {
    faq.addEventListener("click", () => {
      faq.classList.toggle("active");
    });
  });