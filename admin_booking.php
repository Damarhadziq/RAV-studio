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

    $allowed_status = ['pending', 'progress', 'completed'];
    if (!in_array($status, $allowed_status)) {
        http_response_code(400);
        echo "Status tidak valid";
        exit;
    }

    $sql = "UPDATE booking SET status = '$status' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update status";
    }
    exit;
}

// Ambil semua data booking
$result = mysqli_query($conn, "SELECT * FROM booking ORDER BY created_at DESC");

// Hitung total booking
$total_bookings = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RAV Studio - Admin Booking</title>
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

        /* Modern Navigation */
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

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
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
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            color: #334155;
            transition: all 0.2s ease;
        }

        tr:hover td {
            background: #f8fafc;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        /* ID Badge */
        .id-badge {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Project Type */
        .project-type {
            padding: 0.375rem 0.75rem;
            background: #f1f5f9;
            border-radius: 8px;
            font-weight: 500;
            color: #475569;
            border-left: 3px solid #4a90a4;
        }

        /* Message Preview */
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #64748b;
            font-style: italic;
        }

        /* Status Select */
        .status-select {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-select:focus {
            outline: none;
            border-color: #4a90a4;
            box-shadow: 0 0 0 3px rgba(74, 144, 164, 0.1);
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
        @media (max-width: 768px) {
            nav#header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .nav-menu ul {
                gap: 1rem;
            }

            .main-container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .message-preview {
                max-width: 150px;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-container {
            animation: fadeIn 0.6s ease-out;
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
            <li><a href="admin_booking.php" class="nav-link active-link">
                <i class="uil uil-calendar-alt"></i> Booking
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
        <h1 class="page-title">Management Booking</h1>
        <p class="page-subtitle">Kelola semua booking client dengan mudah</p>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-calendar-alt"></i>
            </div>
            <div class="stat-number"><?= $total_bookings ?></div>
            <div class="stat-label">Total Booking</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-clock"></i>
            </div>
            <div class="stat-number">
                <?php 
                mysqli_data_seek($result, 0);
                $pending = 0;
                while($row = mysqli_fetch_assoc($result)) {
                    if($row['status'] == 'pending') {
                        $pending++;
                    }
                }
                echo $pending;
                ?>
            </div>
            <div class="stat-label">Pending</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-spinner"></i>
            </div>
            <div class="stat-number">
                <?php 
                mysqli_data_seek($result, 0);
                $progress = 0;
                while($row = mysqli_fetch_assoc($result)) {
                    if($row['status'] == 'progress') {
                        $progress++;
                    }
                }
                echo $progress;
                ?>
            </div>
            <div class="stat-label">In Progress</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="uil uil-check-circle"></i>
            </div>
            <div class="stat-number">
                <?php 
                mysqli_data_seek($result, 0);
                $completed = 0;
                while($row = mysqli_fetch_assoc($result)) {
                    if($row['status'] == 'completed') {
                        $completed++;
                    }
                }
                echo $completed;
                ?>
            </div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">
                <i class="uil uil-list-ul"></i>
                Daftar Booking Client
            </h2>
        </div>
        
        <div class="table-wrapper">
            <?php 
            mysqli_data_seek($result, 0);
            if(mysqli_num_rows($result) > 0): 
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Client</th>
                        <th>Email</th>
                        <th>Project Type</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <span class="id-badge">#<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['client_name']) ?></strong>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color: #4a90a4; text-decoration: none;">
                                <?= htmlspecialchars($row['email']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="project-type"><?= htmlspecialchars($row['project_type']) ?></span>
                        </td>
                        <td>
                            <div class="message-preview" title="<?= htmlspecialchars($row['message']) ?>">
                                <?= htmlspecialchars($row['message']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <time datetime="<?= $row['created_at'] ?>">
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                                <br>
                                <small style="color: #64748b;"><?= date('H:i', strtotime($row['created_at'])) ?></small>
                            </time>
                        </td>
                        <td>
                            <select class="status-select" onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                                <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="progress" <?= $row['status'] == 'progress' ? 'selected' : '' ?>>Progress</option>
                                <option value="completed" <?= $row['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="uil uil-calendar-slash"></i>
                </div>
                <h3>Belum ada booking</h3>
                <p>Tidak ada data booking yang tersedia saat ini.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'Sukses') {
            // Reload halaman untuk update statistik
            location.reload();
        } else {
            alert('Gagal mengupdate status: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
}
</script>

</body>
</html>