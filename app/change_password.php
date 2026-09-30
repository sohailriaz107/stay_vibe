<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i> Security error: CSRF token mismatch.</div>';
    } else {
        if (isset($_POST['change_password'])) {
            $current_pass = $_POST['current_password'];
            $new_pass     = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];
            
            // Basic validation
            if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
                $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> Please fill in all fields.</div>';
            } elseif (strlen($new_pass) < 8) {
                $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> New password must be at least 8 characters long.</div>';
            } elseif ($new_pass !== $confirm_pass) {
                $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> New passwords do not match.</div>';
            } else {
                // Fetch current hashed password from DB
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $res = $stmt->get_result();
                
                if ($res && $res->num_rows > 0) {
                    $user = $res->fetch_assoc();
                    $hashed_pass = $user['password'];
                    
                    // Verify old password
                    if (!password_verify($current_pass, $hashed_pass)) {
                        $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-times-circle me-2"></i> Current password is incorrect.</div>';
                    } elseif ($current_pass === $new_pass) {
                        $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i> New password cannot be the same as your current password.</div>';
                    } else {
                        // Hash new password and update
                        $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $new_hashed_pass, $user_id);
                        
                        if ($update_stmt->execute()) {
                            $message = '<div class="alert alert-success rounded-4 shadow-sm"><i class="fas fa-check-circle me-2"></i> Password updated successfully!</div>';
                        } else {
                            $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> Error updating password. Please try again.</div>';
                        }
                        $update_stmt->close();
                    }
                } else {
                    $message = '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> User account not found.</div>';
                }
                $stmt->close();
            }
        }
    }
}
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
                    <h2 class="fw-bold mb-1">Change Password</h2>
                    <p class="text-muted small mb-0">Secure your account by updating your credentials regularly.</p>
                </div>

                <?php echo $message; ?>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-7">
                        <div class="premium-card p-3 px-2 px-sm-4 py-sm-4 bg-white border-0 shadow-sm mb-4" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-4 text-center"><i class="fas fa-key me-2 text-primary"></i> Account Security</h5>
                            
                            <form action="" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light text-muted"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="current_password" class="form-control bg-light border-0" placeholder="Enter current password" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light text-muted"><i class="fas fa-key"></i></span>
                                        <input type="password" name="new_password" class="form-control bg-light border-0" placeholder="Min 8 characters" required>
                                    </div>
                                    <div class="form-text x-small text-muted mt-1">Must contain at least 8 characters.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light text-muted"><i class="fas fa-check-double"></i></span>
                                        <input type="password" name="confirm_password" class="form-control bg-light border-0" placeholder="Repeat new password" required>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="change_password" class="btn btn-gold btn-sm px-4 py-2.5 fw-bold rounded-pill shadow-sm" style="min-width: 220px; max-width: 100%; font-size: 13px;">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include('include/footer.php'); ?>
