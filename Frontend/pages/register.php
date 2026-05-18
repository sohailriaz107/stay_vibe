<?php 
$is_subpage = true;
include('../includes/header.php'); 
include('../includes/connect.php');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Security error: CSRF token mismatch.</div>';
    } else {
        // Sanitize inputs
        $full_name = sanitize_input($_POST['full_name']);
        $mobile = sanitize_input($_POST['mobile']);
        $father_name = sanitize_input($_POST['father_name']);
        $dob = sanitize_input($_POST['dob']);
        $email = sanitize_input($_POST['email']);
        $pincode = sanitize_input($_POST['pincode']);
        $reference_id = sanitize_input($_POST['reference_id']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = '<div class="alert alert-danger">Email already registered.</div>';
        } else {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, mobile_number, father_name, dob, email, pincode, reference_id, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $full_name, $mobile, $father_name, $dob, $email, $pincode, $reference_id, $password);
            
            if ($stmt->execute()) {
                $new_user_id = $conn->insert_id;
                
                // Add pending referral if reference_id provided
                if (!empty($reference_id)) {
                    $search_id = $reference_id;
                    if (strtoupper(substr($reference_id, 0, 2)) === 'SV') {
                        $search_id = (int)substr($reference_id, 2);
                    }
                    $ref_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? OR email = ? LIMIT 1");
                    $ref_stmt->bind_param("ss", $search_id, $reference_id);
                    $ref_stmt->execute();
                    $ref_res = $ref_stmt->get_result();
                    if ($ref_res && $ref_res->num_rows > 0) {
                        $referrer = $ref_res->fetch_assoc();
                        $referrer_id = $referrer['id'];
                        $conn->query("INSERT INTO referrals (user_id, referred_user_id, commission, status) VALUES ($referrer_id, $new_user_id, 0, 'pending')");
                    }
                }

                // Auto-login: set session variables and redirect to dashboard
                $_SESSION['user_id']     = $new_user_id;
                $_SESSION['user_name']   = $full_name;
                $_SESSION['user_status'] = 'inactive';
                header("Location: ../App/dashboard.php");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Error: Could not create account. Please try again.</div>';
            }
        }
        $stmt->close();
    }
}
?>

<section class="section-padding bg-light" style="min-height: 100vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 px-2 px-sm-3">
                <div class="premium-card p-3 p-sm-5 bg-white shadow-lg border-0" style="border-radius: 30px;">
                    <div class="text-center mb-4">
                        <a href="../index.php">
                            <img src="../assets/imgs/logo.png" alt="Stay Vibes Logo" class="mb-3" style="max-height: 70px; object-fit: contain;">
                        </a>
                        <h2 class="fw-bold">Create Investor Account</h2>
                        <p class="text-muted">Join Stay Vibes Resort and start your investment journey.</p>
                    </div>

                    <?php echo $message; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row gx-2 gy-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Enter your full name" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="10-digit mobile number" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Father's Name</label>
                                <input type="text" name="father_name" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Father's name" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Date of Birth</label>
                                <input type="date" name="dob" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="email@example.com" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pincode</label>
                                <input type="text" name="pincode" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="6-digit pincode" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Reference ID (Optional)</label>
                                <input type="text" name="reference_id" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Referrer ID" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>" style="font-size: 0.95rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Create Password</label>
                                <input type="password" name="password" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Min 8 characters" required style="font-size: 0.95rem;">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-gold w-100 py-2.5 fw-bold text-uppercase" style="border-radius: 12px; font-size: 0.95rem; letter-spacing: 0.5px;">Create My Account</button>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <p class="text-muted small">Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
