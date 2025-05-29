<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses update status via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $allowed_status = ['konsultasi', 'desain_awal', 'revisi', 'finalisasi', 'konstruksi', 'selesai', 'ditunda', 'dibatalkan'];
    if (!in_array($status, $allowed_status)) {
        http_response_code(400);
        echo "Status tidak valid";
        exit;
    }

    $sql = "UPDATE projects SET status = '$status' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update status";
    }
    exit;
}

// Proses update tanggal via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_date') {
    $id = intval($_POST['id']);
    $date_type = $_POST['date_type'];
    $date_value = $_POST['date_value'];
    
    $allowed_types = ['start_date', 'target_completion', 'actual_completion'];
    if (!in_array($date_type, $allowed_types)) {
        http_response_code(400);
        echo "Tipe tanggal tidak valid";
        exit;
    }
    
    if (!empty($date_value)) {
        $date = DateTime::createFromFormat('Y-m-d', $date_value);
        if (!$date) {
            http_response_code(400);
            echo "Format tanggal tidak valid";
            exit;
        }
        $formatted_date = $date->format('Y-m-d');
    } else {
        $formatted_date = NULL;
    }
    
    if ($formatted_date === NULL) {
        $sql = "UPDATE projects SET $date_type = NULL WHERE id = $id";
    } else {
        $sql = "UPDATE projects SET $date_type = '$formatted_date' WHERE id = $id";
    }
    
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update tanggal: " . mysqli_error($conn);
    }
    exit;
}

// Proses update budget via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_budget') {
    $id = intval($_POST['id']);
    $budget = floatval($_POST['budget']);
    
    $sql = "UPDATE projects SET estimated_budget = $budget WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update budget";
    }
    exit;
}

