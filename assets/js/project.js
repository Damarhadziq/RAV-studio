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

    formData.set('message', formData.get('message').replace(/[\r\n]+/g, ' '));
    fetch("", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes("success")) {
            // Tampilkan modal sukses
            document.getElementById("successModal").style.display = "flex";
            document.body.classList.add("modal-open");
            form.reset();
        } else {
            // Tampilkan pesan error
            alert("Terjadi kesalahan saat mengirim:\n" + data);
        }

        // Aktifkan kembali tombol
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    })
    .catch(error => {
        alert("Gagal mengirim data:\n" + error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Tombol OK di modal
document.getElementById("closeModal").addEventListener("click", function () {
    document.getElementById("successModal").style.display = "none";
    document.body.classList.remove("modal-open");
});

// function updateStatus(id, status) {
//     const formData = new FormData();
//     formData.append('action', 'update_status');
//     formData.append('id', id);
//     formData.append('status', status);

//     fetch('', {
//         method: 'POST',
//         body: formData
//     })
//     .then(res => res.json())
//     .then(data => {
//         if (data.status === 'success') {
//             alert('Status berhasil diperbarui!');
//             if (data.antrian_dipanggil) {
//                 alert('Klien antrian dengan ID ' + data.client_id + ' telah dikirim email!');
//             }
//             location.reload();
//         } else {
//             alert('Gagal memperbarui status: ' + data.message);
//         }
//     })
//     .catch(err => alert('Terjadi kesalahan: ' + err));
// }

});

