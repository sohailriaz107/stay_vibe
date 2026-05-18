<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('login.php');
include('../includes/connect.php');



$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.wallet_balance, bd.kyc_status, up.activation_date 
    FROM users u 
    LEFT JOIN bank_details bd ON u.id = bd.user_id 
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$balance = $user['wallet_balance'] ?? 0;
$kyc_verified = (($user['kyc_status'] ?? 'pending') == 'accepted');
$activation_date = $user['activation_date'] ?? null;

// Calculate available referral earnings
$ref_stmt = $conn->prepare("SELECT SUM(commission) as tot FROM referrals WHERE user_id = ? AND status = 'approved'");
$ref_stmt->bind_param("i", $user_id);
$ref_stmt->execute();
$total_refs = $ref_stmt->get_result()->fetch_assoc()['tot'] ?? 0;
$ref_stmt->close();

$with_stmt = $conn->prepare("SELECT SUM(amount) as tot FROM withdrawals WHERE user_id = ? AND status IN ('pending', 'approved')");
$with_stmt->bind_param("i", $user_id);
$with_stmt->execute();
$total_withdrawn = $with_stmt->get_result()->fetch_assoc()['tot'] ?? 0;
$with_stmt->close();

$available_referral = max(0, $total_refs - $total_withdrawn);

// Fetch Locked Balance (Total Approved Payments for Principal)
$lock_stmt = $conn->prepare("SELECT SUM(amount) as locked_bal FROM payments WHERE user_id = ? AND status = 'approved'");
$lock_stmt->bind_param("i", $user_id);
$lock_stmt->execute();
$lock_res = $lock_stmt->get_result()->fetch_assoc();
$locked_balance = $lock_res['locked_bal'] ?? 0;
$lock_stmt->close();

