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

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Escape input untuk menghindari error SQL
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $subtitle = mysqli_real_escape_string($conn, $_POST["subtitle"]);

        $sql = "INSERT INTO recent_project (image, title, subtitle)
                VALUES ('$target_file', '$title', '$subtitle')";

        if (mysqli_query($conn, $sql)) {
            echo "✅ Project berhasil ditambahkan!";
        } else {
            echo "❌ Error saat menyimpan ke database: " . mysqli_error($conn);
        }
    } else {
        echo "❌ Gagal upload gambar.";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Project</title>
</head>
<body>
    <h2>Form Tambah Project</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Gambar:</label><br>
        <input type="file" name="image" required><br><br>

        <label>Judul:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Sub Judul:</label><br>
        <input type="text" name="subtitle" required><br><br>

        <button type="submit">Simpan Project</button>
    </form>
</body>
</html>
