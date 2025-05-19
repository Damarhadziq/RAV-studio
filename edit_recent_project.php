<?php
// Koneksi ke database
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Update data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
    $image_update = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_update = ", image = '$target_file'";
        }
    }

    $sql = "UPDATE recent_project SET title = '$title', subtitle = '$subtitle' $image_update WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Project berhasil diperbarui!";
    } else {
        echo "❌ Gagal memperbarui project: " . mysqli_error($conn);
    }
}

// Ambil semua data project
$result = mysqli_query($conn, "SELECT * FROM recent_project ORDER BY id ASC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
</head>
<body>
    <h2>Edit Recent Projects</h2>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <form method="post" enctype="multipart/form-data" style="margin-bottom:30px; border:1px solid #ccc; padding:15px;">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <label>Gambar saat ini:</label><br>
            <img src="<?= $row['image'] ?>" alt="Gambar Project" width="150"><br><br>

            <label>Ganti Gambar (jika perlu):</label><br>
            <input type="file" name="image"><br><br>

            <label>Judul:</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required><br><br>

            <label>Sub Judul:</label><br>
            <input type="text" name="subtitle" value="<?= htmlspecialchars($row['subtitle']) ?>" required><br><br>

            <button type="submit">Simpan Perubahan</button>
        </form>
    <?php endwhile; ?>
</body>
</html>

<?php mysqli_close($conn); ?>
