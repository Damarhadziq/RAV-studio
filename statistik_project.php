<?php
session_start();

// Timeout duration (2 jam)
$timeout_duration = 2 * 60 * 60;

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $current_page = basename($_SERVER['PHP_SELF']);
    header("Location: login.php?redirect=" . $current_page);
    exit();
}

// Cek session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    $current_page = basename($_SERVER['PHP_SELF']);
    header("Location: login.php?redirect=" . $current_page);
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

$localhost = "localhost";
$username = "root";
$password = "";
$database = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Function untuk mendapatkan data dengan filter
function getFilteredData($conn, $dateFrom = null, $dateTo = null, $status = null, $projectType = null) {
    $sql = "SELECT * FROM projects WHERE 1=1";
    $params = [];
    
    if ($dateFrom && $dateTo) {
        $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    
    if ($status && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($projectType && $projectType !== 'all') {
        $sql .= " AND project_type = ?";
        $params[] = $projectType;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($conn, $sql);
        }
    } else {
        $result = mysqli_query($conn, $sql);
    }
    
    return $result;
}

// Ambil parameter filter
$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;
$statusFilter = $_GET['status'] ?? 'all';
$projectTypeFilter = $_GET['project_type'] ?? 'all';

// Ambil data dengan filter
$result = getFilteredData($conn, $dateFrom, $dateTo, $statusFilter, $projectTypeFilter);
$projects = [];
while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

// 🔹 1. STATISTIK UMUM
$totalProjects = count($projects);
$konsultasiCount = count(array_filter($projects, fn($p) => $p['status'] === 'konsultasi'));
$desainCount = count(array_filter($projects, fn($p) => $p['status'] === 'desain'));
$revisiCount = count(array_filter($projects, fn($p) => $p['status'] === 'revisi'));
$finalCount = count(array_filter($projects, fn($p) => $p['status'] === 'final'));

// Proyek selesai (ada actual_completion)
$completedProjects = array_filter($projects, fn($p) => !empty($p['actual_completion']));
$completedCount = count($completedProjects);

// Proyek aktif (ada start_date tapi belum ada actual_completion)
$activeProjects = array_filter($projects, fn($p) => !empty($p['start_date']) && empty($p['actual_completion']));
$activeCount = count($activeProjects);

// Rata-rata luas bangunan dan tanah
$buildingAreas = array_filter(array_column($projects, 'building_area'), fn($area) => $area > 0);
$landAreas = array_filter(array_column($projects, 'land_area'), fn($area) => $area > 0);
$budgets = array_filter(array_column($projects, 'estimated_budget'), fn($budget) => $budget > 0);

$avgBuildingArea = count($buildingAreas) > 0 ? round(array_sum($buildingAreas) / count($buildingAreas), 2) : 0;
$avgLandArea = count($landAreas) > 0 ? round(array_sum($landAreas) / count($landAreas), 2) : 0;
$avgBudget = count($budgets) > 0 ? round(array_sum($budgets) / count($budgets), 0) : 0;

// Klien dengan proyek terbanyak
$clientFrequency = [];
foreach ($projects as $project) {
    $client = $project['client_name'];
    $clientFrequency[$client] = ($clientFrequency[$client] ?? 0) + 1;
}
arsort($clientFrequency);
$topClient = array_key_first($clientFrequency);
$topClientCount = $clientFrequency[$topClient] ?? 0;

// 🔹 2. TREN WAKTU
// Data untuk grafik tren (12 bulan terakhir)
$trendData = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    $count = 0;
    $completedInMonth = 0;
    
    foreach ($projects as $project) {
        // Proyek masuk per bulan
        if (date('Y-m', strtotime($project['created_at'])) === $month) {
            $count++;
        }
        // Proyek selesai per bulan
        if (!empty($project['actual_completion']) && date('Y-m', strtotime($project['actual_completion'])) === $month) {
            $completedInMonth++;
        }
    }
    $trendData[] = [
        'month' => $monthName, 
        'incoming' => $count,
        'completed' => $completedInMonth
    ];
}

