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
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    $sql = "INSERT INTO faq (question, answer) VALUES ('$question', '$answer')";
    if (mysqli_query($conn, $sql)) {
        echo "✅ FAQ berhasil ditambahkan!";
    } else {
        echo "❌ Gagal menyimpan ke database: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input FAQ</title>
</head>
<body>
    <h2>Form Input FAQ</h2>
    <form method="post">
        <label>Pertanyaan:</label><br>
        <textarea name="question" rows="2" cols="50" required></textarea><br><br>

        <label>Jawaban:</label><br>
        <textarea name="answer" rows="4" cols="50" required></textarea><br><br>

        <button type="submit">Kirim FAQ</button>
    </form>
</body>
</html>