<?php
/**
 * Sidebar component for User Dashboard
 */
$base_path = isset($is_subpage) && $is_subpage ? '../' : '';
$current_page = basename($_SERVER['PHP_SELF']);
// Ensure we always point to the correct directory
$page_dir = isset($is_subpage) && $is_subpage ? '' : 'pages/';
?>
<div class="dashboard-sidebar p-4 bg-white shadow-sm h-100" style="border-radius: 20px;">
    <div class="user-info-brief text-center mb-5">
        <div class="avatar-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: var(--primary-color); color: white; border-radius: 50%; font-size: 1.5rem; font-weight: 700;">
            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
        </div>
        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h5>
        <span class="badge <?php echo (($_SESSION['user_status'] ?? 'inactive') == 'active') ? 'bg-success' : 'bg-warning'; ?> rounded-pill">
            <?php echo ucfirst($_SESSION['user_status'] ?? 'Inactive'); ?> Account
        </span>
    </div>

    <ul class="nav flex-column dashboard-nav">
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'dashboard.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>dashboard.php">
                <i class="fas fa-th-large me-3"></i> Overview
            </a>
        </li>
       
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'profile.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>profile.php">
                <i class="fas fa-user-circle me-3"></i> My Profile
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'my-plan.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>my-plan.php">
                <i class="fas fa-concierge-bell me-3"></i> Active Plan
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'bank-details.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>bank-details.php">
                <i class="fas fa-university me-3"></i> Bank Details
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'wallet.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>wallet.php">
                <i class="fas fa-wallet me-3"></i> Wallet & History
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'referrals.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>referrals.php">
                <i class="fas fa-users me-3"></i> Referral System
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-3 py-3 <?php echo ($current_page == 'withdraw.php') ? 'active-sidebar' : ''; ?>" href="<?php echo $page_dir; ?>withdraw.php">
                <i class="fas fa-hand-holding-usd me-3"></i> Withdrawal
            </a>
        </li>
        <li class="nav-item mt-5">
            <a class="nav-link rounded-3 py-3 text-danger" href="<?php echo $page_dir; ?>logout.php">
                <i class="fas fa-sign-out-alt me-3"></i> Logout
            </a>
        </li>
    </ul>
</div>

<style>
.dashboard-nav .nav-link {
    color: var(--text-muted);
    font-weight: 500;
    transition: var(--transition-smooth);
    border-left: 4px solid transparent;
}
.dashboard-nav .nav-link:hover {
    background: var(--bg-light);
    color: var(--primary-color);
}
.dashboard-nav .nav-link.active-sidebar {
    background: rgba(11, 44, 77, 0.05);
    color: var(--primary-color);
    border-left-color: var(--secondary-color);
}
</style>
