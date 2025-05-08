function checkScreenSize() {
    if (window.innerWidth >= 1024) {
      window.location.href = "index.html"; 
    }
  }

  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);