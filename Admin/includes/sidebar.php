<div id="sidebar">
    <div class="sidebar-header">
        <h4 class="text-white fw-bold mb-0">STAY <span style="color: var(--admin-secondary);">VIBES</span></h4>
        <p class="text-white-50 x-small-text mb-0 mt-1" style="font-size: 0.7rem; letter-spacing: 2px;">ADMIN CONTROL</p>
    </div>
    
    <div class="nav flex-column mt-4">
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <a href="payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i> Payments
        </a>
        <a href="withdrawals.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'withdrawals.php' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-usd"></i> Withdrawals
        </a>
        <a href="plans.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'plans.php' ? 'active' : ''; ?>">
            <i class="fas fa-hotel"></i> Plans
        </a>
        <a href="bank_info.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bank_info.php' ? 'active' : ''; ?>">
            <i class="fas fa-university"></i> User Bank Info
        </a>
        <a href="referrals.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'referrals.php' ? 'active' : ''; ?>">
            <i class="fas fa-project-diagram"></i> Referrals
        </a>
        <a href="employees.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Employee Module
        </a>
        <?php
        // Unread inquiry count for sidebar badge
        require_once('../Frontend/includes/connect.php');
        $inq_count = $conn->query("SELECT COUNT(*) as c FROM inquiries WHERE status='new'")->fetch_assoc()['c'] ?? 0;
        ?>
        <a href="inquiries.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'active' : ''; ?>" style="position:relative;">
            <i class="fas fa-envelope-open-text"></i> Inquiry Messages
            <?php if($inq_count > 0): ?>
                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.65rem;"><?php echo $inq_count; ?></span>
            <?php endif; ?>
        </a>
        <div class="mt-auto mb-4">
            <hr class="mx-3 opacity-10 border-white">
            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="logout.php" class="nav-link text-danger mt-2">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>
