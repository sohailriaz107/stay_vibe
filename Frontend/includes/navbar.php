<?php
    $base_path = isset($is_subpage) && $is_subpage ? '../' : '';
    $current_page = basename($_SERVER['PHP_SELF']);
    $logged_in = is_authenticated();
?>
<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
            <img src="<?php echo $base_path; ?>assets/imgs/logo.png" alt="Stay Vibes Resort" height="50" class="me-2">
            <span style="font-weight: 800; color: var(--primary-color); font-size: 1.5rem; letter-spacing: -1px;">STAY<span style="color: var(--secondary-color);">VIBES</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
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
                
                <?php if ($logged_in): ?>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo $base_path; ?>pages/dashboard.php" class="btn btn-primary rounded-pill px-4" style="background-color: var(--primary-color);">
                            <i class="fas fa-user-circle me-2"></i> <?php echo explode(' ', $_SESSION['user_name'] ?? 'User')[0]; ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo $base_path; ?>pages/login.php" class="btn btn-outline-primary rounded-pill px-4 me-2">Login</a>
                        <a href="<?php echo $base_path; ?>pages/register.php" class="btn btn-gold rounded-pill px-4">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
