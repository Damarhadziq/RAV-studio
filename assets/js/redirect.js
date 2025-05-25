function checkScreenSize() {
    if (window.innerWidth >= 1024) {
      window.location.href = "index.php"; 
    }
  }

  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);