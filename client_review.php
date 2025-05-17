<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $photo_name = basename($_FILES["photo"]["name"]);
        $photo_tmp = $_FILES["photo"]["tmp_name"];
        $target_file = $upload_dir . uniqid() . "_" . $photo_name;

        if (move_uploaded_file($photo_tmp, $target_file)) {
            $sql = "INSERT INTO client_review (client_name, project_name, review, photo)
                    VALUES ('$client_name', '$project_name', '$review', '$target_file')";

            if (mysqli_query($conn, $sql)) {
                echo "✅ Review berhasil ditambahkan!";
            } else {
                echo "❌ Gagal menyimpan ke database: " . mysqli_error($conn);
            }
        } else {
            echo "❌ Gagal upload foto.";
        }
    } else {
        echo "❌ Tidak ada foto yang diunggah atau terjadi error.";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Client Review</title>
</head>
<body>
    <h2>Form Input Review Klien</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Nama Klien:</label><br>
        <input type="text" name="client_name" required><br><br>

        <label>Nama Proyek:</label><br>
        <input type="text" name="project_name" required><br><br>

        <label>Isi Review:</label><br>
        <textarea name="review" rows="4" cols="40" required></textarea><br><br>

        <label>Foto Klien:</label><br>
        <input type="file" name="photo" accept="image/*" required><br><br>

        <button type="submit">Kirim Review</button>
    </form>
</body>
</html>
