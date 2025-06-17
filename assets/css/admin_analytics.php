<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - RAV Studio Dashboard</title>
    <link rel="shortcut icon" href="assets/img/RAV LOGO.png" type="image/x-icon">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        ::-webkit-scrollbar{
            width: 10px;
            border-radius: 25px;
            display: none;
        }
        ::-webkit-scrollbar-track{
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb{
            background: #ccc;
            border-radius: 30px;
        }
        ::-webkit-scrollbar-thumb:hover{
            background: #bbb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #1e293b;
        }

        /* Navigation */
        nav#header {
            background: linear-gradient(135deg, #4a90a4 0%, #5f7c8a 25%, #3d6b7d 75%, #2c5763 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .nav-logo .nav-name {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(45deg, #ffffff, #e2e8f0);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        .nav-menu ul li {
            position: relative;
        }

        .nav-menu ul li a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-menu ul li a:hover,
        .nav-menu ul li a.active-link {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle::after {
            content: '▼';
            font-size: 0.8rem;
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .dropdown.active .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1001;
            margin-top: 0.5rem;
        }

        .dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            color: #1e293b !important;
            padding: 0.75rem 1rem !important;
            display: block !important;
            border-radius: 0 !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
            background: transparent !important;
            transform: none !important;
        }

        .dropdown-menu a:hover {
            background: #f8fafc !important;
            color: #4a90a4 !important;
            transform: none !important;
        }

        .dropdown-menu a:first-child {
            border-radius: 8px 8px 0 0 !important;
        }

        .dropdown-menu a:last-child {
            border-radius: 0 0 8px 8px !important;
        }

        .nav-button .btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .nav-button .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1.2rem;
            font-weight: 400;
        }

        /* Analytics Cards */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .analytics-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .analytics-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            transition: height 0.3s ease;
        }

        .analytics-card:hover::before {
            height: 8px;
        }

        .analytics-card.booking::before {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .analytics-card.project::before {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .analytics-card.booking .card-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .analytics-card.project .card-icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .analytics-card:hover .card-icon {
            transform: rotate(5deg) scale(1.1);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .card-description {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .card-button {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .analytics-card.booking .card-button {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .analytics-card.project .card-button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .card-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 164, 0.3);
        }

        .analytics-card.booking .card-button:hover {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .analytics-card.project .card-button:hover {
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        /* Stats Overview */
        .stats-overview {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            margin-bottom: 3rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .stats-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 16px;
            border-left: 4px solid #4a90a4;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4a90a4;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-menu ul {
                gap: 1rem;
            }
            
            .page-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 1rem;
            }
            
            .nav-menu ul {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <nav id="header">
        <div class="nav-logo">
            <p class="nav-name">RAV Studio Dashboard</p>
        </div>
        <div class="nav-menu" id="myNavMenu">
            <ul>
                <li><a href="admin_review.php" class="nav-link">
                        <i class="uil uil-star"></i> Review
                    </a></li>
                <li><a href="admin_faq.php" class="nav-link">
                        <i class="uil uil-question-circle"></i> FAQ
                    </a></li>
                <li><a href="admin_booking.php" class="nav-link">
                        <i class="uil uil-calendar-alt"></i> Booking
                    </a></li>
                <li><a href="admin_project.php" class="nav-link">
                        <i class="uil uil-briefcase"></i> Project
                    </a></li>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="uil uil-analytics"></i> Analytics
                    </a>
                    <div class="dropdown-menu">
                        <a href="statistik_booking.php">
                            <i class="uil uil-calendar-alt"></i> Statistik Booking
                        </a>
                        <a href="statistik_project.php">
                            <i class="uil uil-chart"></i> Statistik Project
                        </a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="nav-button">
            <a href="logout.php" class="btn">
                <i class="uil uil-signout"></i> Logout
            </a>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Analytics Overview</h1>
            <p class="page-subtitle">Choose your analytics view</p>
        </div>

        <div class="stats-overview">
            <h2 class="stats-title">
                <i class="uil uil-chart-line"></i>
                Quick Overview
            </h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">127</div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">43</div>
                    <div class="stat-label">Active Projects</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">89%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card booking">
                <div class="card-icon">
                    <i class="uil uil-calendar-alt"></i>
                </div>
                <h3 class="card-title">Statistik Booking</h3>
                <p class="card-description">
                    Analisis mendalam tentang tren booking, perilaku pelanggan, dan wawasan pendapatan. 
                    Lacak performa booking Anda dari waktu ke waktu dengan grafik dan laporan interaktif.
                </p>
                <a href="statistik_booking.php" class="card-button">
                    Lihat Statistik Booking
                    <i class="uil uil-arrow-right"></i>
                </a>
            </div>

            <div class="analytics-card project">
                <div class="card-icon">
                    <i class="uil uil-chart"></i>
                </div>
                <h3 class="card-title">Statistik Project</h3>
                <p class="card-description">
                    Analisis project komprehensif termasuk tingkat penyelesaian, timeline, dan 
                    pemanfaatan sumber daya. Monitor performa project dan produktivitas tim.
                </p>
                <a href="statistik_project.php" class="card-button">
                    Lihat Statistik Project
                    <i class="uil uil-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Close other dropdowns
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('active');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('active');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });
            
            // Close dropdown when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });
        });

        // Card hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.analytics-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>