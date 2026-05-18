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
        if (isset($_POST['update_bank'])) {
            $pan = sanitize_input($_POST['pan_number']);
            $bank = sanitize_input($_POST['bank_name']);
            $acc = sanitize_input($_POST['account_number']);
            $ifsc = sanitize_input($_POST['ifsc_code']);
            
            // Insert or Update bank_details
            $check = $conn->query("SELECT id FROM bank_details WHERE user_id = $user_id");
            if ($check && $check->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE bank_details SET pan_number = ?, bank_name = ?, account_number = ?, ifsc_code = ?, kyc_status = 'pending' WHERE user_id = ?");
                $stmt->bind_param("ssssi", $pan, $bank, $acc, $ifsc, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO bank_details (pan_number, bank_name, account_number, ifsc_code, kyc_status, user_id) VALUES (?, ?, ?, ?, 'pending', ?)");
                $stmt->bind_param("ssssi", $pan, $bank, $acc, $ifsc, $user_id);
            }
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Bank & KYC details updated successfully and submitted for verification.</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating details.</div>';
            }
            $stmt->close();
        }
    }
}

$stmt = $conn->prepare("
    SELECT u.*, 
           bd.account_number, bd.ifsc_code, bd.bank_name, bd.pan_number, bd.kyc_status 
    FROM users u 
    LEFT JOIN bank_details bd ON u.id = bd.user_id 
    WHERE u.id = ?
");
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
                    <h2 class="fw-bold mb-1">Bank & KYC Details</h2>
                    <p class="text-muted small mb-0">Update your banking information for withdrawals.</p>
                </div>

                <?php echo $message; ?>

                <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4">
                    <div class="text-center text-md-start mb-4">
                        <h5 class="fw-bold mb-2"><i class="fas fa-university me-2 text-primary"></i> Banking Information</h5>
                        <span class="badge <?php echo (($user['kyc_status'] ?? 'pending') == 'accepted') ? 'bg-success' : 'bg-warning text-dark'; ?> p-2 px-3 text-nowrap">
                            KYC Status: <?php echo strtoupper($user['kyc_status'] ?? 'PENDING'); ?>
                        </span>
                    </div>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">PAN Card Number</label>
                                <input type="text" name="pan_number" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['pan_number'] ?? ''); ?>" placeholder="ABCDE1234F" required>
                                <div class="form-text x-small">Required for tax verification.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>" placeholder="e.g. HDFC Bank, SBI, etc." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Account Number</label>
                                <input type="text" name="account_number" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['account_number'] ?? ''); ?>" placeholder="Enter your full account number" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($user['ifsc_code'] ?? ''); ?>" placeholder="HDFC0001234" required>
                            </div>
                            
                            <div class="col-12 mt-4 text-center">
                                <button type="submit" name="update_bank" class="btn btn-gold btn-sm px-4 py-2 fw-bold rounded-pill shadow-sm" style="min-width: 220px; max-width: 100%;font-size: 13px;">Save & Submit for Verification</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="alert alert-info border-0 rounded-4 p-4">
                    <div class="d-flex">
                        <div class="me-3 fs-4"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Why do we need this?</h6>
                            <p class="small mb-0 opacity-75">Your bank details are required to process your monthly rental income withdrawals. PAN details are mandatory as per government regulations for investment platforms. All data is encrypted and secure.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
