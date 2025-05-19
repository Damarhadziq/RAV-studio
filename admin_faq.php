<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses update FAQ jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    $sql = "UPDATE faq SET question='$question', answer='$answer' WHERE id=$id";
    mysqli_query($conn, $sql);
}

// Ambil 5 data FAQ
$faq_result = mysqli_query($conn, "SELECT * FROM faq ORDER BY id ASC LIMIT 5");

// Tangkap ID yang diedit (jika ada)
$edit_id = isset($_POST['edit']) ? $_POST['id'] : null;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FAQ</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
        }

        nav#header {
            background: linear-gradient(to right, #2c3e50, #336d8c);
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-logo .nav-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #fff;
        }

        .nav-logo span {
            font-size: 2rem;
            color: #f1c40f;
            margin-left: 5px;
        }

        .nav-menu ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        .nav_menu_list .nav_list a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            position: relative;
            padding-bottom: 5px;
            transition: all 0.3s ease;
        }

        .nav_menu_list .nav_list a:hover,
        .nav_menu_list .nav_list .nav-link.active-link {
            color: #f1c40f;
        }

        .nav_menu_list .nav_list .nav-link.active-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #f1c40f;
            border-radius: 2px;
        }

        .nav-button .btn {
            background-color: #e74c3c;
            color: #fff;
            padding: 0.6rem 1.2rem;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .nav-button .btn:hover {
            background-color: #c0392b;
        }

        .faq-title {
            margin: 30px auto;
            text-align: center;
            font-size: 32px;
            color: #2c3e50;
        }

        .faq-container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .faq-text {
            flex: 1;
        }

        .faq-question {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .faq-answer {
            font-size: 16px;
            color: #555;
        }

        .edit-button {
            background-color: #3498db;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 20px;
        }

        .edit-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

     <nav id="header">
        <div class="nav-logo">
            <p class="nav-name">Admin-Dashboard</p>
            <span>.</span>
        </div>
        <div class="nav-menu" id="myNavMenu">
            <ul class="nav_menu_list">
                <li class="nav_list"><a href="#" class="nav-link">Project</a></li>
                <li class="nav_list"><a href="#" class="nav-link active-link">FAQ</a></li>
                <li class="nav_list"><a href="#" class="nav-link">Review</a></li>
            </ul>
        </div>
        <div class="nav-button">
            <a href="#" class="btn">Logout</a>
        </div>
    </nav>

    <h1 class="faq-title">Frequently Asked Questions (FAQ)</h1>
        
    <?php while ($row = mysqli_fetch_assoc($faq_result)): ?>
        <div class="faq-container">
            <div class="faq-text">
                <div class="faq-question"><?= htmlspecialchars($row['question']) ?></div>
                <div class="faq-answer"><?= htmlspecialchars($row['answer']) ?></div>
            </div>
            <form method="post" style="margin:0;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button class="edit-button" type="submit" name="edit">Edit</button>
            </form>
        </div>

        <?php if ($edit_id == $row['id']): ?>
            <form method="post" style="max-width:900px;margin:10px auto;background:#fff;padding:20px 30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <label>Question:</label><br>
                <textarea name="question" rows="2" cols="70" required><?= htmlspecialchars($row['question']) ?></textarea><br><br>
                <label>Answer:</label><br>
                <textarea name="answer" rows="4" cols="70" required><?= htmlspecialchars($row['answer']) ?></textarea><br><br>
                <button class="edit-button" type="submit" name="update">Simpan</button>
            </form>
        <?php endif; ?>
    <?php endwhile; ?>
</body>
</html>