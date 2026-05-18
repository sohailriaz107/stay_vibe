<?php
    $base_path = isset($is_subpage) && $is_subpage ? '../' : '';
?>
<footer class="footer bg-primary text-white py-4 mt-5" style="background-color: var(--primary-color) !important;">
    <div class="container py-2">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                <div class="footer-brand mb-3">
                    <span style="font-weight: 800; color: white; font-size: 1.6rem; letter-spacing: -1px;">STAY<span style="color: var(--secondary-color);">VIBES</span></span>
                </div>
                <p class="text-white-50 mb-3 pe-lg-5 small">Redefining luxury hospitality through strategic investments. We bridge the gap between premium resort experiences and smart wealth creation.</p>
                <div class="social-links d-flex gap-3">
                    <a href="#" class="social-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); color: white; transition: 0.3s;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); color: white; transition: 0.3s;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); color: white; transition: 0.3s;"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); color: white; transition: 0.3s;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--secondary-color); font-size: 0.9rem;">Quick Links</h6>
                <ul class="list-unstyled footer-links mb-0 small">
                    <li class="mb-2"><a href="<?php echo $base_path; ?>index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/about.php" class="text-white-50 text-decoration-none">Our Story</a></li>
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/plans.php" class="text-white-50 text-decoration-none">Investment Plans</a></li>
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/destinations.php" class="text-white-50 text-decoration-none">Destinations</a></li>
                </ul>
            </div>

            <!-- Support Links -->
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--secondary-color); font-size: 0.9rem;">Support</h6>
                <ul class="list-unstyled footer-links mb-0 small">
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/how-it-works.php" class="text-white-50 text-decoration-none">How It Works</a></li>
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/contact.php" class="text-white-50 text-decoration-none">Contact Support</a></li>
                    <li class="mb-2"><a href="<?php echo $base_path; ?>pages/faq.php" class="text-white-50 text-decoration-none">Help Center</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-4 mb-0">
                <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--secondary-color); font-size: 0.9rem;">Contact Info</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt me-3 mt-1 text-secondary"></i>
                        <span class="text-white-50 small">Stay Vibes Resort Private Limited<br>Rajasthan, India</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-phone-alt me-3 text-secondary"></i>
                        <span class="text-white-50 small">+91 123 456 7890</span>
                    </li>
                    <li class="mb-0 d-flex align-items-center">
                        <i class="fas fa-envelope me-3 text-secondary"></i>
                        <span class="text-white-50 small">support@stayvibesresort.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="mt-4 mb-3 border-white opacity-10">

        <div class="row align-items-center footer-bottom">
            <div class="col-md-6 mb-3 mb-md-0">
                <p class="mb-0 text-white-50 x-small-text">
                    &copy; <?php echo date('Y'); ?> <strong>STAY VIBES RESORT PRIVATE LIMITED</strong>. All Rights Reserved.
                    <br><span class="opacity-50">CIN : U55101RJ2026PTC111701</span>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-white-50 x-small-text">
                    Designed with <i class="fas fa-heart text-danger"></i> for Premium Hospitality.
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-links a:hover {
    color: var(--secondary-color) !important;
    padding-left: 5px;
    transition: all 0.3s ease;
}
.social-icon:hover {
    background: var(--secondary-color) !important;
    color: var(--primary-color) !important;
    transform: translateY(-3px);
}
.x-small-text {
    font-size: 0.75rem;
}
</style>

<!-- Bootstrap 5 JS Bundle (Includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
