<?php 
require_once('../includes/connect.php');
// include('includes/auth.php'); // Ensure this exists and is uncommented soon
include('includes/header.php'); 
include('includes/sidebar.php'); 

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

// Handle Site Settings Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_site_settings'])) {
    $popup_enabled = isset($_POST['popup_enabled']) ? '1' : '0';
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('popup_enabled', '$popup_enabled') ON DUPLICATE KEY UPDATE setting_value='$popup_enabled'");
    // Handle Image Upload
    if (isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["popup_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["popup_image"]["tmp_name"]);
        
        if($check !== false) {
            if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $target_file)) {
                $image_path = "assets/uploads/" . $file_name;
                $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('popup_image', '$image_path') ON DUPLICATE KEY UPDATE setting_value='$image_path'");
            }
        }
    }
    
    $msg = '<div class="alert alert-success">Site Settings updated successfully.</div>';
}

// Handle Summary Settings Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_summary_settings'])) {
    $real_estate = $conn->real_escape_string($_POST['summary_real_estate']);
    $lease_hotels = $conn->real_escape_string($_POST['summary_lease_hotels']);
    $others = $conn->real_escape_string($_POST['summary_others']);
    $interest = $conn->real_escape_string($_POST['summary_interest']);
    $office_exp = $conn->real_escape_string($_POST['summary_office_exp']);
    
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('summary_real_estate', '$real_estate') ON DUPLICATE KEY UPDATE setting_value='$real_estate'");
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('summary_lease_hotels', '$lease_hotels') ON DUPLICATE KEY UPDATE setting_value='$lease_hotels'");
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('summary_others', '$others') ON DUPLICATE KEY UPDATE setting_value='$others'");
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('summary_interest', '$interest') ON DUPLICATE KEY UPDATE setting_value='$interest'");
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('summary_office_exp', '$office_exp') ON DUPLICATE KEY UPDATE setting_value='$office_exp'");
    
    $msg = '<div class="alert alert-success">Summary Percentages updated successfully.</div>';
}

// Fetch Admin Data
$admin_data = $conn->query("SELECT * FROM admins WHERE id = $admin_id")->fetch_assoc();

// Fetch Site Settings
$settings_res = $conn->query("SELECT * FROM site_settings");
$site_settings = [];
while ($row = $settings_res->fetch_assoc()) {
    $site_settings[$row['setting_key']] = $row['setting_value'];
}
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
        
        <!-- Site Settings -->
        <div class="col-lg-6 mt-4">
            <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4"><i class="fas fa-cogs text-info me-2"></i> Site Settings</h5>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="popup_enabled" name="popup_enabled" <?php echo (isset($site_settings['popup_enabled']) && $site_settings['popup_enabled'] == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold text-muted" for="popup_enabled">Enable Home Page Popup</label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Popup Image</label>
                        <?php if(!empty($site_settings['popup_image'])): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $site_settings['popup_image']; ?>" alt="Popup Image" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="popup_image" class="form-control bg-light border-0 py-2" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image.</small>
                    </div>
                    <button type="submit" name="update_site_settings" class="btn btn-info text-white w-100 py-2 rounded-pill">Update Site Settings</button>
                </form>
            </div>
        </div>

        <!-- Payment Summary Settings -->
        <div class="col-lg-6 mt-4">
            <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4"><i class="fas fa-chart-pie text-warning me-2"></i> Payment Summary Percentages</h5>
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Real Estate (%)</label>
                            <input type="number" step="0.01" name="summary_real_estate" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($site_settings['summary_real_estate'] ?? '45'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Lease Hotels (%)</label>
                            <input type="number" step="0.01" name="summary_lease_hotels" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($site_settings['summary_lease_hotels'] ?? '20'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Others (%)</label>
                            <input type="number" step="0.01" name="summary_others" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($site_settings['summary_others'] ?? '15'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Interest Payment (%)</label>
                            <input type="number" step="0.01" name="summary_interest" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($site_settings['summary_interest'] ?? '10'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label small fw-bold text-muted">Office Expense (%)</label>
                            <input type="number" step="0.01" name="summary_office_exp" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($site_settings['summary_office_exp'] ?? '10'); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_summary_settings" class="btn btn-warning text-white w-100 py-2 rounded-pill">Update Percentages</button>
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
