<?php
require_once('auth.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stay Vibes | Admin Dashboard</title>
    <link rel="icon" type="image/jpeg" href="assets/css/imgs/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay"></div>

<!-- Mobile Top Bar -->
<div class="d-lg-none bg-white p-3 shadow-sm d-flex justify-content-between align-items-center sticky-top">
    <h5 class="fw-bold mb-0">STAY <span style="color: var(--admin-secondary);">VIBES</span></h5>
    <button class="btn btn-primary" id="sidebarToggleMobile">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Mobile Sidebar Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggleMobile');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
});
</script>
