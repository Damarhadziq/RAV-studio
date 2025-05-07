function checkScreenSize() {
    if (window.innerWidth >= 1024) {
      window.location.href = "index.html"; 
    }
  }

  checkScreenSize(); // Cek saat pertama kali halaman dibuka
  window.addEventListener('resize', checkScreenSize);