// Proses tambah project baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_project') {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $project_type = mysqli_real_escape_string($conn, $_POST['project_type']);
    $building_concept = mysqli_real_escape_string($conn, $_POST['building_concept']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $building_area = floatval($_POST['building_area']);
    $land_area = floatval($_POST['land_area']);
    $estimated_budget = floatval($_POST['estimated_budget']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $booking_date = $_POST['booking_date'];
    $target_completion = $_POST['target_completion'];
    
    $sql = "INSERT INTO projects (client_name, email, phone, project_type, building_concept, location, building_area, land_area, estimated_budget, description, booking_date, target_completion, status, created_at) 
            VALUES ('$client_name', '$email', '$phone', '$project_type', '$building_concept', '$location', $building_area, $land_area, $estimated_budget, '$description', '$booking_date', '$target_completion', 'konsultasi', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = "Gagal menambah project: " . mysqli_error($conn);
    }
}

// Ambil semua data projects
$result = mysqli_query($conn, "SELECT * FROM projects ORDER BY created_at DESC");
$total_projects = mysqli_num_rows($result);

// Hitung statistik
mysqli_data_seek($result, 0);
$stats = [
    'konsultasi' => 0,
    'desain_awal' => 0,
    'revisi' => 0,
    'finalisasi' => 0,
    'konstruksi' => 0,
    'selesai' => 0,
    'ditunda' => 0,
    'dibatalkan' => 0
];

while($row = mysqli_fetch_assoc($result)) {
    if(isset($stats[$row['status']])) {
        $stats[$row['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RAV Studio - Admin Projects</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
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

        /* Add Project Button */
        .add-project-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .add-project-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            margin-top: 2rem;
        }

        .table-header {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-wrapper {
            overflow-x: auto;
            max-height: 70vh;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            min-width: 2000px;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        td {
            color: #334155;
            line-height: 1.4;
        }

        /* Column Widths */
        th:nth-child(1), td:nth-child(1) { width: 60px; }   /* ID */
        th:nth-child(2), td:nth-child(2) { width: 150px; }  /* Client */
        th:nth-child(3), td:nth-child(3) { width: 120px; }  /* Contact */
        th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Project Type */
        th:nth-child(5), td:nth-child(5) { width: 150px; }  /* Concept */
        th:nth-child(6), td:nth-child(6) { width: 180px; }  /* Location */
        th:nth-child(7), td:nth-child(7) { width: 100px; }  /* Area */
        th:nth-child(8), td:nth-child(8) { width: 120px; }  /* Budget */
        th:nth-child(9), td:nth-child(9) { width: 200px; }  /* Dates */
        th:nth-child(10), td:nth-child(10) { width: 100px; } /* Status */
        th:nth-child(11), td:nth-child(11) { width: 200px; } /* Description */

        /* Row Hover */
        tr:hover td {
            background: #f8fafc;
        }

        /* ID Badge */
        .id-badge {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
        }

        /* Client Info */
        .client-info strong {
            font-weight: 600;
            color: #1e293b;
            display: block;
            margin-bottom: 0.25rem;
        }

        .client-info small {
            color: #64748b;
            font-size: 0.75rem;
        }

        /* Contact Info */
        .contact-info {
            font-size: 0.8rem;
        }

        .contact-info a {
            color: #4a90a4;
            text-decoration: none;
            display: block;
            margin-bottom: 0.25rem;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        /* Project Type */
        .project-type {
            padding: 0.3rem 0.6rem;
            background: #f1f5f9;
            border-radius: 6px;
            font-weight: 500;
            color: #475569;
            border-left: 3px solid #4a90a4;
            font-size: 0.75rem;
            display: inline-block;
        }

        /* Building Info */
        .building-info {
            font-size: 0.8rem;
        }

        .building-info .concept {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .building-info .area-info {
            color: #64748b;
            font-size: 0.75rem;
        }

        /* Location */
        .location-info {
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.3;
        }

        /* Budget */
        .budget-display {
            font-weight: 600;
            color: #059669;
            font-size: 0.85rem;
        }

        .budget-input {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.4rem;
            font-size: 0.75rem;
            width: 100%;
            margin-top: 0.25rem;
        }

        .budget-input:focus {
            outline: none;
            border-color: #4a90a4;
        }

        /* Date Info */
        .date-info {
            font-size: 0.75rem;
        }

        .date-info .date-row {
            margin-bottom: 0.5rem;
        }

        .date-info .date-label {
            font-weight: 600;
            color: #374151;
            display: block;
            margin-bottom: 0.25rem;
        }

        .date-info .date-value {
            color: #64748b;
        }

        .date-info .date-value.not-set {
            color: #9ca3af;
            font-style: italic;
        }

        .date-input {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 4px;
            padding: 0.25rem;
            font-size: 0.7rem;
            width: 100%;
            margin-top: 0.25rem;
        }

        .date-input:focus {
            outline: none;
            border-color: #4a90a4;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
            text-align: center;
            min-width: 80px;
            margin-bottom: 0.5rem;
        }

        .status-konsultasi { background: #fef3c7; color: #92400e; }
        .status-desain_awal { background: #dbeafe; color: #1e40af; }
        .status-revisi { background: #fde68a; color: #d97706; }
        .status-finalisasi { background: #c7d2fe; color: #4338ca; }
        .status-konstruksi { background: #fed7d7; color: #c53030; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-ditunda { background: #f3e8ff; color: #7c3aed; }
        .status-dibatalkan { background: #fee2e2; color: #dc2626; }

        .status-select {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.3rem;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .status-select:focus {
            outline: none;
            border-color: #4a90a4;
        }

        /* Description */
        .description-text {
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.4;
            max-height: 60px;
            overflow-y: auto;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 2rem;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .close {
            color: #64748b;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #1e293b;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #4a90a4;
            box-shadow: 0 0 0 3px rgba(74, 144, 164, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 164, 0.3);
        }

        .btn-secondary {
            background: #64748b;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .table-wrapper {
                overflow-x: scroll;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            th, td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
            
            .modal-content {
                width: 95%;
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
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
            <li><a href="admin_projects.php" class="nav-link active-link">
                <i class="uil uil-building"></i> Projects
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
        <h1 class="page-title">Management Projects</h1>
        <p class="page-subtitle">Kelola semua proyek client dengan detail lengkap</p>
    </div>

    <a href="#" class="add-project-btn" onclick="openModal()">
        <i class="uil uil-plus"></i>
        Tambah Project Baru
    </a>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-building"></i>
            </div>
            <div class="stat-number"><?= $total_projects ?></div>
            <div class="stat-label">Total Projects</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-comments"></i>
            </div>
            <div class="stat-number"><?= $stats['konsultasi'] ?></div>
            <div class="stat-label">Konsultasi</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-pen"></i>
            </div>
            <div class="stat-number"><?= $stats['desain_awal'] ?></div>
            <div class="stat-label">Desain Awal</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-constructor"></i>
            </div>
            <div class="stat-number"><?= $stats['konstruksi'] ?></div>
            <div class="stat-label">Konstruksi</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-check-circle"></i>
            </div>
            <div class="stat-number"><?= $stats['selesai'] ?></div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class
="table-title">
                <i class="uil uil-list-ul"></i>
                Daftar Projects
            </h2>
        </div>

        <div class="table-wrapper">
            <?php if($total_projects > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Kontak</th>
                        <th>Tipe Project</th>
                        <th>Konsep & Area</th>
                        <th>Lokasi</th>
                        <th>Area</th>
                        <th>Budget</th>
                        <th>Timeline</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0);
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td>
                            <span class="id-badge">#<?= $row['id'] ?></span>
                        </td>
                        
                        <td>
                            <div class="client-info">
                                <strong><?= htmlspecialchars($row['client_name']) ?></strong>
                                <small>Terdaftar: <?= date('d/m/Y', strtotime($row['created_at'])) ?></small>
                            </div>
                        </td>
                        
                        <td>
                            <div class="contact-info">
                                <a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a>
                                <a href="tel:<?= htmlspecialchars($row['phone']) ?>"><?= htmlspecialchars($row['phone']) ?></a>
                            </div>
                        </td>
                        
                        <td>
                            <span class="project-type"><?= htmlspecialchars($row['project_type']) ?></span>
                        </td>
                        
                        <td>
                            <div class="building-info">
                                <div class="concept"><?= htmlspecialchars($row['building_concept']) ?></div>
                            </div>
                        </td>
                        
                        <td>
                            <div class="location-info">
                                <?= htmlspecialchars($row['location']) ?>
                            </div>
                        </td>
                        
                        <td>
                            <div class="area-info">
                                <div><strong>Bangunan:</strong> <?= number_format($row['building_area'], 0, ',', '.') ?> m²</div>
                                <div><strong>Tanah:</strong> <?= number_format($row['land_area'], 0, ',', '.') ?> m²</div>
                            </div>
                        </td>
                        
                        <td>
                            <div class="budget-display" onclick="editBudget(<?= $row['id'] ?>, <?= $row['estimated_budget'] ?>)">
                                Rp <?= number_format($row['estimated_budget'], 0, ',', '.') ?>
                            </div>
                            <input type="number" class="budget-input" id="budget-input-<?= $row['id'] ?>" 
                                   value="<?= $row['estimated_budget'] ?>" style="display: none;"
                                   onblur="updateBudget(<?= $row['id'] ?>)"
                                   onkeypress="if(event.key==='Enter') updateBudget(<?= $row['id'] ?>)">
                        </td>
                        
                        <td>
                            <div class="date-info">
                                <div class="date-row">
                                    <span class="date-label">Booking:</span>
                                    <input type="date" class="date-input" value="<?= $row['booking_date'] ?>" 
                                           onchange="updateDate(<?= $row['id'] ?>, 'start_date', this.value)">
                                </div>
                                <div class="date-row">
                                    <span class="date-label">Target:</span>
                                    <input type="date" class="date-input" value="<?= $row['target_completion'] ?>" 
                                           onchange="updateDate(<?= $row['id'] ?>, 'target_completion', this.value)">
                                </div>
                                <div class="date-row">
                                    <span class="date-label">Selesai:</span>
                                    <input type="date" class="date-input" value="<?= $row['actual_completion'] ?>" 
                                           onchange="updateDate(<?= $row['id'] ?>, 'actual_completion', this.value)">
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                            </span>
                            <select class="status-select" onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                                <option value="konsultasi" <?= $row['status'] == 'konsultasi' ? 'selected' : '' ?>>Konsultasi</option>
                                <option value="desain_awal" <?= $row['status'] == 'desain_awal' ? 'selected' : '' ?>>Desain Awal</option>
                                <option value="revisi" <?= $row['status'] == 'revisi' ? 'selected' : '' ?>>Revisi</option>
                                <option value="finalisasi" <?= $row['status'] == 'finalisasi' ? 'selected' : '' ?>>Finalisasi</option>
                                <option value="konstruksi" <?= $row['status'] == 'konstruksi' ? 'selected' : '' ?>>Konstruksi</option>
                                <option value="selesai" <?= $row['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditunda" <?= $row['status'] == 'ditunda' ? 'selected' : '' ?>>Ditunda</option>
                                <option value="dibatalkan" <?= $row['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </td>
                        
                        <td>
                            <div class="description-text">
                                <?= htmlspecialchars($row['description']) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="uil uil-building"></i>
                </div>
                <h3>Belum ada project</h3>
                <p>Klik tombol "Tambah Project Baru" untuk mulai menambahkan project</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Project -->
<div id="addProjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Project Baru</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_project">
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Client</label>
                    <input type="text" name="client_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="tel" name="phone" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipe Project</label>
                    <select name="project_type" class="form-select" required>
                        <option value="">Pilih Tipe Project</option>
                        <option value="Rumah Tinggal">Rumah Tinggal</option>
                        <option value="Ruko">Ruko</option>
                        <option value="Kantor">Kantor</option>
                        <option value="Villa">Villa</option>
                        <option value="Apartemen">Apartemen</option>
                        <option value="Renovasi">Renovasi</option>
                        <option value="Interior">Interior</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konsep Bangunan</label>
                    <input type="text" name="building_concept" class="form-input" required>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Luas Bangunan (m²)</label>
                    <input type="number" name="building_area" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Luas Tanah (m²)</label>
                    <input type="number" name="land_area" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estimasi Budget (Rp)</label>
                    <input type="number" name="estimated_budget" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tanggal Booking</label>
                    <input type="date" name="booking_date" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Target Penyelesaian</label>
                    <input type="date" name="target_completion" class="form-input">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Deskripsi Project</label>
                    <textarea name="description" class="form-textarea" rows="4" required></textarea>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-primary">Simpan Project</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
function openModal() {
    document.getElementById('addProjectModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addProjectModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('addProjectModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Update Status Function
function updateStatus(id, status) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Update status badge
                var statusBadge = document.querySelector(trhas(select[onchange*="${id}"]) .status-badge);
                if (statusBadge) {
                    statusBadge.className = 'status-badge status-' + status;
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
                }
                
                // Show success feedback
                showNotification('Status berhasil diupdate', 'success');
            } else {
                alert('Gagal mengupdate status: ' + xhr.responseText);
            }
        }
    };
    
    xhr.send('action=update_status&id=' + id + '&status=' + encodeURIComponent(status));
}

// Update Date Function
function updateDate(id, dateType, dateValue) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                showNotification('Tanggal berhasil diupdate', 'success');
            } else {
                alert('Gagal mengupdate tanggal: ' + xhr.responseText);
            }
        }
    };
    
    xhr.send('action=update_date&id=' + id + '&date_type=' + encodeURIComponent(dateType) + '&date_value=' + encodeURIComponent(dateValue));
}

// Edit Budget Function
function editBudget(id, currentBudget) {
    var budgetDisplay = document.querySelector(trhas(input[id="budget-input-${id}"]) .budget-display);
    var budgetInput = document.getElementById('budget-input-' + id);
    
    if (budgetDisplay && budgetInput) {
        budgetDisplay.style.display = 'none';
        budgetInput.style.display = 'block';
        budgetInput.focus();
        budgetInput.select();
    }
}

// Update Budget Function
function updateBudget(id) {
    var budgetInput = document.getElementById('budget-input-' + id);
    var budgetDisplay = document.querySelector(trhas(input[id="budget-input-${id}"]) .budget-display);
    var newBudget = budgetInput.value;
    
    if (newBudget === '') {
        budgetInput.style.display = 'none';
        budgetDisplay.style.display = 'block';
        return;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Update display
                budgetDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newBudget);
                budgetInput.style.display = 'none';
                budgetDisplay.style.display = 'block';
                showNotification('Budget berhasil diupdate', 'success');
            } else {
                alert('Gagal mengupdate budget: ' + xhr.responseText);
                budgetInput.style.display = 'none';
                budgetDisplay.style.display = 'block';
            }
        }
    };
    
    xhr.send('action=update_budget&id=' + id + '&budget=' + encodeURIComponent(newBudget));
}

// Notification Function
function showNotification(message, type) {
    // Create notification element
    var notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        z-index: 10000;
        font-weight: 600;
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(function() {
        notification.style.transform = 'translateX(100%)';
        setTimeout(function() {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Error message display
<?php if(isset($error_message)): ?>
alert('<?= addslashes($error_message) ?>');
<?php endif; ?>
</script>

</body>
</html>

<?php
mysqli_close($conn);
?>