<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// 1. Booking Form Submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['client_name'])) {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $project_type = mysqli_real_escape_string($conn, $_POST['project_type']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Simpan data ke DB
    $sql = "INSERT INTO booking (client_name, email, project_type, message, status, manual_status, created_at)
            VALUES ('$client_name', '$email', '$project_type', '$message', 'pending', 'belum', NOW())";

    if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);

        // Hitung jumlah proyek on progress
        $cek_progress = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM booking WHERE manual_status = 'progress'");
        $jumlah = mysqli_fetch_assoc($cek_progress)['jumlah'];

        // Siapkan email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ravstudioandbuild@gmail.com';
            $mail->Password   = 'xksz knnk eoju obyd';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('ravstudioandbuild@gmail.com', 'RAV Studio & Build');
            $mail->addAddress($email, $client_name);

            if ($jumlah >= 3) {
                // Kirim email antrian
                $mail->Subject = "Proyek Anda Sedang Dalam Antrian";
                $mail->Body = "Halo $client_name,\n\n"
                    . "Terima kasih telah mengisi form booking untuk proyek: $project_type.\n\n"
                    . "Saat ini kami sedang menangani banyak proyek (lebih dari 3), sehingga proyek Anda akan masuk ke dalam antrian terlebih dahulu.\n"
                    . "Kami akan menghubungi Anda kembali segera setelah proyek Anda siap untuk dimulai.\n\n"
                    . "Terima kasih atas pengertiannya.\n\nSalam,\nTim RAV Studio & Build";

                $mail->send();
                mysqli_query($conn, "UPDATE booking SET status = 'pending' WHERE id = $last_id");
            } else {
                // Kirim email konfirmasi normal
                $mail->Subject = "Booking Confirmation: $project_type";
                $mail->Body = "Hi $client_name,\n\n"
                    . "We've successfully received your booking:\n\n"
                    . "Project Type: $project_type\nMessage:\n$message\n\n"
                    . "We'll contact you shortly to begin the project.\n\n"
                    . "Regards,\nRAV Studio & Build";

                $mail->send();
                mysqli_query($conn, "UPDATE booking SET status = 'email_sent' WHERE id = $last_id");
            }

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            mysqli_query($conn, "UPDATE booking SET status = 'email_failed' WHERE id = $last_id");
            error_log("Email gagal dikirim: " . $mail->ErrorInfo);
            echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// 2. Update Manual Status → Jika status progress berkurang, kirim ke antrian
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int)$_POST['id'];
    $new_status = $_POST['status'];

    // Ambil status lama
    $result = mysqli_query($conn, "SELECT manual_status FROM booking WHERE id = $id");
    $old = mysqli_fetch_assoc($result)['manual_status'];

    // Update status baru
    mysqli_query($conn, "UPDATE booking SET manual_status = '$new_status' WHERE id = $id");

    $response = ['status' => 'success', 'antrian_dipanggil' => false];

    // // Setelah update progress_status jadi "Selesai"
    // $progress = $_POST['progress_status']; // misal dari dropdown

    // Jika dari progress → selesai, cek apakah proyek aktif sekarang < 3
    if ($old === 'progress' && $new_status === 'selesai') {
        $cek_progress = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE status = 'email_sent' AND manual_status = 'progress'");
        $jumlah_now = mysqli_fetch_assoc($cek_progress)['jumlah'];

        if ($jumlah_now < 3) {
            // Cari klien pending tertua (masuk antrian)
            $pending = mysqli_query($conn, "SELECT * FROM booking WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1");
            if ($client = mysqli_fetch_assoc($pending)) {
                $email = $client['email'];
                $client_name = $client['client_name'];
                $project_type = $client['project_type'];
                $client_id = $client['id'];

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'ravstudioandbuild@gmail.com';
                    $mail->Password   = 'xksz knnk eoju obyd';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('ravstudioandbuild@gmail.com', 'RAV Studio & Build');
                    $mail->addAddress($email, $client_name);
                    $mail->Subject = "Konfirmasi Proyek Siap Dikerjakan: $project_type";
                    $mail->Body = "Halo $client_name,\n\n"
                        . "Kami informasikan bahwa proyek Anda ($project_type) yang sebelumnya dalam antrian kini sudah bisa kami mulai.\n"
                        . "Mohon balas email ini untuk memberikan konfirmasi bahwa Anda setuju kami mulai mengerjakannya.\n\n"
                        . "Terima kasih atas kesabarannya!\n\nSalam,\nTim RAV Studio & Build";

                    $mail->send();
                    mysqli_query($conn, "UPDATE booking SET status = 'waiting_send', manual_status = 'progress' WHERE id = $client_id");

                    $response['antrian_dipanggil'] = true;
                    $response['client_id'] = $client_id;
                } catch (Exception $e) {
                    mysqli_query($conn, "UPDATE booking SET status = 'email_failed' WHERE id = $client_id");
                    error_log("Gagal kirim email antrian ke $email: " . $mail->ErrorInfo);
                    $response['status'] = 'error';
                    $response['message'] = "Gagal kirim email ke antrian: " . $mail->ErrorInfo;
                }
            }
        }
    }

    echo json_encode($response);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- --------- UNICONS ---------- -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke-rounded.css">

    <!-- --------- CSS ---------- -->
    <link rel="stylesheet" href="assets/css/project.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.css">
    <!-- --------- FAVICON ---------- -->
    <link rel="shortcut icon" href="assets/img/RAV LOGO.png" type="image/x-icon">

    <!-- LOTTIE -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <title>RAV Studio & Build</title>
