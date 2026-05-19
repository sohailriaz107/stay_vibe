<?php 
// include('includes/auth.php'); // Ensure this exists and is uncommented soon
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

$msg = '';

// For now, assume admin ID is 1 (or fetch from session if implemented)
$admin_id = 1; 

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, phone=?, address=?, password=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $address, $hashed, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $admin_id);
    }
    
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Profile updated successfully.</div>';
    }
}

// Handle Bank Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bank'])) {
    $account_name = sanitize_input($_POST['company_account_name']);
    $bank_name = sanitize_input($_POST['company_bank_name']);
    $account_number = sanitize_input($_POST['company_account_number']);
    $ifsc = sanitize_input($_POST['company_ifsc']);
    $upi = sanitize_input($_POST['company_upi']);
    
    $stmt = $conn->prepare("UPDATE admins SET company_account_name=?, company_bank_name=?, company_account_number=?, company_ifsc=?, company_upi=? WHERE id=?");
    $stmt->bind_param("sssssi", $account_name, $bank_name, $account_number, $ifsc, $upi, $admin_id);
    
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Company Bank Details updated successfully.</div>';
    }
}

// Fetch Admin Data
$admin_data = $conn->query("SELECT * FROM admins WHERE id = $admin_id")->fetch_assoc();
?>

<div id="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-5 w-100">
        <div>
            <h2 class="fw-bold mb-1">Settings</h2>
            <p class="text-muted">Manage your admin profile and company bank information.</p>
        </div>
    </div>

    <?php echo $msg; ?>

    <div class="row g-4">
        <!-- Admin Profile Settings -->
        <div class="col-lg-6">
            <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4"><i class="fas fa-user-shield text-primary me-2"></i> Admin Profile</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Phone Number</label>
                        <input type="text" name="phone" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Admin Address</label>
                        <textarea name="address" class="form-control bg-light border-0 py-2" rows="2" placeholder="Enter company or admin address"><?php echo htmlspecialchars($admin_data['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">New Password <span class="fw-normal text-secondary">(Leave blank to keep current)</span></label>
                        <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Enter new password">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary w-100 py-2 rounded-pill">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Company Bank Details -->
        <div class="col-lg-6">
            <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4"><i class="fas fa-university text-success me-2"></i> Company Bank Details</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Account Name</label>
                        <input type="text" name="company_account_name" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['company_account_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Bank Name</label>
                        <input type="text" name="company_bank_name" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['company_bank_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Account Number</label>
                        <input type="text" name="company_account_number" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['company_account_number'] ?? ''); ?>" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">IFSC Code</label>
                            <input type="text" name="company_ifsc" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['company_ifsc'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">UPI ID (Optional)</label>
                            <input type="text" name="company_upi" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($admin_data['company_upi'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_bank" class="btn btn-success w-100 py-2 rounded-pill">Update Bank Details</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// include('includes/footer.php'); // We removed the JS logic from here, so it's fine not to include if not needed, but header needs closing tags.
?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
