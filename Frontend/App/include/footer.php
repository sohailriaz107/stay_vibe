<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- App Bottom Navigation Bar -->
<div class="mobile-bottom-nav">
    <a href="index.php" class="mobile-nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="plans.php" class="mobile-nav-link <?php echo ($current_page == 'referrals.php') ? 'active' : ''; ?>">
           <i class="fas fa-gem"></i>
        <span>All Plans</span>
    </a>
     <a href="contact.php" class="mobile-nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">
        <i class="fas fa-headset"></i>
        <span>Support</span>
    </a>
    <a href="profile.php" class="mobile-nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
   
</div>

<style>
/* Bottom Nav Styles (Visible on Mobile & Desktop) */
.mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: #ffffff;
    box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.04);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: space-around;
    border-top: none;
    padding-bottom: env(safe-area-inset-bottom);
}

.mobile-nav-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #a0aec0 !important;
    text-decoration: none !important;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 8px 12px;
    flex: 1;
    text-align: center;
}

.mobile-nav-link i {
    font-size: 1.2rem;
    margin-bottom: 2px;
    transition: transform 0.3s ease;
}

.mobile-nav-link:hover {
    color: var(--primary-color) !important;
}

.mobile-nav-link.active {
    color: var(--secondary-color) !important;
}

.mobile-nav-link.active i {
    transform: translateY(-2px);
}

/* Global bottom spacer for all devices so footer doesn't hide content */
body {
    padding-bottom: 75px !important;
}
</style>
