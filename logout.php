<?php
session_start();

// Hancurkan semua session
session_unset();
session_destroy();

// Set pesan logout ke session
session_start();
$_SESSION['login_message'] = "Anda telah logout dari sistem, mohon login kembali.";

// Redirect ke login.php
header("Location: login.php");
exit();
?>