<?php
    $base_path = isset($is_subpage) && $is_subpage ? '../' : '';
    $current_page = basename($_SERVER['PHP_SELF']);
    $logged_in = is_authenticated();
?>
<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container d-flex align-items-center justify-content-between flex-wrap">
        
        <!-- Left side: Brand Logo -->
        <div class="d-flex align-items-center order-1 order-lg-1">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
                <img src="<?php echo $base_path; ?>assets/imgs/logo.png" alt="Stay Vibes Resort" height="36" class="me-2 d-inline-block align-top">
                <span class="brand-text fw-bold fs-5 ms-2" style="color: var(--primary-color); font-family: 'Poppins', sans-serif; letter-spacing: 0.5px;">Stay <span style="color: var(--secondary-color);">Vibe</span></span>
            </a>
        </div>

        <!-- Right side on mobile & desktop: Login (or Dashboard) Button -->
        <div class="order-2 order-lg-3">
            <?php if ($logged_in): ?>
                <a href="<?php echo $base_path; ?>App/dashboard.php" class="btn btn-primary rounded-pill px-3 px-sm-4 py-2" style="background-color: var(--primary-color); border-color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">
                    <i class="fas fa-user-circle me-1"></i>
                    <span><?php echo explode(' ', $_SESSION['user_name'] ?? 'User')[0]; ?></span>
                </a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>pages/login.php" class="btn btn-outline-primary rounded-pill px-3 px-sm-4 py-2" style="font-weight: 600; font-size: 0.9rem; border-color: var(--primary-color); color: var(--primary-color); transition: var(--transition-smooth);">Login</a>
            <?php endif; ?>
        </div>

      
    </div>
</nav>