</head>
<body>
   <div class="container" data-scroll-container>
    <!-- --------------- HEADER --------------- -->
      <nav id="header">
        <div class="nav-logo">
            <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
            <span>.</span>
        </div>  
        <div class="nav-menu" id="myNavMenu">
            <ul class="nav_menu_list">
                <li class="nav_list">
                    <a href="index.php" class="nav-link">Home</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="about.php" class="nav-link">About</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="#project" class="nav-link scroll-link active-link" data-target="#project">Project</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="contact.html" class="nav-link">Contact</a>
                    <div class="circle"></div>
                </li>
            </ul>
        </div>
      </nav>
    <!-- ---------- ---- MAIN ---------------- -->
    <main class="wrapper">
       <!-- -------------- PROJECT ---------------- -->
       <div class="project">
            <section class="top-project" data-scroll-section  id="project">
                <div class="top-project-contain">
                    <div class="left-project-contain">
                        <div class="top-title-project">
                            <div class="first-title" data-scroll data-scroll-direction="vertikal" data-scroll-speed="6" data-scroll-position="top">
                                <p>Projects That Tell Stories</p>
                            </div>
                            <div class="sec-title" data-scroll data-scroll-direction="vertikal" data-scroll-speed="4" data-scroll-position="top">
                                <h2>Each space we design holds meaning — shaped by people, place, and purpose.</h2>
                            </div>
                            <a href="detail-project.html" class="project-btn">
                                See Our Projects
                            </a>
                        </div>
                        <div class="top-grafik-bg" data-scroll data-scroll-direction="vertikal" data-scroll-speed="2" data-scroll-position="top">
                            <img src="assets/img/PROJECT BG.png" alt="project-grafik" loading="lazy">
                        </div>
                    </div>
                </div>
                <div class="blur-mask-bottom"></div>
            </section>
            <!-- -------------- ars journey ---------------- -->
            <section class="ars-journey" data-scroll-section data-scroll-section-id="section1">
                <div class="journey-contain" data-scroll data-scroll-delay="0.04" data-scroll-speed="6">
                    <div class="title-journey"data-scroll>
                        <p>Discover Our Architecture Journey</p>
                    </div>
                    <div class="journey-box" >
                        <div class="journey-box-contain" >
                            <div class="journey-box-img">
                                <img src="assets/img/JOURNEY1.jpg" alt="JOURNEY1" loading="lazy">
                            </div>
                        </div>
                        <div class="journey-box-contain">
                            <div class="journey-box-img">
                                <img src="assets/img/JOURNEY2.jpg" alt="JOURNEY2" loading="lazy">
                            </div>
                        </div>
                        <div class="journey-box-contain">
                            <div class="journey-box-img">
                                <img src="assets/img/JOURNEY3.jpg" alt="JOURNEY3" loading="lazy">
                            </div>
                        </div>
                        <div class="journey-box-contain">
                            <div class="journey-box-img">
                                <img src="assets/img/JOURNEY4.jpg" alt="JOURNEY4" loading="lazy">
                            </div>
                        </div>
                        <div class="journey-box-contain">
                            <div class="journey-box-img">
                                <img src="assets/img/JOURNEY5.jpg" alt="JOURNEY5" loading="lazy">
                            </div>
                        </div>
                    </div>
                    <p class="deskripsi-journey">A curated collection of our architectural explorations — each project crafted with intent, context, and clarity.</p>
                </div>
            </section>

            <!-- -------------- people-feel ---------------- -->
            <section class="people-feel" data-scroll-section>
                <div class="people-feel-contain">
                    <div class="people-feel-text">
                        <div class="top-text-feel" data-scroll data-scroll-speed="4">
                            <p>We don't just build structures</p>
                        </div>
                        <div class="bottom-text-feel" data-scroll data-scroll-delay="0.04" data-scroll-speed="-1" data-scroll-offset="0.5">
                            <p>— we shape how people feel within them.</p>
                        </div>
                    </div>
                    <div class="people-feel-img" data-scroll data-scroll-speed="1">
                        <img src="assets/img/PEOPLE FEEL.jpg" alt="people feel">
                    </div>
                </div>
            </section>

            <!-- -------------- first project ---------------- -->
            <section class="first-project" data-scroll-section>
                <div class="first-project-contain"data-scroll data-scroll-speed="2">
                    <div class="text-first-project">
                        <div class="title-first-project">
                            <p class="main-title">Casa Luma Residence</p>
                            <p class="label-first-project">First Project</p>
                        </div>
                        <div class="subtitle-first-project">
                            <p>Residential Architecture - Private House</p>
                        </div>
                    </div>
                    <div class="first-project-main">
                        <div class="img-first-project">
                            <div class="img-contain-project">
                                <img src="assets/img/RAV LOGO.png" alt="" class="logo-rav">
                            </div>
                            <p class="summary-project">The design emphasizes natural ventilation, shaded courtyards, and an effortless indoor-outdoor flow. Wooden slats, concrete textures, and lush landscape elements define its identity.</p>
                        </div>
                        <div class="deskripsi-first-project">
                            <div class="deskripsi-contain">
                                <div class="deskripsi-child">
                                    <p class="title-deskripsi">Location</p>
                                    <p class="contain-deskripsi">Bali, Indonesia</p>
                                </div>
                                <div class="deskripsi-child">
                                    <p class="title-deskripsi">Area</p>
                                    <p class="contain-deskripsi">320 sqm</p>
                                </div>
                                <div class="deskripsi-child">
                                    <p class="title-deskripsi">Status</p>
                                    <p class="contain-deskripsi">Completed in 2016</p>
                                </div>
                                <div class="deskripsi-child">
                                    <p class="title-deskripsi">Typology</p>
                                    <p class="contain-deskripsi">Residential / Tropical Modern</p>
                                </div>
                            </div>
                            <a href="" class="btn-deskripsi-project">See Detail!</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- -------------- short testimonial ---------------- -->
            <section class="short-testimonial" data-scroll-section>
                <div class="short-testimonial-contain">
                    <div class="marquee" id="marquee"  data-scroll data-scroll-speed="4">
                        <div class="blur-mask-marquee-top"></div>
                        <div class="marquee-contain">
                            <div class="marquee-left">
                                <div class="marquee-content marquee-up">
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 1.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 2.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 3.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 1.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 2.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 3.jpg" alt="">
                                </div>
                                </div>
                            </div>

                            <div class="marquee-right">
                                <div class="marquee-content marquee-down">
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 1.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 2.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 3.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 1.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 2.jpg" alt="">
                                </div>
                                <div class="marquee-content-img">
                                    <img src="assets/img/cafe marquee 3.jpg" alt="">
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="blur-mask-marquee-bottom"></div>
                    </div>
                    <div class="text-testimonial" data-scroll data-scroll-speed="6">
                        <p class="petik">“</p>
                        <div class="top-title-testimonial">
                            <p class="contain-top-title">Working with RAV Studio was an absolute game-changer for my office.</p>
                            <p class="person-tag">— Lena Marlowe, Founder of Google</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- -------------- form project ---------------- -->
            <section class="form-project" data-scroll-section>
                <div class="form-project-contain">
                    <div class="top-text-form-project">
                        <div class="first-text-form-project">
                            <p>You made it all the way here!</p>
                        </div>
                        <div class="sec-text-form-project">
                            <p>Looks like you enjoyed our work. Ready to create something together?</p>
                        </div>
                    </div>
                    <div class="marquee-partnership">
                        <div class="marquee-partnership-contain">
                            <div class="partnership-img">
                                <img src="assets/img/Waskita_Karya.svg.png" alt="Waskita Karya">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/9ee8f4998ab4543f70a43de78a41b676.png" alt="DJARUM">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/SCC.BK_BIG-afaf9b39.png" alt="SCC">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/eeb0edf2975364ee5755ab29c1bf176f.png" alt="POCARI SWEAT">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Dulux-Logo.png" alt="DULUX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/MowilexPremiumPaints.webp" alt="MOWILEX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Waskita_Karya.svg.png" alt="Waskita Karya">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/9ee8f4998ab4543f70a43de78a41b676.png" alt="DJARUM">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/SCC.BK_BIG-afaf9b39.png" alt="SCC">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/eeb0edf2975364ee5755ab29c1bf176f.png" alt="POCARI SWEAT">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Dulux-Logo.png" alt="DULUX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/MowilexPremiumPaints.webp" alt="MOWILEX">
                            </div>
                        </div>
                        <div class="marquee-partnership-contain">
                            <div class="partnership-img">
                                <img src="assets/img/Waskita_Karya.svg.png" alt="Waskita Karya">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/9ee8f4998ab4543f70a43de78a41b676.png" alt="DJARUM">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/SCC.BK_BIG-afaf9b39.png" alt="SCC">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/eeb0edf2975364ee5755ab29c1bf176f.png" alt="POCARI SWEAT">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Dulux-Logo.png" alt="DULUX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/MowilexPremiumPaints.webp" alt="MOWILEX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Waskita_Karya.svg.png" alt="Waskita Karya">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/9ee8f4998ab4543f70a43de78a41b676.png" alt="DJARUM">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/SCC.BK_BIG-afaf9b39.png" alt="SCC">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/eeb0edf2975364ee5755ab29c1bf176f.png" alt="POCARI SWEAT">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/Dulux-Logo.png" alt="DULUX">
                            </div>
                            <div class="partnership-img">
                                <img src="assets/img/MowilexPremiumPaints.webp" alt="MOWILEX">
                            </div>
                        </div>
                    </div>
                    <div class="form-contain">
                        <div class="left-contain-form">
                            <div class="top-text-form">
                                <div class="title-form">
                                    <h2>Let's Build Your Dream Space!</h2>
                                </div>
                                <div class="deskripsi-form">
                                    <p>Discuss your vision with our architects and get expert guidance to bring your dream project to life.</p>
                                </div>
                            </div>
                            <form action="" method="post" id="booking-form" class="form-group">
                                <input type="text" class="form-control" name="client_name" placeholder="Name / Full Name" required>
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                                <input type="text" class="form-control" name="project_type" placeholder="Project Type (cafe, house, etc.)" required>
                                <textarea class="form-control" name="message" placeholder="Message" required></textarea>
                                <button class="btn-shine" type="submit" name="submit_booking">
                                    <span>Make Your Project</span>
                                </button>
                            </form>
                        </div>
                        <div class="right-contain-form">
                            <div class="top-right-contain">
                                <p>ARCHITECTURE WITH PURPOSE</p>
                                <img src="assets/img/form about svg.svg" alt="svg">
                            </div>
                            <div class="bottom-right-contain">
                                <p>Every line drawn shapes a better tomorrow.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grafik-bottom">
                        <img src="assets/img/grafik2.png" alt="">
                        <div class="deskripsi-grafik">
                            <p>Sustainable Structures Enduring Beauty</p>
                        </div>
                    </div>
                </div>
            </section>
            <!-- MODAL NOTIFIKASI -->
            <div id="successModal" class="modal" style="display: none;">
            <div class="modal-content">
                <p class="title-modal">Your Booking Has Been Received!</p>
                <p class="deskripsi-modal">Thank you for reaching out. Our team will get back to you shortly to discuss your project further.</p>
                <dotlottie-player src="https://lottie.host/0bb1ed25-5d7a-43c6-af7d-6f9316a1341e/QOdZ5Yj9sq.lottie" background="transparent" speed="1" style="width: 300px; height: 300px" loop autoplay></dotlottie-player>
                <button id="closeModal" class="got-it">Got It</button>
            </div>
            </div>
        </div>
    </main>

    <!-- --------------- FOOTER --------------- -->
    <footer data-scroll-section>
        <div class="left-footer">
            <div class="logo-footer">
                <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
            </div>
            <div class="sosmed">
                <div class="sosmed-contain">
                    <i class="hgi hgi-stroke hgi-instagram"></i><p>ravstudio.id</p>
                </div>
                <div class="sosmed-contain">
                    <i class="hgi hgi-stroke hgi-mail-01"></i><p>ravarchitect1@gmail.com</p>
                </div>
            </div>
            <div class="grafik-footer">
                <img src="assets/img/grafik2.png" alt="">
            </div>
        </div>
        <div class="right-footer">
            <div class="footer-text">
                <div class="right-title">
                    <p>Stay Inspired</p>
                </div>
                <div class="deskripsi-footer">
                    <p>Get the latest stories and project updates.</p>
                </div>
            </div>
            <div class="bottom-footer">
                <p>Copyright &copy; <a href="#home" style="text-decoration: none;">RAV</a> - All rights reserved</p>
            </div>
        </div>
    </footer>
    </div>
    <!-- ----- TYPING JS Link ----- -->
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>

    <script src="https://unpkg.com/scrollreveal"></script>


    <!-- ----- LOCOMOTIVE ----- -->
    <script src="https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.js"></script>

    <script nomodule src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.6.0/polyfill.min.js" crossorigin="anonymous"></script>
    <script nomodule src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?features=Object.assign%2CElement.prototype.append%2CNodeList.prototype.forEach%2CCustomEvent%2Csmoothscroll" crossorigin="anonymous"></script>

    <script src="assets/js/locomotive-scroll.min.js"></script>

    <!-- Tambahkan GSAP dan ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>



    <!-- ----- MAIN JS ----- -->
    <script src="assets/js/project.js"></script>
</body>
</html>