<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

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
    $sql = "SELECT * FROM booking WHERE 1=1";
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
$bookings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}

// Statistik Umum
$totalBookings = count($bookings);
$pendingCount = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
$progressCount = count(array_filter($bookings, fn($b) => $b['status'] === 'progress'));
$completedCount = count(array_filter($bookings, fn($b) => $b['status'] === 'completed'));
$cancelCount = count(array_filter($bookings, fn($b) => $b['status'] === 'cancel'));

// Rata-rata booking per bulan (dari data yang ada)
$monthlyData = [];
foreach ($bookings as $booking) {
    $month = date('Y-m', strtotime($booking['created_at']));
    $monthlyData[$month] = ($monthlyData[$month] ?? 0) + 1;
}
$avgBookingsPerMonth = count($monthlyData) > 0 ? round(array_sum($monthlyData) / count($monthlyData), 1) : 0;

// Data untuk grafik tren waktu (6 bulan terakhir)
$trendData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    $count = 0;
    foreach ($bookings as $booking) {
        if (date('Y-m', strtotime($booking['created_at'])) === $month) {
            $count++;
        }
    }
    $trendData[] = ['month' => $monthName, 'count' => $count];
}

// Analisis jenis proyek
$projectTypes = [];
foreach ($bookings as $booking) {
    $type = $booking['project_type'];
    $projectTypes[$type] = ($projectTypes[$type] ?? 0) + 1;
}
arsort($projectTypes);

// Data booking berdasarkan hari dalam seminggu
$weekdayData = [
    'Minggu' => 0, 'Senin' => 0, 'Selasa' => 0, 'Rabu' => 0,
    'Kamis' => 0, 'Jumat' => 0, 'Sabtu' => 0
];
$dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
foreach ($bookings as $booking) {
    $dayIndex = date('w', strtotime($booking['created_at']));
    $weekdayData[$dayNames[$dayIndex]]++;
}

// Analisis jam booking (distribusi berdasarkan jam dalam sehari)
$hourlyData = array_fill(0, 24, 0);
foreach ($bookings as $booking) {
    $hour = (int)date('H', strtotime($booking['created_at']));
    $hourlyData[$hour]++;
}

// Analisis email domain (untuk memahami jenis klien)
$emailDomains = [];
foreach ($bookings as $booking) {
    $domain = substr(strrchr($booking['email'], "@"), 1);
    $emailDomains[$domain] = ($emailDomains[$domain] ?? 0) + 1;
}
arsort($emailDomains);
$topDomains = array_slice($emailDomains, 0, 5, true);

// Analisis klien berdasarkan frekuensi booking
$clientFrequency = [];
foreach ($bookings as $booking) {
    $email = $booking['email'];
    $clientFrequency[$email] = ($clientFrequency[$email] ?? 0) + 1;
}
arsort($clientFrequency);
$repeatClients = count(array_filter($clientFrequency, fn($count) => $count > 1));
$newClients = count(array_filter($clientFrequency, fn($count) => $count === 1));

// Statistik success dan conversion rate
$successfulBookings = $completedCount;
$totalNonCancelBookings = $totalBookings - $cancelCount;
$successRate = $totalNonCancelBookings > 0 ? round(($successfulBookings / $totalNonCancelBookings) * 100, 1) : 0;
$cancelRate = $totalBookings > 0 ? round(($cancelCount / $totalBookings) * 100, 1) : 0;

// Analisis panjang pesan klien (indikator minat/kompleksitas)
$messageLengths = [];
$totalMessageLength = 0;
$messageCount = 0;
foreach ($bookings as $booking) {
    if (!empty($booking['message'])) {
        $length = strlen($booking['message']);
        $messageLengths[] = $length;
        $totalMessageLength += $length;
        $messageCount++;
    }
}
$avgMessageLength = $messageCount > 0 ? round($totalMessageLength / $messageCount) : 0;

// Tren booking bulanan untuk tahun ini
$currentYear = date('Y');
$monthlyTrendThisYear = [];
for ($month = 1; $month <= 12; $month++) {
    $monthKey = $currentYear . '-' . sprintf('%02d', $month);
    $monthName = date('M', mktime(0, 0, 0, $month, 1));
    $count = 0;
    foreach ($bookings as $booking) {
        if (date('Y-m', strtotime($booking['created_at'])) === $monthKey) {
            $count++;
        }
    }
    $monthlyTrendThisYear[] = ['month' => $monthName, 'count' => $count];
}

// Ambil semua project types untuk filter
$allProjectTypes = mysqli_query($conn, "SELECT DISTINCT project_type FROM booking ORDER BY project_type");

// Peak hours analysis
$peakHour = array_keys($hourlyData, max($hourlyData))[0];
$peakHourFormatted = sprintf('%02d:00', $peakHour);

// Growth rate calculation (perbandingan bulan ini vs bulan lalu)
$thisMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));
$thisMonthCount = 0;
$lastMonthCount = 0;

