<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Use relative path so it works in any hosting subdirectory
        $dir = dirname($_SERVER['SCRIPT_NAME']);
        header('Location: ' . $dir . '/login.php');
        exit;
    }
}
?>