// Durasi rata-rata proyek
$durations = [];
$delays = [];
foreach ($projects as $project) {
    if (!empty($project['start_date']) && !empty($project['actual_completion'])) {
        $start = new DateTime($project['start_date']);
        $end = new DateTime($project['actual_completion']);
        $duration = $start->diff($end)->days;
        $durations[] = $duration;
        
        // Hitung keterlambatan
        if (!empty($project['target_completion'])) {
            $target = new DateTime($project['target_completion']);
            if ($end > $target) {
                $delay = $target->diff($end)->days;
                $delays[] = $delay;
            }
        }
    }
}
$avgDuration = count($durations) > 0 ? round(array_sum($durations) / count($durations), 1) : 0;
$avgDelay = count($delays) > 0 ? round(array_sum($delays) / count($delays), 1) : 0;

// 🔹 3. ANALISIS STATUS PROYEK
$statusData = [
    'konsultasi' => $konsultasiCount,
    'desain' => $desainCount,
    'revisi' => $revisiCount,
    'final' => $finalCount
];
$topStatus = array_search(max($statusData), $statusData);

// 🔹 4. JENIS PROYEK & KONSEP BANGUNAN
$projectTypes = [];
$buildingConcepts = [];
$locationAnalysis = [];

foreach ($projects as $project) {
    // Jenis proyek
    $type = $project['project_type'];
    $projectTypes[$type] = ($projectTypes[$type] ?? 0) + 1;
    
    // Konsep bangunan
    $concept = $project['building_concept'];
    if (!empty($concept)) {
        $buildingConcepts[$concept] = ($buildingConcepts[$concept] ?? 0) + 1;
    }
    
    // Lokasi
    $location = $project['location'];
    if (!empty($location)) {
        $locationAnalysis[$location] = ($locationAnalysis[$location] ?? 0) + 1;
    }
}
arsort($projectTypes);
arsort($buildingConcepts);
arsort($locationAnalysis);

// 🔹 5. WAKTU & DURASI PROYEK
// Waktu dari booking ke start
$bookingToStartDurations = [];
foreach ($projects as $project) {
    if (!empty($project['booking_date']) && !empty($project['start_date'])) {
        $booking = new DateTime($project['booking_date']);
        $start = new DateTime($project['start_date']);
        $duration = $booking->diff($start)->days;
        $bookingToStartDurations[] = $duration;
    }
}
$avgBookingToStart = count($bookingToStartDurations) > 0 ? round(array_sum($bookingToStartDurations) / count($bookingToStartDurations), 1) : 0;

// Proyek tepat waktu vs terlambat
$onTimeProjects = 0;
$lateProjects = 0;
foreach ($projects as $project) {
    if (!empty($project['target_completion']) && !empty($project['actual_completion'])) {
        $target = new DateTime($project['target_completion']);
        $actual = new DateTime($project['actual_completion']);
        if ($actual <= $target) {
            $onTimeProjects++;
        } else {
            $lateProjects++;
        }
    }
}

// 🔹 6. KONVERSI BOOKING
$bookingCount = count(array_filter($projects, fn($p) => !empty($p['booking_date'])));
$startedCount = count(array_filter($projects, fn($p) => !empty($p['start_date'])));
$conversionRate = $bookingCount > 0 ? round(($startedCount / $bookingCount) * 100, 1) : 0;

// Data untuk grafik bulanan tahun ini
$currentYear = date('Y');
$monthlyTrendThisYear = [];
for ($month = 1; $month <= 12; $month++) {
    $monthKey = $currentYear . '-' . sprintf('%02d', $month);
    $monthName = date('M', mktime(0, 0, 0, $month, 1));
    $count = 0;
    foreach ($projects as $project) {
        if (date('Y-m', strtotime($project['created_at'])) === $monthKey) {
            $count++;
        }
    }
    $monthlyTrendThisYear[] = ['month' => $monthName, 'count' => $count];
}

// Growth rate calculation
$thisMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));
$thisMonthCount = 0;
$lastMonthCount = 0;

foreach ($projects as $project) {
    $projectMonth = date('Y-m', strtotime($project['created_at']));
    if ($projectMonth === $thisMonth) {
        $thisMonthCount++;
    } elseif ($projectMonth === $lastMonth) {
        $lastMonthCount++;
    }
}

