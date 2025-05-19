<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $upload_dir = "uploads/";

    // Jika ada file baru diunggah
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $photo_name = basename($_FILES["photo"]["name"]);
        $photo_tmp = $_FILES["photo"]["tmp_name"];
        $target_file = $upload_dir . uniqid() . "_" . $photo_name;

        if (move_uploaded_file($photo_tmp, $target_file)) {
            $sql = "UPDATE client_review 
                    SET client_name='$client_name', project_name='$project_name', review='$review', photo='$target_file' 
                    WHERE id=$id";
        } else {
            echo "❌ Gagal upload foto.";
            exit;
        }
    } else {
        // Tanpa mengganti foto
        $sql = "UPDATE client_review 
                SET client_name='$client_name', project_name='$project_name', review='$review' 
                WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        echo "✅ Review berhasil diperbarui!<br>";
    } else {
        echo "❌ Gagal memperbarui data: " . mysqli_error($conn);
    }
}

// Ambil 6 data pertama
$result = mysqli_query($conn, "SELECT * FROM client_review ORDER BY created_at ASC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Review Page</title>
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


    .review-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      padding: 30px;
    }

    /* Individual review box */
    .review-box {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .review-name {
      font-weight: bold;
      font-size: 1.1rem;
      margin-bottom: 5px;
    }

    .review-role {
      font-size: 0.9rem;
      color: #7f8c8d;
      margin-bottom: 10px;
    }

    .review-comment {
      font-size: 0.95rem;
      margin-bottom: 20px;
      color: #2c3e50;
      flex-grow: 1;
    }

    .edit-button {
      align-self: flex-end;
      background-color: #3498db;
      color: #fff;
      padding: 6px 14px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background-color 0.3s ease;
    }

    .Review-title {
            margin: 30px auto;
            text-align: center;
            font-size: 32px;
            color: #2c3e50;
    }
    .edit-button:hover {
      background-color: #2980b9;
    }

    @media (max-width: 768px) {
      .review-container {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 500px) {
      .review-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<nav id="header">
        <div class="nav-logo">
            <p class="nav-name">Admin-Dashboard</p>
            <span>.</span>
        </div>
        <div class="nav-menu" id="myNavMenu">
            <ul class="nav_menu_list">
                <li class="nav_list"><a href="#" class="nav-link">Project</a></li>
                <li class="nav_list"><a href="#" class="nav-link">FAQ</a></li>
                <li class="nav_list"><a href="#" class="nav-link active-link">Review</a></li>
            </ul>
        </div>
        <div class="nav-button">
            <a href="#" class="btn">Logout</a>
        </div>
    </nav>
<body>

  <!-- Navbar Judul Review -->
  <h1 class="Review-title">Review</h1>

  <!-- Review Section -->
  <div class="review-container">
    <?php $edit_id = isset($_POST['edit']) ? $_POST['id'] : null;?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="review-box">
            <?php if (!empty($row['photo'])): ?>
                <img src="<?= htmlspecialchars($row['photo']) ?>" alt="Foto Klien" style="width:100%; border-radius:8px; margin-bottom:15px;">
            <?php endif; ?>
            <div class="review-name"><?= htmlspecialchars($row['client_name']) ?></div>
            <div class="review-role"><?= htmlspecialchars($row['project_name']) ?></div>
            <div class="review-comment"><?= htmlspecialchars($row['review']) ?></div>

            <form method="post" style="margin-top:10px;" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button class="edit-button" type="submit" name="edit">Edit</button>
            </form>
        </div>

        <?php if (isset($edit_id) && $edit_id == $row['id']): ?>
            <form method="post" enctype="multipart/form-data"
                style="max-width:900px;margin:10px auto;background:#fff;padding:20px 30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <label>Nama Klien:</label><br>
                <input type="text" name="client_name" value="<?= htmlspecialchars($row['client_name']) ?>" required><br><br>

                <label>Nama Proyek:</label><br>
                <input type="text" name="project_name" value="<?= htmlspecialchars($row['project_name']) ?>" required><br><br>

                <label>Review:</label><br>
                <textarea name="review" rows="4" cols="70" required><?= htmlspecialchars($row['review']) ?></textarea><br><br>

                <label>Ganti Foto (Opsional):</label><br>
                <input type="file" name="photo"><br><br>

                <button class="edit-button" type="submit" name="update">Simpan</button>
            </form>
        <?php endif; ?>
    <?php endwhile; ?>
  </div>

</body>
</html>