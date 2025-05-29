<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database configuration (seharusnya di file config.php terpisah)
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Variables untuk feedback
$message = '';
$message_type = '';

// Proses update FAQ dengan prepared statement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    // Validasi input
    if (empty($question) || empty($answer)) {
        $message = "Question dan Answer harus diisi!";
        $message_type = "error";
    } else {
        // Update menggunakan prepared statement
        $stmt = $conn->prepare("UPDATE faq SET question=?, answer=? WHERE id=?");
        $stmt->bind_param("ssi", $question, $answer, $id);
        
        if ($stmt->execute()) {
            $message = "FAQ berhasil diperbarui!";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui FAQ.";
            $message_type = "error";
        }
        $stmt->close();
    }
}

// Ambil 5 data FAQ dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM faq ORDER BY id ASC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();

// Tangkap ID yang diedit (jika ada)
$edit_id = isset($_POST['edit']) ? (int)$_POST['id'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RAV Studio - Admin FAQ</title>
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

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Modern Navigation - Sama dengan review */
        nav#header {
            background: linear-gradient(135deg, #4a90a4 0%, #5f7c8a 25%, #3d6b7d 75%, #2c5763 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            height: fit-content;
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

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 1px solid #34d399;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 1px solid #f87171;
        }

        /* FAQ Container */
        .faq-container {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .faq-box {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            position: relative;
        }

        .faq-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .faq-question {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            line-height: 1.4;
            padding-right: 4rem;
        }

        .faq-answer {
            font-size: 1rem;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 1.5rem;
        }

        .faq-actions {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
        }

        .edit-form-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin: 1.5rem 0;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #4a90a4;
            box-shadow: 0 0 0 3px rgba(74, 144, 164, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a90a4, #3d6b7d);
            color: white;
            box-shadow: 0 4px 15px rgba(74, 144, 164, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 164, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
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

            .faq-question {
                padding-right: 1rem;
            }

            .faq-actions {
                position: static;
                margin-top: 1rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .faq-container {
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
            <li><a href="admin_faq.php" class="nav-link active-link">
                <i class="uil uil-question-circle"></i> FAQ
            </a></li>
            <li><a href="admin_booking.php" class="nav-link">
                <i class="uil uil-calendar-alt"></i> Booking
            </a></li>
            <li><a href="admin_project.php" class="nav-link">
                <i class="uil uil-calendar-alt"></i> Project
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
        <h1 class="page-title">Management FAQ</h1>
        <p class="page-subtitle">Kelola pertanyaan yang sering ditanyakan dengan mudah</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $message_type == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="uil <?= $message_type == 'success' ? 'uil-check-circle' : 'uil-exclamation-triangle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="faq-container">
        <?php 
        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        
        foreach ($faqs as $row): 
        ?>
            <div class="faq-box">
                <div class="faq-actions">
                    <form method="post" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button class="btn btn-primary" type="submit" name="edit">
                            <i class="uil uil-edit"></i> Edit FAQ
                        </button>
                    </form>
                </div>

                <div class="faq-question"><?= htmlspecialchars($row['question']) ?></div>
                <div class="faq-answer"><?= htmlspecialchars($row['answer']) ?></div>
            </div>

            <?php if (isset($edit_id) && $edit_id == $row['id']): ?>
                <div class="edit-form-container">
                    <h3 style="margin-bottom: 1.5rem; color: #1e293b;">
                        <i class="uil uil-edit"></i> Edit FAQ
                    </h3>
                    
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Pertanyaan (Question):</label>
                            <textarea name="question" class="form-textarea" rows="3" required><?= htmlspecialchars($row['question']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jawaban (Answer):</label>
                            <textarea name="answer" class="form-textarea" rows="5" required><?= htmlspecialchars($row['answer']) ?></textarea>
                        </div>

                        <button class="btn btn-success" type="submit" name="update">
                            <i class="uil uil-check"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if (empty($faqs)): ?>
        <div class="empty-state">
            <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">
                <i class="uil uil-question-circle"></i>
            </div>
            <h3>Belum ada FAQ</h3>
            <p>Tidak ada data FAQ yang tersedia saat ini.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$conn->close();
?>