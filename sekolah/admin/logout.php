<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_destroy();

// Redirect ke halaman login
header("Location: ../public/login.php");
exit;
