<?php
// admin_booking.php

$localhost  = "localhost";
$username   = "root";
$password   = "";
$database   = "rav_studio";

$conn = mysqli_connect($localhost, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses update status via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $allowed_status = ['pending', 'progress', 'completed'];
    if (!in_array($status, $allowed_status)) {
        http_response_code(400);
        echo "Status tidak valid";
        exit;
    }

    $sql = "UPDATE booking SET status = '$status' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "Sukses";
    } else {
        http_response_code(500);
        echo "Gagal update status";
    }
    exit;
}

// Ambil semua data booking
$result = mysqli_query($conn, "SELECT * FROM booking ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Booking</title>
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
        .status-pending {
            color: gray;
        }
        .status-progress {
            color: blue;
        }
        .status-completed {
            color: green;
        }
        .dropdown {
            padding: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Daftar Booking Client</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Project</th>
                <th>Pesan</th>
                <th>Status Projek</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) : 
                $selected_pending = $row['status'] === 'pending' ? 'selected' : '';
                $selected_progress = $row['status'] === 'progress' ? 'selected' : '';
                $selected_completed = $row['status'] === 'completed' ? 'selected' : '';
            ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['client_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['project_type']); ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                <td>
                    <select class="dropdown status-<?= $row['status']; ?>" onchange="changeStatus(this, <?= $row['id']; ?>)">
                        <option value="pending" <?= $selected_pending ?>>Pending</option>
                        <option value="progress" <?= $selected_progress ?>>Progress</option>
                        <option value="completed" <?= $selected_completed ?>>Completed</option>
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

    // Ubah kelas warna dropdown
    selectElement.classList.remove("status-pending", "status-progress", "status-completed");
    if (newStatus === "progress") {
        selectElement.classList.add("status-progress");
    } else if (newStatus === "completed") {
        selectElement.classList.add("status-completed");
    } else {
        selectElement.classList.add("status-pending");
    }

    // Kirim request update ke server
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "");
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log("Status berhasil diperbarui");
        } else {
            alert("Gagal update status");
        }
    };
    xhr.send("action=update_status&id=" + bookingId + "&status=" + newStatus);
}

// Inisialisasi warna dropdown saat load halaman
document.querySelectorAll(".dropdown").forEach(select => {
    const val = select.value;
    if(val === "progress") select.classList.add("status-progress");
    else if(val === "completed") select.classList.add("status-completed");
    else select.classList.add("status-pending");
});
</script>

</body>
</html>
