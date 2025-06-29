<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$clientReviews = mysqli_query($conn, "SELECT * FROM client_review ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- --------- UNICONS ---------- -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke-rounded.css">

    <!-- --------- CSS ---------- -->
    <link rel="stylesheet" href="assets/css/about.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.css">

    <!-- --------- FAVICON ---------- -->
    <link rel="shortcut icon" href="assets/img/RAV LOGO.png" type="image/x-icon">

    <!-- --------- FAVICON ---------- -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.2/vanilla-tilt.min.js"></script>
    
    <!-- LOTTIE -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <title>RAV Studio & Build</title>
</head>
<body>
   <div class="container" data-scroll-container>
    <!-- --------------- HEADER --------------- -->
      <nav id="header">
        <a href="index.php" class="nav-logo">
            <img src="./assets/img/RAV LOGO.png" alt="RAV LOGO">
        </a>  
        <div class="nav-menu" id="myNavMenu">
            <ul class="nav_menu_list">
                <li class="nav_list">
                    <a href="index.php" class="nav-link">Home</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="#about" class="nav-link scroll-link active-link" data-target="#about">About</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="project.php" class="nav-link">Project</a>
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
       <!-- -------------- OPENING ABOUT ---------------- -->
       <div class="about" id="#about">
        <section class="opening-about" data-scroll-section id="about">
            <div class="opening-about-containt">
                <div class="mid-contain">
                    <div class="short-text">
                        <p>Our Story, Our Vision</p>
                    </div>
                    <div class="overlay-four-img">
                        <div class="four-image">
                            <div class="image-box">
                                <img src="assets/img/four-img1.jpg" alt="image1">
                            </div>
                            <div class="image-box">
                                <img src="assets/img/four-img2.jpg" alt="image2">
                            </div>
                            <div class="image-box">
                                <img src="assets/img/four-img3.jpg" alt="image3">
                            </div>
                            <div class="image-box">
                                <img src="assets/img/four-img4.jpg" alt="image4">
                            </div>
                        </div>
                        <div class="title-about">
                            <div class="top-title">
                                <span>How We Transform Spaces into </span>
                            </div>
                            <div class="bottom-title">
                                <span>Timeless</span>
                                <span class="Masterpieces">Masterpieces</span>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#marquee" class="btn-marquee" data-scroll-to>
                    <div class="btn-explore">
                        <p>Explore Now</p>
                    </div>
                </a>
                <div class="about-bg">
                    <img src="assets/img/bg about.png" alt="bg-about">
                </div>
                <div class="marquee" id="marquee">
                    <div class="marquee">
                        <div class="marquee-left">
                          <div class="marquee-content">
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                          </div>
                          <div class="marquee-content">
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                          </div>
                        </div>
                        <div class="marquee-right">
                          <div class="marquee-content">
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                          </div>
                          <div class="marquee-content">
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Where Creativity Meets Functionality</p>
                            <img src="assets/img/Frame 308.svg" alt="">
                            <p>Building Beyond Structures</p>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        </div>

        <!-- -------------- ABOUT US ---------------- -->
        <section class="about-us" data-scroll-section>
            <div class="about-us-content" data-scroll data-scroll-speed="2">
                <div class="bg-about">
                        <img src="assets/img/710fe7c6f6c6e89df3060695f62ff610.jpeg" alt="">
                    </div>
                    <div class="about-contain">
                        <div class="about-us-img">
                            <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
                        </div>
                        <div class="about-us-text">
                            <div class="about-deskripsi">
                                <p>At RAV Studio & Build, we believe that architecture is more than just structures—it's about crafting timeless spaces that inspire and elevate everyday living. With a commitment to innovation, sustainability, and aesthetic excellence, we blend art and functionality to create designs that stand the test of time.
                                Our team of passionate architects and designers specializes in delivering bespoke solutions for residential, commercial, and urban projects. Every detail is meticulously considered, ensuring that our creations not only meet but exceed expectations.</p>
                            </div>
                        </div>
                    </div>
                </div>
         </section>

        <!-- -------------- PARTNERSHIP ---------------- -->
         <section class="partnership" data-scroll-section>
            <div class="partnership-content" data-scroll>
                <div class="partnership-img" data-scroll data-scroll-delay="0.018" data-scroll-speed="3">
                    <img src="assets/img/Waskita_Karya.svg.png" alt="Waskita Karya">
                </div>
                <div class="partnership-img" data-scroll data-scroll-delay="0.02" data-scroll-speed="3">
                    <img src="assets/img/9ee8f4998ab4543f70a43de78a41b676.png" alt="DJARUM">
                </div>
                <div class="partnership-img" data-scroll data-scroll-delay="0.04" data-scroll-speed="3">
                    <img src="assets/img/SCC.BK_BIG-afaf9b39.png" alt="SCC">
                </div>
                <div class="partnership-img" data-scroll data-scroll-delay="0.06" data-scroll-speed="3">
                    <img src="assets/img/eeb0edf2975364ee5755ab29c1bf176f.png" alt="POCARI SWEAT">
                </div>
                <div class="partnership-img" data-scroll data-scroll-delay="0.08" data-scroll-speed="3">
                    <img src="assets/img/Dulux-Logo.png" alt="DULUX">
                </div>
                <div class="partnership-img" data-scroll data-scroll-delay="0.1" data-scroll-speed="3">
                    <img src="assets/img/MowilexPremiumPaints.webp" alt="MOWILEX">
                </div>
            </div>
         </section>

         <!-- -------------- THE FOUNDER ---------------- -->
         <section class="founder" data-scroll-section>
            <div class="founder-contain" data-scroll data-scroll-speed="6">
                <div class="founder-img">
                    <img src="assets/img/founder-img.jpg" alt="">
                </div>
                <div class="founder-main-contain">
                    <div class="founder-text-title">
                        <div class="founder-title">
                            <p class="founder-title-label">Founder</p>
                            <p class="founder-main-title">Azizah Rara</p>
                        </div>
                        <p class="founder-short-text">This studio was founded from a deep passion for designing spaces that are not only functional, but also meaningful and inspiring. Every line and detail is crafted with a human-centered approach, mindful of sustainability, and responsive to its context.</p>
                    </div>
                    <div class="sosmed-founder">
                        <a href="" class="sosmed-icon-founder">
                            <i class="hgi hgi-stroke hgi-instagram"></i>
                        </a>
                        <a href="" class="sosmed-icon-founder">
                            <i class="hgi hgi-stroke hgi-twitter"></i>
                        </a>
                        <a href="" class="sosmed-icon-founder">
                            <i class="hgi hgi-stroke hgi-linkedin-02"></i>
                        </a>
                    </div>
                    <div class="founder-experience">
                        <p class="title-founder-experience">Ms. Rara Experience</p>
                        <div class="founder-experience-icon">
                            <div class="main-founder-experience">
                                <i class="hgi hgi-stroke hgi-checkmark-circle-03"></i><p>Over 40+ Projects Designed Across 4 Years</p>
                            </div>
                            <div class="main-founder-experience">
                                <i class="hgi hgi-stroke hgi-checkmark-circle-03"></i><p>30+ Satisfied Clients Served Since 2020</p>
                            </div>
                            <div class="main-founder-experience">
                                <i class="hgi hgi-stroke hgi-checkmark-circle-03"></i><p>10 Cities Reached with Contextual Design Solutions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </section>
          
        <!-- -------------- OTHER ELEMEN ---------------- -->
         <section class="other-element" data-scroll-section>
            <div class="inovasi">
                <div class="inovasi-text" data-scroll data-scroll-speed="2">
                    <div class="title-inovasi">
                        <p>Building the Future with Creativity</p>
                    </div>
                    <div class="deskripsi-inovasi">
                        <p>Through a collaborative and explorative approach, we continuously innovate to craft designs that remain relevant in an ever-evolving world. We don't just construct buildings—we create experiences, emotions, and identities within every architectural detail.</p>
                    </div>
                </div>
                <div class="inovasi-contain" data-scroll>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.02" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-idea-01"></i>
                        <p>Innovative Design Approach</p>
                    </div>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.04" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-leaf-01"></i>
                        <p>Sustainability & Eco-Friendly Solutions</p>
                    </div>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.06" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-agreement-01"></i>
                        <p>Client-Centric Collaboration</p>
                    </div>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.08" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-artboard"></i>
                        <p>Attention to Detail & Craftsmanship</p>
                    </div>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.14" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-building-05"></i>
                        <p>Timeless & Impactful Spaces</p>
                    </div>
                    <div class="inovasi-box" data-scroll data-scroll-delay="0.2" data-scroll-speed="6">
                        <i class="hgi hgi-stroke hgi-nano-technology"></i>
                        <p>Seamless Integration of Technology</p>
                    </div>
                </div>
            </div>
         </section>

        <!-- -------------- TESTIMONIAL ---------------- -->
        <section class="testimoni" data-scroll-section>
            <div class="testimoni-content">
                <?php while ($row = mysqli_fetch_assoc($clientReviews)) : ?>
                    <div class="card-testi-content">
                        <div class="card-title">
                            <img src="<?= htmlspecialchars($row['photo']) ?>" alt="profile" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;">
                            <div class="name-title">
                                <div class="name-client">
                                    <p><?= htmlspecialchars($row['client_name']) ?></p>
                                </div>
                                <div class="client-project">
                                    <p><?= htmlspecialchars($row['project_name']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="main-containt-testi">
                            <p><?= htmlspecialchars($row['review']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="grafik-bottom">
                <img src="assets/img/grafik2.png" alt="">
                <div class="deskripsi-grafik">
                    <p>Sustainable Structures Enduring Beauty</p>
                </div>
            </div>
        </section>
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

    <!-- ----- SCROLL REVEAL JS Link----- -->
    <script src="https://unpkg.com/scrollreveal"></script>

    <script src="https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.js"></script>

    <script nomodule src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.6.0/polyfill.min.js" crossorigin="anonymous"></script>

    <script nomodule src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?features=Object.assign%2CElement.prototype.append%2CNodeList.prototype.forEach%2CCustomEvent%2Csmoothscroll" crossorigin="anonymous"></script>

    <script src="assets/js/locomotive-scroll.min.js"></script>

    <!-- ----- MAIN JS ----- -->
    <script src="assets/js/about.js"></script>
    
</body>
</html>