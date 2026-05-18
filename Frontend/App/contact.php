<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
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
        $name    = sanitize_input($_POST['full_name']);
        $email   = sanitize_input($_POST['email']);
        $phone   = sanitize_input($_POST['phone']);
        $plan    = sanitize_input($_POST['plan']);
        $msg     = sanitize_input($_POST['message']);
        
        $stmt = $conn->prepare("INSERT INTO contact_inquiries (full_name, email, phone, interested_plan, message) VALUES (?, ?, ?, ?, ?)");
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
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
                    <h2 class="fw-bold mb-1">Support & Help Desk</h2>
                    <p class="text-muted small mb-0">Get in touch with our luxury resort portfolio experts.</p>
                </div>

                <div class="row g-3">
                    <!-- Contact Info Cards -->
                    <div class="col-lg-5">
                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm mb-3 text-center text-sm-start" style="border-radius: 20px;">
                            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                                <div class="icon-box bg-light p-3 rounded-circle text-secondary">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Corporate Office</h6>
                                    <p class="text-muted small mb-0">Stay Vibes Resort Pvt Ltd, Rajasthan, India</p>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm mb-3 text-center text-sm-start" style="border-radius: 20px;">
                            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                                <div class="icon-box bg-light p-3 rounded-circle text-secondary">
                                    <i class="fas fa-phone-alt fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Direct Hotline</h6>
                                    <p class="text-muted small mb-0">+91 123 456 7890 (Mon - Sat, 10am - 6pm)</p>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm mb-3 text-center text-sm-start" style="border-radius: 20px;">
                            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                                <div class="icon-box bg-light p-3 rounded-circle text-secondary">
                                    <i class="fas fa-envelope fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Support Email</h6>
                                    <p class="text-muted small mb-0">support@stayvibesresort.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <h6 class="fw-bold mb-3 small text-secondary">Follow Our Journey:</h6>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f text-secondary"></i></a>
                                <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-instagram text-secondary"></i></a>
                                <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-linkedin-in text-secondary"></i></a>
                                <a href="#" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-twitter text-secondary"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Form -->
                    <div class="col-lg-7">
                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-4 text-center" style="font-size: 1.1rem;">Send an Inquiry</h5>
                            <?php echo $message_status; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Full Name</label>
                                        <input type="text" name="full_name" class="form-control bg-light border-0" placeholder="Your Name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-dark">Email Address</label>
                                        <input type="email" name="email" class="form-control bg-light border-0" placeholder="email@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-dark">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control bg-light border-0" placeholder="+91" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Interested Plan</label>
                                        <select name="plan" class="form-select bg-light border-0" required>
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
                                        <textarea name="message" class="form-control bg-light border-0" rows="4" placeholder="How can we help you?" required></textarea>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-gold btn-sm px-4 py-2.5 fw-bold rounded-pill shadow-sm" style="font-size: 13px; min-width: 220px; max-width: 100%;">SEND INQUIRY NOW</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Location Preview Map -->
                    <div class="col-12 mt-3">
                        <div class="premium-card p-2 bg-white border-0 shadow-sm" style="border-radius: 20px;">
                            <div class="rounded-4 overflow-hidden" style="height: 250px;">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3557.4646738520!2d75.7667856!3d26.9124336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396db40f890d790d%3A0x6b7c3d2f2d2d2d2d!2sJaipur%2C%20Rajasthan!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include('include/footer.php'); ?>
