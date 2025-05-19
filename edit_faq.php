<?php
$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    $sql = "UPDATE faq SET question='$question', answer='$answer' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "✅ FAQ berhasil diperbarui!<br>";
    } else {
        echo "❌ Gagal memperbarui FAQ: " . mysqli_error($conn);
    }
}

// Ambil 5 data pertama
$result = mysqli_query($conn, "SELECT * FROM faq ORDER BY id ASC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit FAQ</title>
</head>
<body>
    <h2>Edit FAQ</h2>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <form method="post" style="border:1px solid #ccc; padding:10px; margin-bottom:20px;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">

        <label>Pertanyaan:</label><br>
        <textarea name="question" rows="2" cols="50" required><?= htmlspecialchars($row['question']) ?></textarea><br><br>

        <label>Jawaban:</label><br>
        <textarea name="answer" rows="4" cols="50" required><?= htmlspecialchars($row['answer']) ?></textarea><br><br>

        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
    <?php endwhile; ?>

</body>
</html>
