<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow both admin and employee sessions
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_employee = isset($_SESSION['emp_logged_in']) && $_SESSION['emp_logged_in'] === true;

if (!$is_admin && !$is_employee) {
    header("Location: login.php");
    exit();
}
?>
