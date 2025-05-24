<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// $result = mysqli_query($conn, "SELECT * FROM recent_project ORDER BY id ASC LIMIT 5"); // ambil 5 terbaru
$clientReviews = mysqli_query($conn, "SELECT * FROM client_review ORDER BY created_at ASC");
$faqResult = mysqli_query($conn, "SELECT * FROM faq ORDER BY id ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- --------- UNICONS ---------- -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke-rounded.css">

    <!-- --------- CSS ---------- -->
    <link rel="stylesheet" href="assets/css/styles.css">

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
        <div class="nav-logo">
            <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
            <span>.</span>
        </div>  
        <div class="nav-menu" id="myNavMenu">
            <ul class="nav_menu_list">
                <li class="nav_list">
                    <a href="#home" class="nav-link active-link">Home</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="about.php" class="nav-link">About</a>
                    <div class="circle"></div>
                </li>
                <li class="nav_list">
                    <a href="project.html" class="nav-link">Project</a>
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
       <!-- -------------- FEATURED BOX ---------------- -->
       <div class="home" id="home">
           <section class="featured-box" data-scroll-section>
               <div class="featured-text">
                <div class="video-bg">
                    <video autoplay muted loop id="bg-video">
                        <source src="video/ASSET VIDEO HOME.mp4" type="video/mp4">
                      </video>
                </div>
                   <div class="header-text" id="header-text">
                       <div class="header" data-scroll data-scroll-speed="3" data-scroll-position="top">
                           <span>Building</span>
                           <span class="typedText1">Space</span>
                           <div class="sec-head">
                               <span>Creating</span>
                               <span class="typedText2">Stories.</span>
                           </div>
                       </div>
                       <div class="deskripsi" data-scroll data-scroll-speed="4" data-scroll-position="top">
                           <p>We deliver architectural designs that are not only functional, but also aesthetic and sustainable. Find space solutions that are elegant, modern and aligned with your vision.</p>
                       </div>
                       <a href="#crafting" class="btn-home" data-scroll data-scroll-speed="6" data-scroll-position="top" data-scroll-to>
                           <div class="GIT-btn">
                               <p>Get in Touch</p>
                           </div>
                       </a>
                       <div class="social_icons" data-scroll data-scroll-speed="5" data-scroll-position="top">
                           <div class="icon">
                               <a href="https://www.instagram.com/dmrhdz.iq" target="_blank" rel="noopener noreferrer">
                                   <i class="hgi hgi-stroke hgi-instagram"></i>
                               </a>
                               <span class="tooltip">instagram</span>
                           </div>
                           <div class="icon">
                               <a href="https://www.facebook.com/@indra.alto1/?mibextid=rS40aB7S9Ucbxw6v" target="_blank" rel="noopener noreferrer">
                                   <i class="hgi hgi-stroke hgi-facebook-02"></i>
                               </a>
                               <span class="tooltip">facebook</span>
                           </div>
                           <div class="icon">
                               <a href="" target="_blank" rel="noopener noreferrer">
                                   <i class="hgi hgi-stroke hgi-whatsapp"></i>
                               </a>
                               <span class="tooltip">whatsapp</span>
                           </div>
                           <div class="icon">
                               <a href="" target="_blank" rel="noopener noreferrer">
                                   <i class="hgi hgi-stroke hgi-mail-01"></i>
                               </a>
                               <span class="tooltip">email</span>
                           </div>
                       </div>
                   </div>
                   <div class="card-confident" data-scroll data-scroll-direction="horizontal" data-scroll-speed="-4" data-scroll-position="top">
                    <div class="card-right">
                        <div class="confident-text">
                            <p><i class="hgi hgi-stroke hgi-compass-01"></i> 120+ Project</p>
                        </div>
                        <div class="confident-text">
                            <p><i class="hgi hgi-stroke hgi-agreement-02"></i> 50+ Client</p>
                        </div>
                        <div class="confident-text">
                            <p><i class="hgi hgi-stroke hgi-scroll"></i> 4 Years Experience</p>
                        </div>
                    </div>
                   </div>
               </div>
              </div>
           </section>
        
        <!-- -------------- CRAFTING BOX ---------------- -->
           <section class="crafting" id="crafting" data-scroll-section>
            <div class="crafting-contain">
                <div class="header-craft" data-scroll data-scroll-delay="0.04" data-scroll-speed="4" data-scroll-offset="1">
                    <div class="header-craft-text">
                        <span>Crafting</span>
                        <span class="sec">Space,</span>
                        <span>Elevating</span>
                        <span class="sec">Experiences</span>
                    </div>
                    <div class="deskripsi-craft">
                        <p>Every corner has meaning, every element has purpose. We deliver design that blends with lifestyle, prioritizing aesthetics, comfort and functionality.</p>
                    </div>
                </div>
                <div class="craft-content"data-scroll data-scroll-delay="0.04" data-scroll-speed="6">
                    <div class="content-box">
                        <div class="content-box-img">
                            <img src="assets/img/c394151b0fef0713dad60f35cdb4529a.jpeg" alt="Serenity in Simplicity">
                        </div>
                        <div class="header-box">Serenity in Simplicity</div>
                        <div class="deskripsi-box">Soothing simplicity, minimalism with warmth.</div>
                    </div>
                    <div class="content-box">
                        <div class="content-box-img">
                            <img src="./assets/img/318074a8eff42c6546322137dc8e65b5.jpeg" alt="Bold Elegance">
                        </div>
                        <div class="header-box">Bold Elegance</div>
                        <div class="deskripsi-box">The contrast is luxurious and full of character.</div>
                    </div>
                    <div class="content-box">
                        <div class="content-box-img">
                            <img src="assets/img/70586fc9482b248879d9a82a98f47290.jpeg" alt="Harmonious Living">
                        </div>
                        <div class="header-box">Harmonious Living</div>
                        <div class="deskripsi-box">A balance of comfortable, and lively design.</div>
                    </div>
                </div>
            </div>
           </section>

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

        <!-- -------------- PEOPLE FEEL ---------------- -->
         <section class="people-feel" data-scroll-section>
            <div class="people-feel-contain">
                <div class="people-feel-text">
                    <div class="top-text-feel" data-scroll data-scroll-speed="4">
                        <p>We don't just build structures</p>
                    </div>
                    <div class="bottom-text-feel" data-scroll data-scroll-delay="0.04" data-scroll-speed="-4" data-scroll-offset="0.05">
                        <p>— we shape how people feel within them.</p>
                    </div>
                </div>
                <div class="people-feel-img" data-scroll data-scroll-speed="1">
                    <img src="assets/img/PEOPLE FEEL.jpg" alt="people feel">
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

            
         </section>

        <!-- -------------- FAQ ---------------- -->
         <section class="faq" id="faq" data-scroll-section>
            <div class="faq-content">
                <div class="faq-title">
                    <p>Frequently Ask Question</p>
                </div>
                <?php while ($row = mysqli_fetch_assoc($faqResult)): ?>
                <div class="card-faq-content">
                    <div class="question">
                        <p><?= htmlspecialchars($row['question']) ?></p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p><?= nl2br(htmlspecialchars($row['answer'])) ?></p>
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

    <!-- ----- LOCOMOTIVE SCROLL ----- -->
    <script src="https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.js"></script>

    <script nomodule src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.6.0/polyfill.min.js" crossorigin="anonymous"></script>
    <script nomodule src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?features=Object.assign%2CElement.prototype.append%2CNodeList.prototype.forEach%2CCustomEvent%2Csmoothscroll" crossorigin="anonymous"></script>

    <script src="assets/js/locomotive-scroll.min.js"></script>
    
    <!-- ----- MAIN JS ----- -->
    <script src="assets/js/script.js"></script>

</body>
</html>