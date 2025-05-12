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

    $sql = "INSERT INTO client_review (client_name, project_name, review)
            VALUES ('$client_name', '$project_name', '$review')";

    if (mysqli_query($conn, $sql)) {
        echo "✅ Review berhasil ditambahkan!";
    } else {
        echo "❌ Gagal menambahkan review: " . mysqli_error($conn);
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
    <form method="post">
        <label>Nama Klien:</label><br>
        <input type="text" name="client_name" required><br><br>

        <label>Nama Project:</label><br>
        <input type="text" name="project_name" required><br><br>

        <label>Isi Review:</label><br>
        <textarea name="review" rows="4" cols="40" required></textarea><br><br>

        <button type="submit">Kirim Review</button>
    </form>
</body>
</html>
