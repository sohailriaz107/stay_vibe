<?php 
$is_subpage = true;
include('../includes/header.php'); 
include('../includes/connect.php');

// Fetch plans from DB
$plans_result = $conn->query("SELECT id, plan_name, plan_price FROM plans ORDER BY plan_price ASC");
$plans = [];
if ($plans_result) {
    while ($p = $plans_result->fetch_assoc()) {
        $plans[] = $p;
    }
}

// Process form if submitted
$message_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message_status = '<div class="alert alert-danger rounded-4">Security error: CSRF token mismatch.</div>';
    } else {
        $name  = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $plan  = sanitize_input($_POST['plan']);
        $msg   = sanitize_input($_POST['message']);

        $stmt = $conn->prepare("INSERT INTO inquiries (full_name, email, phone, plan, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $plan, $msg);
        
        if ($stmt->execute()) {
            $message_status = '<div class="alert alert-success rounded-4 shadow-sm"><i class="fas fa-check-circle me-2"></i> Thank you, ' . htmlspecialchars($name) . '! Your message has been sent. Our team will contact you within 24 hours.</div>';
        } else {
            $message_status = '<div class="alert alert-danger rounded-4">Something went wrong. Please try again.</div>';
        }
        $stmt->close();
    }
}
?>
<?php include('../includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white pt-5 mt-5" style="background: linear-gradient(rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)), url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; border-radius: 0 0 50px 50px;">
    <div class="container pt-5 pb-4 text-center">
        <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 4px; color: var(--secondary-color);">Connect With Us</h6>
        <h1 class="display-3 fw-bold mb-4" style="color: var(--secondary-color);">We're Here to Help</h1>
        <p class="lead mx-auto text-white-50" style="max-width: 700px;">Have questions about our investment portfolios or membership benefits? Our luxury hospitality experts are ready to guide you.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info Cards -->
            <div class="col-lg-5">
                <div class="pe-lg-4">
                    <h2 class="display-6 fw-bold mb-4">Get In Touch</h2>
                    <p class="text-muted mb-5">Visit our corporate office or reach out via digital channels. We prioritize fast and transparent communication.</p>
                    
                    <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4" style="border-radius: 20px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light p-3 rounded-circle me-3 text-secondary">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Corporate Office</h6>
                                <p class="text-muted small mb-0">Stay Vibes Resort Pvt Ltd, Rajasthan, India</p>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4" style="border-radius: 20px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light p-3 rounded-circle me-3 text-secondary">
                                <i class="fas fa-phone-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Direct Hotline</h6>
                                <p class="text-muted small mb-0">+91 123 456 7890 (Mon - Sat, 10am - 6pm)</p>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4" style="border-radius: 20px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light p-3 rounded-circle me-3 text-secondary">
                                <i class="fas fa-envelope fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Support Email</h6>
                                <p class="text-muted small mb-0">support@stayvibesresort.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h6 class="fw-bold mb-3">Follow Our Journey:</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Form -->
            <div class="col-lg-7">
                <div class="premium-card p-3 p-md-5 bg-white border-0 shadow-lg" style="border-radius: 35px;">
                    <h4 class="fw-bold mb-4">Send an Inquiry</h4>
                    <?php echo $message_status; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control border shadow-sm bg-white py-3 px-4 rounded-4" placeholder="Your Name" style="border-color: #eee !important;" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Email Address</label>
                                <input type="email" name="email" class="form-control border shadow-sm bg-white py-3 px-4 rounded-4" placeholder="email@example.com" style="border-color: #eee !important;" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Phone Number</label>
                                <input type="tel" name="phone" class="form-control border shadow-sm bg-white py-3 px-4 rounded-4" placeholder="+91" style="border-color: #eee !important;" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Interested Plan</label>
                                <select name="plan" class="form-select border shadow-sm bg-white py-3 px-4 rounded-4" style="border-color: #eee !important;" required>
                                    <option value="">Select a Plan</option>
                                    <?php foreach($plans as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['plan_name']); ?>">
                                            <?php echo htmlspecialchars($p['plan_name']); ?> (₹<?php echo number_format($p['plan_price']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if(empty($plans)): ?>
                                        <option value="Other">Other / General Inquiry</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Your Message</label>
                                <textarea name="message" class="form-control border shadow-sm bg-white py-3 px-4 rounded-4" rows="5" placeholder="How can we help you?" style="border-color: #eee !important;" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-gold w-100 py-3 fw-bold rounded-4 shadow-sm" style="letter-spacing: 1px; font-size: 1.1rem;">SEND INQUIRY NOW</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Preview -->
<section class="section-padding bg-light pt-0">
    <div class="container">
        <div class="rounded-5 overflow-hidden shadow-sm" style="height: 400px; border: 10px solid white;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3557.4646738520!2d75.7667856!3d26.9124336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396db40f890d790d%3A0x6b7c3d2f2d2d2d2d!2sJaipur%2C%20Rajasthan!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