$growthRate = $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0;

// Ambil semua project types untuk filter
$allProjectTypes = mysqli_query($conn, "SELECT DISTINCT project_type FROM projects ORDER BY project_type");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RAV Studio - Projects Analytics Dashboard</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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

        .nav-menu ul li a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu ul li a:hover,
        .nav-menu ul li a.active-link {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
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
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .filter-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .filter-input,
        .filter-select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: white;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #4a90a4;
            box-shadow: 0 0 0 3px rgba(74, 144, 164, 0.1);
        }

        .filter-button {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .filter-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 164, 0.3);
        }

        .reset-button {
            background: #6b7280;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .reset-button:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
        }

        .stat-card.success::before {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-card.warning::before {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-card.danger::before {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-card.info::before {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-card.info .stat-icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .stat-change {
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: #10b981;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        /* Chart Containers */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        /* Full Width Charts */
        .full-width-chart {
            grid-column: 1 / -1;
        }

        .full-width-chart .chart-wrapper {
            height: 400px;
        }

        /* Top Lists */
        .top-list {
            margin-top: 1rem;
        }

        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 3px solid #4a90a4;
        }

        .top-item-name {
            font-weight: 600;
            color: #1e293b;
        }

        .top-item-count {
            background: #4a90a4;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .metric-item {
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #4a90a4;
            text-align: center;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #4a90a4;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        /* Summary Section */
        .filter-summary {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #4a90a4;
            font-size: 0.9rem;
        }

        .summary-item strong {
            color: #1e293b;
            display: block;
            margin-bottom: 0.5rem;
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
                        <i class="uil uil-building"></i> Project
                    </a></li>
                <li class="dropdown">
                    <a href="admin_project.php" class="nav-link dropdown-toggle">
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
            <h1 class="page-title">Projects Analytics Dashboard</h1>
            <p class="page-subtitle">Analisis mendalam data proyek dan performa bisnis RAV Studio</p>
        </div>

        <!-- <div class="row mb-4">
            <div class="col-12 text-center">
                <button type="button" class="btn btn-secondary me-2" onclick="printReport()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button type="button" class="btn btn-success" onclick="exportCSV()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
        </div> -->

        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">
                <i class="uil uil-filter"></i> Filter & Pencarian
            </h3>
            <form method="GET" action="" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label">Tanggal Mulai</label>
                        <input type="date" name="date_from" class="filter-input" 
                               value="<?= htmlspecialchars($dateFrom ?? '') ?>" id="dateFrom">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Tanggal Akhir</label>
                        <input type="date" name="date_to" class="filter-input" 
                               value="<?= htmlspecialchars($dateTo ?? '') ?>" id="dateTo">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-select" id="statusFilter">
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="konsultasi" <?= $statusFilter === 'konsultasi' ? 'selected' : '' ?>>Konsultasi</option>
                            <option value="desain" <?= $statusFilter === 'desain' ? 'selected' : '' ?>>Desain</option>
                            <option value="revisi" <?= $statusFilter === 'revisi' ? 'selected' : '' ?>>Revisi</option>
                            <option value="final" <?= $statusFilter === 'final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Jenis Proyek</label>
                        <select name="project_type" class="filter-select" id="projectTypeFilter">
                            <option value="all" <?= $projectTypeFilter === 'all' ? 'selected' : '' ?>>Semua Proyek</option>
                            <?php mysqli_data_seek($allProjectTypes, 0); ?>
                            <?php while ($typeRow = mysqli_fetch_assoc($allProjectTypes)): ?>
                                <option value="<?= htmlspecialchars($typeRow['project_type']) ?>" 
                                        <?= $projectTypeFilter === $typeRow['project_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($typeRow['project_type']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="filter-button">
                            <i class="uil uil-search"></i> Analisis
                        </button>
                    </div>
                    <div class="filter-group">
                        <button type="button" class="reset-button" onclick="resetFilters()">
                            <i class="uil uil-refresh"></i> Reset
                        </button>
                        </a>
                    </div>
                </div>
            </form>
            
            <?php if($dateFrom || $dateTo || $statusFilter !== 'all' || $projectTypeFilter !== 'all'): ?>
            <div class="filter-summary">
                <h4 style="margin-bottom: 1rem; color: #1e293b;">Ringkasan Filter:</h4>
                <div class="summary-grid">
                    <?php if($dateFrom && $dateTo): ?>
                        <div class="summary-item">
                            <strong>Periode</strong>
                            <?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?>
                        </div>
                    <?php endif; ?>
                    <?php if($statusFilter !== 'all'): ?>
                        <div class="summary-item">
                            <strong>Status</strong>
                            <?= ucfirst($statusFilter) ?>
                        </div>
                    <?php endif; ?>
                    <?php if($projectTypeFilter !== 'all'): ?>
                        <div class="summary-item">
                            <strong>Jenis Proyek</strong>
                            <?= $projectTypeFilter ?>
                        </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <strong>Total Data</strong>
                        <?= $totalProjects ?> proyek ditemukan
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="uil uil-folder"></i>
                </div>
                <div class="stat-number"><?= $totalProjects ?></div>
                <div class="stat-label">Total Proyek</div>
                <?php if($growthRate != 0): ?>
                    <div class="stat-change <?= $growthRate > 0 ? 'positive' : 'negative' ?>">
                        <?= $growthRate > 0 ? '+' : '' ?><?= $growthRate ?>% dari bulan lalu
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="uil uil-check-circle"></i>
                </div>
                <div class="stat-number"><?= $completedCount ?></div>
                <div class="stat-label">Proyek Selesai</div>
                <?php if($totalProjects > 0): ?>
                    <div class="stat-change">
                        <?= round(($completedCount / $totalProjects) * 100, 1) ?>% dari total
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="uil uil-play-circle"></i>
                </div>
                <div class="stat-number"><?= $activeCount ?></div>
                <div class="stat-label">Proyek Aktif</div>
                <?php if($totalProjects > 0): ?>
                    <div class="stat-change">
                        <?= round(($activeCount / $totalProjects) * 100, 1) ?>% dari total
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="uil uil-percentage"></i>
                </div>
                <div class="stat-number"><?= $conversionRate ?>%</div>
                <div class="stat-label">Tingkat Konversi</div>
                <div class="stat-change">
                    <?= $startedCount ?> dari <?= $bookingCount ?> booking
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="uil uil-clock"></i>
                </div>
                <div class="stat-number"><?= $avgDuration ?></div>
                <div class="stat-label">Rata-rata Durasi (Hari)</div>
                <?php if($avgDelay > 0): ?>
                    <div class="stat-change negative">
                        Rata-rata terlambat <?= $avgDelay ?> hari
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="uil uil-money-bill"></i>
                </div>
                <div class="stat-number">Rp <?= number_format($avgBudget / 1000000, 1) ?>M</div>
                <div class="stat-label">Rata-rata Budget</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Status Distribution -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-chart-pie"></i> Distribusi Status Proyek
                </h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="metrics-grid">
                    <div class="metric-item">
                        <div class="metric-value"><?= $konsultasiCount ?></div>
                        <div class="metric-label">Konsultasi</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value"><?= $desainCount ?></div>
                        <div class="metric-label">Desain</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value"><?= $revisiCount ?></div>
                        <div class="metric-label">Revisi</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value"><?= $finalCount ?></div>
                        <div class="metric-label">Final</div>
                    </div>
                </div>
            </div>

            <!-- Project Types -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-chart-bar"></i> Jenis Proyek Terpopuler
                </h3>
                <div class="chart-wrapper">
                    <canvas id="projectTypeChart"></canvas>
                </div>
                <div class="top-list">
                    <?php $i = 1; foreach(array_slice($projectTypes, 0, 5, true) as $type => $count): ?>
                        <div class="top-item">
                            <span class="top-item-name">#<?= $i ?> <?= htmlspecialchars($type) ?></span>
                            <span class="top-item-count"><?= $count ?></span>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>

            <!-- Trend Chart -->
            <div class="chart-container full-width-chart">
                <h3 class="chart-title">
                    <i class="uil uil-chart-line"></i> Tren Proyek 12 Bulan Terakhir
                </h3>
                <div class="chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Building Concepts -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-home"></i> Konsep Bangunan Favorit
                </h3>
                <div class="chart-wrapper">
                    <canvas id="conceptChart"></canvas>
                </div>
                <div class="top-list">
                    <?php $i = 1; foreach(array_slice($buildingConcepts, 0, 5, true) as $concept => $count): ?>
                        <div class="top-item">
                            <span class="top-item-name">#<?= $i ?> <?= htmlspecialchars($concept) ?></span>
                            <span class="top-item-count"><?= $count ?></span>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>

            <!-- Location Analysis -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-map-marker"></i> Sebaran Lokasi Proyek
                </h3>
                <div class="chart-wrapper">
                    <canvas id="locationChart"></canvas>
                </div>
                <div class="top-list">
                    <?php $i = 1; foreach(array_slice($locationAnalysis, 0, 5, true) as $location => $count): ?>
                        <div class="top-item">
                            <span class="top-item-name">#<?= $i ?> <?= htmlspecialchars($location) ?></span>
                            <span class="top-item-count"><?= $count ?></span>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="chart-container" style="margin-bottom: 2rem;">
            <h3 class="chart-title">
                <i class="uil uil-tachometer-fast"></i> Metrik Performa Proyek
            </h3>
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-value"><?= $avgBuildingArea ?> m²</div>
                    <div class="metric-label">Rata-rata Luas Bangunan</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value"><?= $avgLandArea ?> m²</div>
                    <div class="metric-label">Rata-rata Luas Tanah</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value"><?= $avgBookingToStart ?> hari</div>
                    <div class="metric-label">Booking ke Mulai Kerja</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value"><?= $onTimeProjects ?>/<?= $lateProjects ?></div>
                    <div class="metric-label">Tepat Waktu / Terlambat</div>
                </div>
            </div>
            
            <?php if($topClient): ?>
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 12px;">
                <h4 style="color: #1e293b; margin-bottom: 1rem;">🏆 Klien Terbaik</h4>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; font-size: 1.1rem;"><?= htmlspecialchars($topClient) ?></span>
                    <span style="background: #4a90a4; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                        <?= $topClientCount ?> proyek
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
            // Chart.js Configuration
            Chart.defaults.font.family = 'Inter';
            Chart.defaults.color = '#64748b';

            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Konsultasi', 'Desain', 'Revisi', 'Final'],
                    datasets: [{
                        data: [<?= $konsultasiCount ?>, <?= $desainCount ?>, <?= $revisiCount ?>, <?= $finalCount ?>],
                        backgroundColor: ['#f59e0b', '#3b82f6', '#ef4444', '#10b981'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // Project Type Chart
            const projectTypeCtx = document.getElementById('projectTypeChart').getContext('2d');
            new Chart(projectTypeCtx, {
                type: 'bar',
                data: {
                    labels: [<?php foreach(array_slice($projectTypes, 0, 6, true) as $type => $count): ?>'<?= addslashes($type) ?>',<?php endforeach; ?>],
                    datasets: [{
                        label: 'Jumlah Proyek',
                        data: [<?php foreach(array_slice($projectTypes, 0, 6, true) as $type => $count): ?><?= $count ?>,<?php endforeach; ?>],
                        backgroundColor: '#4a90a4',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: [<?php foreach($trendData as $data): ?>'<?= $data['month'] ?>',<?php endforeach; ?>],
                    datasets: [{
                        label: 'Proyek Masuk',
                        data: [<?php foreach($trendData as $data): ?><?= $data['incoming'] ?>,<?php endforeach; ?>],
                        borderColor: '#4a90a4',
                        backgroundColor: 'rgba(74, 144, 164, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Proyek Selesai',
                        data: [<?php foreach($trendData as $data): ?><?= $data['completed'] ?>,<?php endforeach; ?>],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Building Concept Chart
            const conceptCtx = document.getElementById('conceptChart').getContext('2d');
            new Chart(conceptCtx, {
                type: 'pie',
                data: {
                    labels: [<?php foreach(array_slice($buildingConcepts, 0, 5, true) as $concept => $count): ?>'<?= addslashes($concept) ?>',<?php endforeach; ?>],
                    datasets: [{
                        data: [<?php foreach(array_slice($buildingConcepts, 0, 5, true) as $concept => $count): ?><?= $count ?>,<?php endforeach; ?>],
                        backgroundColor: ['#4a90a4', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // Location Chart
            const locationCtx = document.getElementById('locationChart').getContext('2d');
            new Chart(locationCtx, {
                type: 'bar',
                data: {
                    labels: [<?php foreach(array_slice($locationAnalysis, 0, 5, true) as $location => $count): ?>'<?= addslashes($location) ?>',<?php endforeach; ?>],
                    datasets: [{
                        label: 'Jumlah Proyek',
                        data: [<?php foreach(array_slice($locationAnalysis, 0, 5, true) as $location => $count): ?><?= $count ?>,<?php endforeach; ?>],
                        backgroundColor: ['#4a90a4', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Auto-refresh functionality
            function autoRefresh() {
                const params = new URLSearchParams(window.location.search);
                if (params.toString()) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 300000); // Refresh every 5 minutes if filters are applied
                }
            }

            // Reset Filters Function
            function resetFilters() {
                // Reset semua input field
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('projectTypeFilter').value = 'all';

            // Submit form untuk reload data dengan filter yang sudah direset
            document.getElementById('filterForm').submit();
            }

            // Initialize auto-refresh
            autoRefresh();

            // Filter form validation
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                const dateFrom = document.getElementById('dateFrom').value;
                const dateTo = document.getElementById('dateTo').value;
                
                if (dateFrom && dateTo && dateFrom > dateTo) {
                    e.preventDefault();
                    alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                    return false;
                }
            });

        // Export functionality (optional)
        function exportData() {
            // Siapkan data CSV
            var csvData = [];
            
            // Header CSV
            csvData.push(['Metric', 'Value', 'Description', 'Date Generated']);
            
            // Helper function untuk escape CSV fields dengan benar
            function escapeCSVField(field) {
                if (field === null || field === undefined) {
                    return '';
                }
                
                // Convert ke string
                var str = String(field);
                
                // Jika field mengandung koma, newline, atau double quote, wrap dengan double quotes
                if (str.includes(',') || str.includes('\n') || str.includes('\r') || str.includes('"')) {
                    // Escape internal double quotes dengan double double quotes
                    str = str.replace(/"/g, '""');
                    return '"' + str + '"';
                }
                
                return str;
            }
            
            // Helper function untuk format angka dalam Bahasa Indonesia
            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }
            
            // Get current date in Indonesian format
            var currentDate = new Date().toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric'
            });
            
            // Ambil data statistik dari PHP
            var stats = [
                ['Total Proyek', <?= $totalProjects ?>, 'Jumlah total proyek dalam periode filter', currentDate],
                ['Proyek Selesai', <?= $completedCount ?>, '<?= $totalProjects > 0 ? round(($completedCount / $totalProjects) * 100, 1) : 0 ?>% dari total', currentDate],
                ['Proyek Aktif', <?= $activeCount ?>, '<?= $totalProjects > 0 ? round(($activeCount / $totalProjects) * 100, 1) : 0 ?>% dari total', currentDate],
                ['Tingkat Konversi', '<?= $conversionRate ?>%', '<?= $startedCount ?> dari <?= $bookingCount ?> booking', currentDate],
                ['Rata-rata Durasi', '<?= $avgDuration ?> hari', 'Durasi rata-rata penyelesaian proyek', currentDate],
                ['Rata-rata Budget', 'Rp <?= number_format($avgBudget / 1000000, 1) ?>M', 'Budget rata-rata proyek', currentDate],
                ['Rata-rata Luas Bangunan', '<?= $avgBuildingArea ?> m²', 'Luas bangunan rata-rata', currentDate],
                ['Rata-rata Luas Tanah', '<?= $avgLandArea ?> m²', 'Luas tanah rata-rata', currentDate]
            ];
            
            // Tambahkan data statistik
            stats.forEach(stat => csvData.push(stat));
            
            // Tambahkan separator
            csvData.push(['', '', '', '']);
            csvData.push(['DISTRIBUSI STATUS', '', '', '']);
            
            // Data status
            csvData.push(['Konsultasi', <?= $konsultasiCount ?>, 'Proyek dalam tahap konsultasi', currentDate]);
            csvData.push(['Desain', <?= $desainCount ?>, 'Proyek dalam tahap desain', currentDate]);
            csvData.push(['Revisi', <?= $revisiCount ?>, 'Proyek dalam tahap revisi', currentDate]);
            csvData.push(['Final', <?= $finalCount ?>, 'Proyek dalam tahap final', currentDate]);
            
            // Tambahkan separator
            csvData.push(['', '', '', '']);
            csvData.push(['TOP JENIS PROYEK', '', '', '']);
            
            // Data jenis proyek (dari PHP)
            <?php foreach(array_slice($projectTypes, 0, 10, true) as $type => $count): ?>
            csvData.push(['<?= addslashes($type) ?>', <?= $count ?>, 'Jumlah proyek <?= addslashes($type) ?>', currentDate]);
            <?php endforeach; ?>
            
            // Tambahkan separator
            csvData.push(['', '', '', '']);
            csvData.push(['TOP KONSEP BANGUNAN', '', '', '']);
            
            // Data konsep bangunan (dari PHP)
            <?php foreach(array_slice($buildingConcepts, 0, 10, true) as $concept => $count): ?>
            csvData.push(['<?= addslashes($concept) ?>', <?= $count ?>, 'Jumlah proyek dengan konsep <?= addslashes($concept) ?>', currentDate]);
            <?php endforeach; ?>
            
            // Tambahkan separator
            csvData.push(['', '', '', '']);
            csvData.push(['TOP LOKASI PROYEK', '', '', '']);
            
            // Data lokasi (dari PHP)
            <?php foreach(array_slice($locationAnalysis, 0, 10, true) as $location => $count): ?>
            csvData.push(['<?= addslashes($location) ?>', <?= $count ?>, 'Jumlah proyek di <?= addslashes($location) ?>', currentDate]);
            <?php endforeach; ?>
            
            // Tambahkan informasi filter
            csvData.push(['', '', '', '']);
            csvData.push(['INFORMASI FILTER', '', '', '']);
            <?php if($dateFrom && $dateTo): ?>
            csvData.push(['Periode Filter', '<?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?>', 'Periode data yang dianalisis', currentDate]);
            <?php endif; ?>
            <?php if($statusFilter !== 'all'): ?>
            csvData.push(['Status Filter', '<?= ucfirst($statusFilter) ?>', 'Filter status yang diterapkan', currentDate]);
            <?php endif; ?>
            <?php if($projectTypeFilter !== 'all'): ?>
            csvData.push(['Jenis Proyek Filter', '<?= $projectTypeFilter ?>', 'Filter jenis proyek yang diterapkan', currentDate]);
            <?php endif; ?>
            
            // Tambahkan data tren 12 bulan
            csvData.push(['', '', '', '']);
            csvData.push(['TREN 12 BULAN TERAKHIR', '', '', '']);
            csvData.push(['Bulan', 'Proyek Masuk', 'Proyek Selesai', 'Tanggal Export']);
            <?php foreach($trendData as $data): ?>
            csvData.push(['<?= $data['month'] ?>', <?= $data['incoming'] ?>, <?= $data['completed'] ?>, currentDate]);
            <?php endforeach; ?>
            
            // Convert ke CSV string dengan proper escaping
            var csvString = csvData.map(row => 
                row.map(field => escapeCSVField(field)).join(',')
            ).join('\r\n'); // Gunakan \r\n untuk kompatibilitas Windows/Excel
            
            // Tambahkan BOM untuk proper UTF-8 encoding di Excel
            var BOM = '\uFEFF';
            csvString = BOM + csvString;
            
            // Buat dan download file
            var blob = new Blob([csvString], { 
                type: 'text/csv;charset=utf-8;' 
            });
            
            // Alternatif untuk browser yang tidak support Blob constructor
            if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveOrOpenBlob(blob, filename);
                return;
            }
            
            var link = document.createElement('a');
            
            if (link.download !== undefined) {
                var url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                
                // Buat nama file dengan timestamp dan filter info
                var timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
                var filterInfo = '';
                <?php if($statusFilter !== 'all'): ?>
                filterInfo += '_<?= $statusFilter ?>';
                <?php endif; ?>
                <?php if($projectTypeFilter !== 'all'): ?>
                filterInfo += '_' + '<?= str_replace(' ', '', $projectTypeFilter) ?>';
                <?php endif; ?>
                
                var filename = 'RAV_Studio_Analytics${filterInfo}_${timestamp}.csv';
                
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Cleanup
                setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 100);
                
                // Show success message
                alert('✅ File CSV berhasil didownload!\n\nFile: ' + filename + '\n\nFile berisi data analytics lengkap dan telah dioptimalkan untuk Excel.\n\nTips: Jika teks masih tidak tampil dengan benar di Excel, coba buka file menggunakan "Data" > "From Text/CSV" dan pilih encoding UTF-8.');
            } else {
                alert('❌ Browser Anda tidak mendukung download file CSV.\n\nSilakan gunakan browser modern seperti Chrome, Firefox, atau Edge.');
            }
        }

        // 2. Juga perbaiki fungsi untuk button action (sudah ada di kode, tapi pastikan exportData() dipanggil dengan benar):
        // Ganti bagian ini juga:
        document.addEventListener('DOMContentLoaded', function() {
            const pageHeader = document.querySelector('.page-header');
            const actionButtons = document.createElement('div');
            actionButtons.className = 'action-buttons';
            actionButtons.style.cssText = 'margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;';

            actionButtons.innerHTML = `
                <button onclick="printReport()" class="filter-button" style="background: #6b7280; min-width: 140px;">
                    <i class="uil uil-print"></i> Print Report
                </button>
                <button onclick="exportData()" class="filter-button" style="background: #059669; min-width: 140px;">
                    <i class="uil uil-download-alt"></i> Export CSV
                </button>
            `;
            
            pageHeader.appendChild(actionButtons);
        });

        // 3. Tambahkan juga fungsi untuk export data proyek detail (optional):
        function exportProjectDetails() {
            // Export detail semua proyek
            var csvData = [];
            csvData.push(['ID', 'Client Name', 'Project Type', 'Status', 'Building Concept', 'Location', 'Building Area', 'Land Area', 'Budget', 'Created Date', 'Start Date', 'Target Completion', 'Actual Completion']);
            
            // Data proyek dari PHP (perlu ditambahkan di PHP jika ingin export detail)
            <?php foreach($projects as $project): ?>
            csvData.push([
                '<?= $project['id'] ?? '' ?>',
                '<?= addslashes($project['client_name'] ?? '') ?>',
                '<?= addslashes($project['project_type'] ?? '') ?>',
                '<?= addslashes($project['status'] ?? '') ?>',
                '<?= addslashes($project['building_concept'] ?? '') ?>',
                '<?= addslashes($project['location'] ?? '') ?>',
                '<?= $project['building_area'] ?? 0 ?>',
                '<?= $project['land_area'] ?? 0 ?>',
                '<?= $project['estimated_budget'] ?? 0 ?>',
                '<?= $project['created_at'] ?? '' ?>',
                '<?= $project['start_date'] ?? '' ?>',
                '<?= $project['target_completion'] ?? '' ?>',
                '<?= $project['actual_completion'] ?? '' ?>'
            ]);
            <?php endforeach; ?>
            
            // Convert and download
            var csvString = csvData.map(row => 
                row.map(field => "${field}").join(',')
            ).join('\n');
            
            var BOM = '\uFEFF';
            csvString = BOM + csvString;
            
            var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `RAV_Studio_Project_Details_${new Date().toISOString().slice(0, 10)}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        // Print functionality
        function printReport() {
            window.print();
        }

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

    <!-- Print Styles -->
    <style media="print">
        nav, .filter-section, .nav-button { display: none !important; }
        .main-container { padding: 1rem !important; max-width: none !important; }
        .chart-container { break-inside: avoid; margin-bottom: 1rem !important; }
        .stats-container { break-inside: avoid; }
        body { background: white !important; }
    </style>
</body>
</html>

<?php
mysqli_close($conn);
?>