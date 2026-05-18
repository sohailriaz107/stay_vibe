<?php
    $base_path = isset($is_subpage) && $is_subpage ? '../' : '';
    $current_page = basename($_SERVER['PHP_SELF']);
    $logged_in = is_authenticated();
?>
<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container d-flex align-items-center justify-content-between flex-wrap">
        
        <!-- Left side on mobile: Hamburger Menu Toggle & Brand Logo -->
        <div class="d-flex align-items-center order-1 order-lg-1">
            <button class="navbar-toggler border-0 ps-0 me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="box-shadow: none; border: none; outline: none;">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
                <img src="<?php echo $base_path; ?>assets/imgs/logo.png" alt="Stay Vibes Resort" height="40" class="me-2 d-inline-block align-top">
                <span class="brand-text" style="font-weight: 800; color: var(--primary-color); font-size: 1.3rem; letter-spacing: -1px;">STAY<span style="color: var(--secondary-color);">VIBES</span></span>
            </a>
        </div>

        <!-- Right side on mobile & desktop: Login (or Dashboard) Button -->
        <div class="order-2 order-lg-3">
            <?php if ($logged_in): ?>
                <a href="<?php echo $base_path; ?>App/dashboard.php" class="btn btn-primary rounded-pill px-3 px-sm-4 py-2" style="background-color: var(--primary-color); border-color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">
                    <i class="fas fa-user-circle me-1 me-sm-2"></i>
                    <span class="d-none d-sm-inline"><?php echo explode(' ', $_SESSION['user_name'] ?? 'User')[0]; ?></span>
                    <span class="d-inline d-sm-none">Dashboard</span>
                </a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>pages/login.php" class="btn btn-outline-primary rounded-pill px-3 px-sm-4 py-2" style="font-weight: 600; font-size: 0.9rem; border-color: var(--primary-color); color: var(--primary-color); transition: var(--transition-smooth);">Login</a>
            <?php endif; ?>
        </div>

        <!-- Collapsible Menu links (Order 3 on mobile, 2 on desktop) -->
        <div class="collapse navbar-collapse order-3 order-lg-2" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center py-3 py-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>pages/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'plans.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>pages/plans.php">Investment Plans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'destinations.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>pages/destinations.php">Destinations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'how-it-works.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>pages/how-it-works.php">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>pages/contact.php">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
