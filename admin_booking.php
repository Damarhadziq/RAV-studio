<?php
// 1 file untuk tampil + proses update AJAX

$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Kalau request POST dari AJAX untuk update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = intval($_POST['id']);
    $manual_status = $_POST['manual_status'];

    $allowed_status = ['belum', 'progress', 'selesai'];
    if (!in_array($manual_status, $allowed_status)) {
        http_response_code(400);
        echo "Status tidak valid";
        exit;
    }

    $sql = "UPDATE booking SET manual_status = '$manual_status' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update status";
    }
    exit; // penting, supaya bagian HTML nggak ikut dikirim
}

// Ambil data booking untuk tampil
$result = mysqli_query($conn, "SELECT * FROM booking ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Booking</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #aaa;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .status-pending { color: orange; }
        .status-email_sent { color: green; }
        .status-email_failed { color: red; }
        .progress-dropdown {
            padding: 5px;
            font-weight: bold;
        }
        .status-belum {
            color: gray;
        }
        .status-progress {
            color: blue;
        }
        .status-selesai {
            color: green;
        }
    </style>
</head>
<body>
    <h2>Daftar Booking Client</h2>

    <table id="bookingTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Project</th>
                <th>Pesan</th>
                <th>Status Email</th>
                <th>Progress Status</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) : 
                $selected_belum = $row['manual_status'] === 'belum' ? 'selected' : '';
                $selected_progress = $row['manual_status'] === 'progress' ? 'selected' : '';
                $selected_selesai = $row['manual_status'] === 'selesai' ? 'selected' : '';
            ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['client_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['project_type']); ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                <td class="status-<?= $row['status']; ?>"><?= $row['status']; ?></td>
                <td>
                    <select class="progress-dropdown" onchange="changeStatus(this, <?= $row['id']; ?>)">
                        <option value="belum" <?= $selected_belum ?>>Belum Ditentukan</option>
                        <option value="progress" <?= $selected_progress ?>>On Progress</option>
                        <option value="selesai" <?= $selected_selesai ?>>Selesai</option>
                    </select>
                </td>
                <td><?= $row['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

<script>
function changeStatus(selectElement, bookingId) {
    const newStatus = selectElement.value;

    // Ubah warna dropdown
    selectElement.classList.remove("status-belum", "status-progress", "status-selesai");
    if (newStatus === "progress") {
        selectElement.classList.add("status-progress");
    } else if (newStatus === "selesai") {
        selectElement.classList.add("status-selesai");
    } else {
        selectElement.classList.add("status-belum");
    }

    // Kirim AJAX update status
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "");
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log("Status berhasil diperbarui");
        } else {
            alert("Gagal update status");
        }
    };
    xhr.send("action=update_status&id=" + bookingId + "&manual_status=" + newStatus);
}

// Inisialisasi warna saat load halaman
document.querySelectorAll(".progress-dropdown").forEach(select => {
    const val = select.value;
    if(val === "progress") select.classList.add("status-progress");
    else if(val === "selesai") select.classList.add("status-selesai");
    else select.classList.add("status-belum");
});
</script>

</body>
</html>
