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
    <link rel="stylesheet" href="assets/css/admin-style.css?v=1.2">
    
    <!-- DataTables & Responsive CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Global DataTables Mobile Centering Style -->
    <style>
        @media (max-width: 768px) {
            .dataTables_wrapper .dataTables_length {
                text-align: center !important;
                margin-top: 15px !important;
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter {
                text-align: center !important;
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                max-width: 300px !important;
                margin-left: 0 !important;
                margin-top: 5px;
                text-align: center !important;
                display: inline-block !important;
            }
            .dataTables_wrapper .dataTables_paginate {
                text-align: center !important;
                margin-top: 15px;
                margin-bottom: 15px !important;
                display: flex;
                justify-content: center;
            }
            .dataTables_wrapper .dataTables_info {
                text-align: center !important;
                margin-bottom: 10px;
            }
            .premium-table-card {
                padding: 1rem !important;
            }
            table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
            table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
                padding-left: 30px !important;
            }
            /* Center collapsed child details on mobile */
            table.dataTable > tbody > tr.child ul.dtr-details {
                width: 100% !important;
            }
            table.dataTable > tbody > tr.child ul.dtr-details > li {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
                border-bottom: 1px solid #efefef !important;
                padding: 0.8rem 0 !important;
            }
            table.dataTable > tbody > tr.child span.dtr-title {
                font-weight: 700 !important;
                margin-bottom: 0.35rem !important;
                text-align: center !important;
                display: block !important;
            }
            table.dataTable > tbody > tr.child span.dtr-data {
                text-align: center !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
            }
            table.dataTable > tbody > tr.child span.dtr-data .d-flex {
                justify-content: center !important;
                align-items: center !important;
                text-align: center !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay"></div>

<!-- Mobile Top Bar -->
<div class="d-lg-none bg-white p-2 px-3 shadow-sm d-flex justify-content-between align-items-center sticky-top" style="z-index: 1030;">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary btn-sm rounded-3" id="sidebarToggleMobile" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-bars fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">STAY <span style="color: var(--admin-secondary);">VIBES</span></h5>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="small fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
    </div>
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
            
            // Toggle icon between hamburger and cross
            const icon = toggleBtn.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.className = 'fas fa-times fs-5';
            } else {
                icon.className = 'fas fa-bars fs-5';
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            
            // Revert toggle icon back to hamburger
            const icon = toggleBtn.querySelector('i');
            if (icon) icon.className = 'fas fa-bars fs-5';
        });
    }
});
</script>