// Fetch lock-in years of active plan
$lockin_years = 3;
if ($activation_date) {
    $plan_check = $conn->prepare("
        SELECT p.lockin_years 
        FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        WHERE up.user_id = ? AND up.status = 'active'
        LIMIT 1
    ");
    $plan_check->bind_param("i", $user_id);
    $plan_check->execute();
    $p_res = $plan_check->get_result()->fetch_assoc();
    if ($p_res) {
        $lockin_years = $p_res['lockin_years'] ?? 3;
    }
    $plan_check->close();
}

// Check if 3 months have passed
$three_months_passed = false;
if ($activation_date) {
    $lock_end_date = date('Y-m-d', strtotime($activation_date . ' +3 months'));
    if (date('Y-m-d') >= $lock_end_date) {
        $three_months_passed = true;
    }
}

// Check if 3 years (lock-in period) have passed
$three_years_passed = false;
if ($activation_date) {
    $lock_3year_end_date = date('Y-m-d', strtotime($activation_date . " +$lockin_years years"));
    if (date('Y-m-d') >= $lock_3year_end_date) {
        $three_years_passed = true;
    }
}

// Calculate MAX withdrawable
$max_withdrawable = $balance;
if (!$three_months_passed) {
    $max_withdrawable = min($balance, $available_referral);
}

// If 3 years have passed, principal is unlocked and added to Available Balance and Max Withdrawable
if ($three_years_passed) {
    $balance += $locked_balance;
    $max_withdrawable += $locked_balance;
    $locked_balance = 0;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_withdrawal'])) {
    if (!$kyc_verified) {
        $message = '<div class="alert alert-danger">Please complete your KYC verification first.</div>';
    } else {
        $amount = (float)sanitize_input($_POST['amount']);
        
        if ($amount > $max_withdrawable) {
            if (!$three_months_passed && $balance > $max_withdrawable) {
                $message = '<div class="alert alert-danger">Your rental income is locked for 3 months. You can only withdraw your available referral earnings (₹' . number_format($max_withdrawable, 2) . ').</div>';
            } else {
                $message = '<div class="alert alert-danger">Insufficient balance.</div>';
            }
        } elseif ($amount < 5000 && $amount > $available_referral) {
            $message = '<div class="alert alert-danger">Minimum withdrawal amount is ₹5,000 (except for Referral Earnings). Your available referral earning is only ₹' . number_format($available_referral, 2) . '.</div>';
        } else {
            $fee = $amount * 0.10;
            $final_amount = $amount - $fee;
            
            // Insert into withdrawals table
            $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, fee, final_amount, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iddd", $user_id, $amount, $fee, $final_amount);
            
            if ($stmt->execute()) {
                // Debit from wallet
                $up_stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
                $up_stmt->bind_param("di", $amount, $user_id);
                $up_stmt->execute();
                
                $message = '<div class="alert alert-success">Withdrawal request of ₹' . number_format($amount, 2) . ' submitted successfully.</div>';
                $balance -= $amount; // Update displayed balance
            } else {
                $message = '<div class="alert alert-danger">Error submitting withdrawal request. Please try again.</div>';
            }
        }
    }
}
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
                    <h2 class="fw-bold mb-1">Request Withdrawal</h2>
                    <p class="text-muted small mb-0">Withdraw your earned rental and referral income directly to your bank account.</p>
                </div>

                <?php echo $message; ?>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100">
                            <h5 class="fw-bold mb-4">Withdrawal Form</h5>
                            <form action="" method="POST" id="withdrawForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Available Balance</label>
                                    <div class="h3 fw-bold text-primary mb-0">₹<?php echo number_format($balance, 2); ?></div>
                                    <?php if(!$three_months_passed && $balance > $max_withdrawable): ?>
                                        <div class="small text-danger mt-2 fw-bold"><i class="fas fa-lock me-1"></i> Rental income is locked for 3 months.</div>
                                        <div class="small text-success mt-1"><i class="fas fa-check-circle me-1"></i> Max withdrawable (Referrals only): ₹<?php echo number_format($max_withdrawable, 2); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Withdrawal Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₹</span>
                                        <input type="number" name="amount" id="withdrawAmount" class="form-control form-control-lg bg-light border-0" placeholder="Enter amount" required>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">Note: Min. ₹5,000 for Rental. <span class="text-success fw-bold">No minimum limit for Referral Earnings!</span> (10% fee applies)</p>
                                </div>
                                
                                <div class="p-3 rounded-4 mb-4" style="background: rgba(201, 162, 39, 0.05);">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small text-muted">Processing Fee (10%)</span>
                                        <span class="small fw-bold text-danger" id="feeDisplay">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Final Payout Amount</span>
                                        <span class="fw-bold text-success" id="finalDisplay">₹0.00</span>
                                    </div>
                                </div>

                                <button type="submit" name="request_withdrawal" class="btn btn-gold btn-lg w-100 py-3 <?php echo !$kyc_verified ? 'disabled' : ''; ?>">
                                    Confirm Withdrawal Request
                                </button>
                                <?php if (!$kyc_verified): ?>
                                    <p class="text-danger small text-center mt-3"><i class="fas fa-lock me-1"></i> Complete KYC in <a href="profile.php">Profile</a> to enable.</p>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100">
                            <h5 class="fw-bold mb-4">Important Guidelines</h5>
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-info-circle text-primary mt-1 me-3"></i>
                                    <span>Minimum withdrawal amount is ₹5,000 per request. <strong class="text-success">(Exception: Referral Earnings have NO minimum limit and can be withdrawn anytime!)</strong></span>
                                </li>
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-info-circle text-primary mt-1 me-3"></i>
                                    <span>Withdrawals are processed within 3-5 working days.</span>
                                </li>
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-info-circle text-primary mt-1 me-3"></i>
                                    <span>A flat 10% fee is deducted from each transaction for platform maintenance.</span>
                                </li>
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-info-circle text-primary mt-1 me-3"></i>
                                    <span>Ensure your bank details in the profile section are accurate.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm mt-4">
                            <h5 class="fw-bold mb-4">Withdrawal History</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-muted small">
                                            <th class="border-0">Request Date</th>
                                            <th class="border-0">Amount</th>
                                            <th class="border-0">Fee</th>
                                            <th class="border-0">Payout</th>
                                            <th class="border-0 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $history_stmt = $conn->prepare("SELECT amount, fee, final_amount, status, created_at FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
                                        $history_stmt->bind_param("i", $user_id);
                                        $history_stmt->execute();
                                        $history_res = $history_stmt->get_result();

                                        if ($history_res->num_rows > 0):
                                            while ($hist = $history_res->fetch_assoc()):
                                                $st_class = $hist['status'] == 'pending' ? 'text-warning' : ($hist['status'] == 'approved' ? 'text-success' : 'text-danger');
                                        ?>
                                            <tr>
                                                <td class="text-muted"><?php echo date('d M Y', strtotime($hist['created_at'])); ?></td>
                                                <td class="fw-bold">₹<?php echo number_format($hist['amount'], 2); ?></td>
                                                <td class="text-danger small">₹<?php echo number_format($hist['fee'], 2); ?></td>
                                                <td class="fw-bold text-success">₹<?php echo number_format($hist['final_amount'], 2); ?></td>
                                                <td class="text-center fw-bold <?php echo $st_class; ?>"><?php echo ucfirst($hist['status']); ?></td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else: 
                                        ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No withdrawal history found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('withdrawAmount').addEventListener('input', function(e) {
    let amount = parseFloat(e.target.value) || 0;
    let fee = amount * 0.10;
    let final = amount - fee;
    
    document.getElementById('feeDisplay').innerText = '₹' + fee.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('finalDisplay').innerText = '₹' + final.toLocaleString(undefined, {minimumFractionDigits: 2});
});
</script>

<?php include('../includes/footer.php'); ?>
