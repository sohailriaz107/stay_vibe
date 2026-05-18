<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('login.php');
include('../includes/connect.php');



$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Security error.</div>';
    } else {
        // Update Personal or KYC Info
        if (isset($_POST['update_profile'])) {
            $full_name = sanitize_input($_POST['full_name']);
            $father_name = sanitize_input($_POST['father_name']);
            $pincode = sanitize_input($_POST['pincode']);
            $mobile_number = sanitize_input($_POST['mobile_number']);
            $email = sanitize_input($_POST['email']);
            $dob = sanitize_input($_POST['dob']);
            
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, father_name = ?, pincode = ?, mobile_number = ?, email = ?, dob = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $full_name, $father_name, $pincode, $mobile_number, $email, $dob, $user_id);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Profile updated successfully.</div>';
                $_SESSION['user_name'] = $full_name;
            }
            $stmt->close();
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<?php include('../includes/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include('../includes/dashboard_sidebar.php'); ?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="dashboard-header mb-4">
                    <h2 class="fw-bold mb-1">My Profile</h2>
                    <p class="text-muted small mb-0">Manage your personal information and banking details.</p>
                </div>

                <?php echo $message; ?>

                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-lg-12">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm">
                            <h5 class="fw-bold mb-4"><i class="fas fa-user-edit me-2 text-primary"></i> Personal Details</h5>
                            <form action="" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Full Name</label>
                                        <input type="text" name="full_name" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Mobile Number</label>
                                        <input type="text" name="mobile_number" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Email Address</label>
                                        <input type="email" name="email" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Father's Name</label>
                                        <input type="text" name="father_name" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['father_name']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Pincode</label>
                                        <input type="text" name="pincode" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['pincode']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Referral ID</label>
                                        <input type="text" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['reference_id']); ?>" disabled>
                                    </div>
                                    <div class="col-12 mt-4 text-center">
                                        <button type="submit" name="update_profile" class="btn btn-primary px-4">Update Details</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- KYC & Bank Details Link -->
                    <div class="col-lg-12">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-nowrap"><i class="fas fa-university me-2 text-primary"></i> KYC & Banking</h5>
                                    <p class="text-muted small mb-0">Manage your bank details for withdrawals.</p>
                                </div>
                                <div class="text-center text-md-end w-100 w-md-auto">
                                    <a href="bank-details.php" class="btn btn-outline-primary px-4 rounded-pill text-nowrap">Update Bank Info</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
