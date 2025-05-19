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
<html>
<head>
    <title>Edit Client Review</title>
</head>
<body>
    <h2>Edit Client Review</h2>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <form method="post" enctype="multipart/form-data" style="border:1px solid #ccc; padding:10px; margin-bottom:20px;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">

        <label>Nama Klien:</label><br>
        <input type="text" name="client_name" value="<?= htmlspecialchars($row['client_name']) ?>" required><br><br>

        <label>Nama Proyek:</label><br>
        <input type="text" name="project_name" value="<?= htmlspecialchars($row['project_name']) ?>" required><br><br>

        <label>Isi Review:</label><br>
        <textarea name="review" rows="4" cols="40" required><?= htmlspecialchars($row['review']) ?></textarea><br><br>

        <label>Foto Saat Ini:</label><br>
        <?php if (!empty($row['photo'])): ?>
            <img src="<?= $row['photo'] ?>" alt="Foto Klien" width="100"><br>
        <?php else: ?>
            <p><i>Tidak ada foto</i></p>
        <?php endif; ?>
        <label>Ganti Foto (opsional):</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
    <?php endwhile; ?>

</body>
</html>