foreach ($bookings as $booking) {
    $bookingMonth = date('Y-m', strtotime($booking['created_at']));
    if ($bookingMonth === $thisMonth) {
        $thisMonthCount++;
    } elseif ($bookingMonth === $lastMonth) {
        $lastMonthCount++;
    }
}

$growthRate = $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RAV Studio - Analytics Dashboard</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        ::-webkit-scrollbar {
            width: 10px;
            border-radius: 25px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 30px;
        }

        ::-webkit-scrollbar-thumb:hover {
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
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

        /* Heatmap Styles */
        .weekday-heatmap {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .weekday-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .weekday-item.active {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            border-color: #4a90a4;
        }

        .weekday-name {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .weekday-count {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Client Analysis */
        .client-analysis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .client-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #4a90a4;
        }

        .client-metric {
            font-size: 2rem;
            font-weight: 700;
            color: #4a90a4;
            margin-bottom: 0.5rem;
        }

        .client-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Email Domain Analysis */
        .domain-list {
            margin-top: 1rem;
        }

        .domain-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 3px solid #4a90a4;
        }

        .domain-name {
            font-weight: 600;
            color: #1e293b;
        }

        .domain-count {
            background: #4a90a4;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .weekday-heatmap {
                grid-template-columns: repeat(4, 1fr);
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
                <li><a href="admin_analytics.php" class="nav-link active-link">
                        <i class="uil uil-analytics"></i> Analytics
                    </a></li>
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
            <h1 class="page-title">Analytics Dashboard</h1>
            <p class="page-subtitle">Analisis mendalam data booking dan performa bisnis RAV Studio</p>
        </div>

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
                            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="progress" <?= $statusFilter === 'progress' ? 'selected' : '' ?>>Progress</option>
                            <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancel" <?= $statusFilter === 'cancel' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Jenis Proyek</label>
                        <select name="project_type" class="filter-select" id="projectTypeFilter">
                            <option value="all" <?= $projectTypeFilter === 'all' ? 'selected' : '' ?>>Semua Proyek</option>
                            <?php while($type = mysqli_fetch_assoc($allProjectTypes)): ?>
                                <option value="<?= htmlspecialchars($type['project_type']) ?>" 
                                        <?= $projectTypeFilter === $type['project_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['project_type']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="filter-button">
                            <i class="uil uil-search"></i> Filter Data
                        </button>
                    </div>
                    <div class="filter-group">
                        <button type="button" class="reset-button" onclick="resetFilters()">
                            <i class="uil uil-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Enhanced Statistics -->
        <div class="stats-container">
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="uil uil-clock"></i>
                </div>
                <div class="stat-number"><?= $pendingCount ?></div>
                <div class="stat-label">Pending</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="uil uil-sync"></i>
                </div>
                <div class="stat-number"><?= $progressCount ?></div>
                <div class="stat-label">Progress</div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="uil uil-check-circle"></i>
                </div>
                <div class="stat-number"><?= $completedCount ?></div>
                <div class="stat-label">Completed</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="uil uil-times-circle"></i>
                </div>
                <div class="stat-number"><?= $cancelCount ?></div>
                <div class="stat-label">Cancel</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="uil uil-percentage"></i>
                </div>
                <div class="stat-number"><?= $successRate ?>%</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Tren Booking 6 Bulan -->
            <div class="chart-container full-width-chart">
                <h3 class="chart-title">
                    <i class="uil uil-chart-line"></i> Tren Booking (6 Bulan Terakhir)
                </h3>
                <div class="chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-chart-pie"></i> Distribusi Status
                </h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Project Types -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-chart"></i> Jenis Proyek Populer
                </h3>
                <div class="chart-wrapper">
                    <canvas id="projectChart"></canvas>
                </div>
            </div>

            <!-- Booking by Weekday -->
            <div class="chart-container full-width-chart">
                <h3 class="chart-title">
                    <i class="uil uil-calendar"></i> Booking Berdasarkan Hari
                </h3>
                <div class="weekday-heatmap">
                    <?php 
                    $maxWeekdayCount = max($weekdayData);
                    foreach ($weekdayData as $day => $count): 
                        $intensity = $maxWeekdayCount > 0 ? ($count / $maxWeekdayCount) : 0;
                        $activeClass = $intensity > 0.7 ? 'active' : '';
                    ?>
                        <div class="weekday-item <?= $activeClass ?>">
                            <div class="weekday-name"><?= $day ?></div>
                            <div class="weekday-count"><?= $count ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Hourly Distribution -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-clock-three"></i> Distribusi Jam Booking
                </h3>
                <div class="chart-wrapper">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>

            <!-- Monthly Trend This Year -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="uil uil-chart-bar"></i> Tren Bulanan <?= $currentYear ?>
                </h3>
                <div class="chart-wrapper">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Client Analysis -->
        <div class="chart-container">
            <h3 class="chart-title">
                <i class="uil uil-users-alt"></i> Analisis Klien
            </h3>
            <div class="client-analysis">
                <div class="client-item">
                    <div class="client-metric"><?= $newClients ?></div>
                    <div class="client-label">Klien Baru</div>
                </div>
                <div class="client-item">
                    <div class="client-metric"><?= $repeatClients ?></div>
                    <div class="client-label">Klien Berulang</div>
                </div>
                <div class="client-item">
                    <div class="client-metric"><?= $peakHourFormatted ?></div>
                    <div class="client-label">Jam Puncak Booking</div>
                </div>
            </div>
        </div>

        <!-- Filter Summary -->
        <?php if ($dateFrom || $dateTo || $statusFilter !== 'all' || $projectTypeFilter !== 'all'): ?>
            <div class="filter-summary">
                <h3 class="chart-title">
                    <i class="uil uil-info-circle"></i> Ringkasan Filter Aktif
                </h3>
                <div class="summary-grid">
                    <?php if ($dateFrom && $dateTo): ?>
                        <div class="summary-item">
                            <strong>Periode:</strong>
                            <?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($statusFilter !== 'all'): ?>
                        <div class="summary-item">
                            <strong>Status:</strong>
                            <?= ucfirst($statusFilter) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($projectTypeFilter !== 'all'): ?>
                        <div class="summary-item">
                            <strong>Jenis Proyek:</strong>
                            <?= htmlspecialchars($projectTypeFilter) ?>
                        </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <strong>Total Data:</strong>
                        <?= $totalBookings ?> booking
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Chart.js Configuration
        Chart.defaults.font.family = 'Inter';
        Chart.defaults.color = '#64748b';

        // Color Palette
        const colors = {
            primary: '#4a90a4',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6',
            purple: '#8b5cf6',
            gradient: ['#4a90a4', '#3d6b7d', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        };

        // Trend Chart (Line Chart)
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($trendData, 'month')) ?>,
                datasets: [{
                    label: 'Jumlah Booking',
                    data: <?= json_encode(array_column($trendData, 'count')) ?>,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
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

        // Status Chart (Doughnut Chart)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Progress', 'Completed', 'Cancel'],
                datasets: [{
                    data: [<?= $pendingCount ?>, <?= $progressCount ?>, <?= $completedCount ?>, <?= $cancelCount ?>],
                    backgroundColor: [colors.warning, colors.info, colors.success, colors.danger],
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

        // Project Types Chart (Bar Chart)
        const projectCtx = document.getElementById('projectChart').getContext('2d');
        const projectChart = new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($projectTypes)) ?>,
                datasets: [{
                    label: 'Jumlah Project',
                    data: <?= json_encode(array_values($projectTypes)) ?>,
                    backgroundColor: colors.gradient,
                    borderRadius: 8,
                    borderSkipped: false
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

        // Hourly Distribution Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyChart = new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Booking per Jam',
                    data: <?= json_encode($hourlyData) ?>,
                    backgroundColor: colors.primary + '80',
                    borderColor: colors.primary,
                    borderWidth: 1,
                    borderRadius: 4
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

        // Monthly Chart This Year
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlyTrendThisYear, 'month')) ?>,
                datasets: [{
                    label: 'Booking <?= $currentYear ?>',
                    data: <?= json_encode(array_column($monthlyTrendThisYear, 'count')) ?>,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
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

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            if (!document.querySelector('form').checkValidity()) return;
            location.reload();
        }, 300000);

        // Print functionality
        function printReport() {
            window.print();
        }

        // Export functionality (basic CSV export)
        function exportData() {
            const data = [
                ['Metric', 'Value'],
                ['Total Booking', '<?= $totalBookings ?>'],
                ['Pending', '<?= $pendingCount ?>'],
                ['Progress', '<?= $progressCount ?>'],
                ['Completed', '<?= $completedCount ?>'],
                ['Cancel', '<?= $cancelCount ?>'],
                ['Success Rate', '<?= $successRate ?>%'],
                ['Cancel Rate', '<?= $cancelRate ?>%'],
                ['New Clients', '<?= $newClients ?>'],
                ['Repeat Clients', '<?= $repeatClients ?>'],
                ['Avg Message Length', '<?= $avgMessageLength ?>'],
                ['Peak Hour', '<?= $peakHourFormatted ?>']
            ];

            let csvContent = "data:text/csv;charset=utf-8,";
            data.forEach(row => {
                csvContent += row.join(",") + "\r\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "rav_studio_analytics_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Add export and print buttons
        document.addEventListener('DOMContentLoaded', function() {
            const pageHeader = document.querySelector('.page-header');
            const actionButtons = document.createElement('div');
            actionButtons.className = 'action-buttons';
            actionButtons.style.cssText = 'margin-top: 1rem; display: flex; gap: 1rem; justify-content: center;';
            
            actionButtons.innerHTML = `
                <button onclick="printReport()" class="filter-button" style="background: #6b7280;">
                    <i class="uil uil-print"></i> Print Report
                </button>
                <button onclick="exportData()" class="filter-button" style="background: #059669;">
                    <i class="uil uil-download-alt"></i> Export CSV
                </button>
            `;
            
            pageHeader.appendChild(actionButtons);
        });
    </script>
</body>
</html>
