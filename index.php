<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$result = mysqli_query($conn, "SELECT * FROM recent_project ORDER BY id DESC LIMIT 5"); // ambil 5 terbaru
$review_query = mysqli_query($conn, "SELECT client_name, project_name, review FROM client_review ORDER BY created_at DESC");
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

    <!-- --------- FAVICON ---------- -->
    <link rel="shortcut icon" href="assets/img/RAV LOGO.png" type="image/x-icon">
    
    <!-- --------- FAVICON ---------- -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.2/vanilla-tilt.min.js"></script>
    
    <!-- LOTTIE -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <title>RAV Studio & Build</title>
</head>
<body>
   <div class="container">
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
                    <a href="about.html" class="nav-link">About</a>
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
        <!-- <div class="nav-menu-btn">
            <i id="menu-icon" class="uil uil-bars" onclick="toggleMenu()"></i>
            <i id="close-icon" class="uil uil-multiply" style="display: none;" onclick="toggleMenu()"></i>
        </div> -->
      </nav>
    <!-- ---------- ---- MAIN ---------------- -->
    <main class="wrapper">
       <!-- -------------- FEATURED BOX ---------------- -->
       <div class="home" id="home">
           <section class="featured-box">
               <div class="featured-text">
                <div class="video-bg">
                    <video autoplay muted loop id="bg-video">
                        <source src="video/ASSET VIDEO HOME.mp4" type="video/mp4">
                      </video>
                </div>
                   <div class="header-text" id="header-text">
                       <div class="header">
                           <span>Building</span>
                           <span class="typedText1">Space</span>
                           <div class="sec-head">
                               <span>Creating</span>
                               <span class="typedText2">Stories.</span>
                           </div>
                       </div>
                       <div class="deskripsi">
                           <p>We deliver architectural designs that are not only functional, but also aesthetic and sustainable. Find space solutions that are elegant, modern and aligned with your vision.</p>
                       </div>
                       <a href="#crafting" class="btn-home">
                           <div class="GIT-btn">
                               <p>Get in Touch</p>
                           </div>
                       </a>
                       <div class="social_icons">
                           <div class="icon">
                               <a href="https://www.instagram.com/dmrhdz.iq" target="_blank" rel="noopener noreferrer">
                                   <i class="hgi hgi-stroke hgi-instagram"></i>
                               </a>
                               <span    class="tooltip">instagram</span>
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
                   <div class="card-confident">
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
           <section class="crafting" id="crafting">
            <div class="crafting-contain">
                <div class="header-craft">
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
                <div class="craft-content">
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
        <section class="about-us">
            <div class="about-us-content">
                <div class="bg-about">
                        <img src="assets/img/710fe7c6f6c6e89df3060695f62ff610.jpeg" alt="">
                    </div>
                    <div class="about-contain">
                        <div class="about-us-img">
                            <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
                        </div>
                        <div class="about-us-text">
                            <div class="about-deskripsi">
                                <p>At RAV Studio & Build, we believe that architecture is more than just structures—it’s about crafting timeless spaces that inspire and elevate everyday living. With a commitment to innovation, sustainability, and aesthetic excellence, we blend art and functionality to create designs that stand the test of time.
                                Our team of passionate architects and designers specializes in delivering bespoke solutions for residential, commercial, and urban projects. Every detail is meticulously considered, ensuring that our creations not only meet but exceed expectations.</p>
                            </div>
                        </div>
                    </div>
                </div>
         </section>

        <!-- -------------- PARTNERSHIP ---------------- -->
         <section class="partnership">
            <div class="partnership-content">
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
            <div class="elevating">
                <div class="elev-img">
                    <img src="./assets/img/6780.jpg" alt="">
                </div>
                <div class="elev-text">
                    <div class="elev-header">
                        <p>Elevating Spaces with Timeless Elegance</p>
                    </div>
                    <div class="elev-deskripsi">
                        <p>Bringing harmony between aesthetics, function, and character of the space.</p>
                    </div>
                </div>
            </div>
         </section>

        <!-- -------------- OTHER ELEMEN ---------------- -->
         <section class="quality">
            <div class="quality-us">
                <div class="quality-text">
                    <div class="first-text">
                        <p><hr>Building with Precision, Designing with Passion.</p>
                    </div>
                    <div class="sec-text">
                        <div class="sec-text-content">
                            <span>Harmony in</span>
                            <span class="sec">Design</span>
                        </div>
                        <div class="sec-text-content">
                            <span>Elegance in</span>
                            <span class="sec">Details.</span>
                        </div>
                    </div>
                    <div class="third-text">
                        <p>Beauty is not just about form, but also about how the space speaks. Every element in our designs is designed to create the perfect balance, aesthetics and functionality.</p>
                    </div>
                    <div class="quality-box">
                        <div class="quality-box-content">
                            <i class="hgi hgi-stroke hgi-blush-brush-02"></i>
                            <p>Elegant & Functional Design</p>
                        </div>
                        <div class="quality-box-content">
                            <i class="hgi hgi-stroke hgi-cube"></i>
                            <p>Quality Materials & Construction</p>
                        </div>
                        <div class="quality-box-content">
                            <i class="hgi hgi-stroke hgi-two-finger-05"></i>
                            <p>Personalization & Exclusivity</p>
                        </div>
                        <div class="quality-box-content">
                            <i class="hgi hgi-stroke hgi-briefcase-03"></i>
                            <p>Professional & Experienced</p>
                        </div>
                    </div>
                    <div class="quality-btn">
                        <a href="project.html">Explore Project <i class="hgi hgi-stroke hgi-arrow-right-02"></i></a>
                    </div>
                </div>
                <div class="quality-img">
                    <img src="./assets/img/3a6a930c190e93b374338f4c193226cd.jpeg" alt="">
                </div>
            </div>
         </section>

        <!-- -------------- SHORT PROJECT ---------------- -->
         <section class="short-project">
            <div class="project-short-contain">
                <div class="project-text">
                    <div class="main-title">
                        <div class="first-title">
                            <span class="first-color">Explore Boundless</span>
                            <span class="sec-color">Creativity</span>
                        </div>
                        <div class="sec-title">
                            <span>in Every</span>
                            <span class="sec-color">Design</span>
                        </div>
                    </div>
                    <div class="deskripsi-title">
                        <p id="typewriter-text">Every project is a journey towards uniqueness. Explore the innovative designs we create with precision and rigor. </p>
                        <hr>
                    </div>
                </div>
                <div class="opening-project">
                    <div class="left-opening">
                        <p>Recent Project from RAV Studio & Build</p>
                    </div>
                    <div class="right-opening">
                        <a href="">
                            <p>See More Project <i class="hgi hgi-stroke hgi-arrow-right-01"></i></p>
                        </a>
                    </div>
                </div>
                <!-- -------------- Recent Project ---------------- -->
                <div class="project-content">
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="main-content">
                        <a href="">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                        <div class="project-text-short">
                            <div class="title-project">
                                <p><?= htmlspecialchars($row['title']) ?></p>
                            </div>
                            <div class="deskripsi-project">
                                <p><?= htmlspecialchars($row['subtitle']) ?></p>
                            </div>
                        </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
         </section>

        <!-- -------------- TESTIMONIAL ---------------- -->
        <section class="testimoni">
            <div class="testimoni-content">
                <?php while ($row = mysqli_fetch_assoc($review_query)) : ?>
                <div class="card-testi-content">
                    <div class="card-title">
                        <img src="assets/img/Group.svg" alt="profile">
                        <div class="name-title">
                            <div class="name-client">
                                <p><?= htmlspecialchars($row['client_name']); ?></p>
                            </div>
                            <div class="client-project">
                                <p><?= htmlspecialchars($row['project_name']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="main-containt-testi">
                        <p><?= htmlspecialchars($row['review']); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="bg-grafik">
                <img src="assets/img/grafik bg.png" alt="grafik-bg">
            </div>
        </section>

        <!-- -------------- FAQ ---------------- -->
         <section class="faq" id="faq">
            <div class="faq-content">
                <div class="faq-title">
                    <p>Frequently Ask Question</p>
                </div>
                <div class="card-faq-content">
                    <div class="question">
                        <p>What services does your company offer?</p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p>We provide a range of architectural services, including building design, space planning, interior design consultation, renovation, and construction project management.</p>
                    </div>
                </div>
                <div class="card-faq-content">
                    <div class="question">
                        <p>Do you handle small-scale projects, or only large ones?</p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p>We cater to all types of projects, from private residences and commercial spaces to large-scale developments like office buildings and public facilities.</p>
                    </div>
                </div>
                <div class="card-faq-content">
                    <div class="question">
                        <p>How long does it take to complete a project?</p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p>Project duration depends on the complexity of the design, the scale of the building, and permit processing. The design phase may take a few weeks to months, while construction timelines vary based on the project size.</p>
                    </div>
                </div>
                <div class="card-faq-content">
                    <div class="question">
                        <p>How does the collaboration process work?</p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p>It starts with an initial consultation to understand the client's needs. We then create a concept design, refine it based on feedback, and proceed to detailed engineering and construction phases.</p>
                    </div>
                </div>
                <div class="card-faq-content">
                    <div class="question">
                        <p>Can you work with a contractor chosen by the client?</p>
                        <i class="hgi hgi-stroke hgi-arrow-down-01"></i>
                    </div>
                    <div class="answer">
                        <p>Absolutely. We can collaborate with your preferred contractor or recommend trusted partners we have worked with before.</p>
                    </div>
                </div>
            </div>
            <div class="grafik-bottom">
                <img src="assets/img/grafik2.png" alt="">
                <div class="deskripsi-grafik">
                    <p>Sustainable Structures Enduring Beauty</p>
                </div>
            </div>
         </section>
    </main>
    </div>

    <!-- --------------- FOOTER --------------- -->
    <footer>
        <div class="left-footer">
            <div class="logo-footer">
                <img src="assets/img/RAV LOGO.png" alt="RAV LOGO">
            </div>
            <div class="sosmed">
                <div class="sosmed-contain">
                    <i class="hgi hgi-stroke hgi-instagram"></i><p>ravstudio.id</p>
                </div>
                <div class="sosmed-contain">
                    <i class="hgi hgi-stroke hgi-mail-01"></i><p>ravstudio&build@outlook.co.id</p>
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

    <!-- ----- MAIN JS ----- -->
    <script src="assets/js/script.js"></script>

</body>
</html>