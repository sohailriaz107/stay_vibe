<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');

$user_id = $_SESSION['user_id'];
$message = '';

$stmt = $conn->prepare("
    SELECT u.*, 
           bd.account_number, bd.ifsc_code, bd.bank_name, bd.pan_number, bd.kyc_status,
           up.activation_date, up.end_date, up.next_payout_date, up.status as plan_status,
           p.plan_name as actual_plan_name, p.plan_price
    FROM users u
    LEFT JOIN bank_details bd ON u.id = bd.user_id
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    LEFT JOIN plans p ON up.plan_id = p.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Default values if table fields don't exist yet
$wallet_balance = $user['wallet_balance'] ?? 0;
$total_earnings = $user['total_earnings'] ?? 0;
$monthly_income = $user['monthly_income'] ?? 0;
$active_plan = $user['actual_plan_name'] ?? $user['active_plan'] ?? 'No Active Plan';

// Fetch Locked Balance (Total Approved Payments for Principal)
$lock_stmt = $conn->prepare("SELECT SUM(amount) as locked_bal FROM payments WHERE user_id = ? AND status = 'approved'");
$lock_stmt->bind_param("i", $user_id);
$lock_stmt->execute();
$lock_res = $lock_stmt->get_result()->fetch_assoc();
$locked_balance = $lock_res['locked_bal'] ?? 0;
$lock_stmt->close();

// Check if lockin period has passed
$three_years_passed = false;
$lockin_years = 3;
if (!empty($user['activation_date'])) {
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

    $lock_3year_end_date = date('Y-m-d', strtotime($user['activation_date'] . " +$lockin_years years"));
    if (date('Y-m-d') >= $lock_3year_end_date) {
        $three_years_passed = true;
    }
}

// Add unlocked principal to dashboard displayed wallet balance
if ($three_years_passed) {
    $wallet_balance += $locked_balance;
}

// Calculate Referral Earnings Dynamically
$sum_stmt = $conn->prepare("SELECT SUM(commission) as total FROM referrals WHERE user_id = ? AND status = 'approved'");
$sum_stmt->bind_param("i", $user_id);
$sum_stmt->execute();
$referral_earnings = $sum_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$sum_stmt->close();

// Check for pending payments
$stmt_pay = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$stmt_pay->bind_param("i", $user_id);
$stmt_pay->execute();
$pending_payment = $stmt_pay->get_result()->fetch_assoc();
$stmt_pay->close();
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <style>
    .app-grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }
    @media (max-width: 576px) {
        .app-grid-container {
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }
    }
    .app-menu-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.01);
    }
    .app-menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(201, 162, 39, 0.12);
        border-color: rgba(201, 162, 39, 0.2);
    }
    .app-icon-wrapper {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 1.4rem;
        transition: all 0.3s ease;
    }
    .app-menu-label {
        font-size: 0.82rem;
        font-weight: 650;
        color: #2d3748;
        text-align: center;
        margin-top: 4px;
        line-height: 1.2;
    }
    @media (max-width: 576px) {
        .app-icon-wrapper {
            width: 45px;
            height: 45px;
            font-size: 1.15rem;
            margin-bottom: 6px;
        }
        .app-menu-label {
            font-size: 0.7rem;
            font-weight: 600;
        }
        .app-menu-card {
            padding: 12px 4px;
            border-radius: 15px;
        }
    }
    </style>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Dashboard Overview</h2>
                        <p class="text-muted small mb-0">Welcome back, your investment is growing.</p>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <?php if ($user['status'] == 'active'): ?>
                            <span class="badge bg-success p-2 px-3 rounded-pill shadow-sm text-nowrap">
                                <i class="fas fa-check-circle me-1"></i> Account Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark p-2 px-3 rounded-pill shadow-sm text-nowrap">
                                <i class="fas fa-clock me-1"></i> Pending Verification
                            </span>
                        <?php endif; ?>
                        <div class="date-badge bg-white px-3 py-2 rounded-pill shadow-sm small fw-bold text-nowrap d-inline-block">
                            <i class="far fa-calendar-alt me-2 text-secondary"></i> <?php echo date('d M, Y'); ?>
                        </div>
                    </div>
                </div>

                <!-- App-Style Quick Navigation Grid (2x4 Circular Layout) -->
                

                <?php echo $message; ?>

                <?php if ($user['status'] === 'suspended'): ?>
                    <div class="premium-card p-5 bg-danger bg-opacity-10 shadow-sm border border-danger border-opacity-25 text-center mb-4" style="border-radius: 25px;">
                        <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(220, 53, 69, 0.1); border-radius: 50%; color: #dc3545; font-size: 1.8rem;">
                            <i class="fas fa-ban"></i>
                        </div>
                        <h4 class="fw-bold text-danger">ACCOUNT SUSPENDED</h4>
                        <p class="text-danger mb-0">Your account has been permanently or temporarily suspended by the Administration.</p>
                        <p class="text-danger small">You will no longer receive monthly profit lock-ins or referral commissions.</p>
                    </div>
                <?php elseif ($user['status'] === 'inactive'): ?>
                    <div class="premium-card p-5 bg-white shadow-sm border-0 text-center mb-4" style="border-radius: 25px;">
                        <?php if ($pending_payment): ?>
                            <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(255, 193, 7, 0.1); border-radius: 50%; color: #ffc107; font-size: 1.8rem;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4 class="fw-bold">Verification Pending</h4>
                            <p class="text-muted mb-4">We have received your payment for <strong><?php echo htmlspecialchars($pending_payment['plan_name']); ?></strong>. Our team is currently verifying your transaction. Please wait for 12-24 hours.</p>
                            <div class="small fw-bold text-uppercase" style="letter-spacing: 2px; color: var(--secondary-color);">Status: Pending</div>
                        <?php else: ?>
                            <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(201, 162, 39, 0.1); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h4 class="fw-bold">Activation Required</h4>
                            <p class="text-muted mb-4">Please choose a plan to activate your account and start earning.</p>
                            <a href="../pages/plans.php" class="btn btn-gold px-5 rounded-pill py-3 fw-bold">Choose Investment Plan</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div class="row g-4 gy-4 mb-4">
                    <!-- Financial Stats -->
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center">
                            <div class="icon-box p-3 rounded-circle bg-primary text-white mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                            <h6 class="text-muted small">Wallet Balance</h6>
                            <h3 class="fw-bold mb-0">₹<?php echo number_format($wallet_balance, 2); ?></h3>
                            <span class="text-success small fw-bold mt-2 d-inline-block"><i class="fas fa-arrow-up me-1"></i> Available</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center">
                            <div class="icon-box p-3 rounded-circle text-white mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background-color: var(--secondary-color);">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                            <h6 class="text-muted small">Total Earnings</h6>
                            <h3 class="fw-bold mb-0">₹<?php echo number_format($total_earnings, 2); ?></h3>
                            <span class="text-primary small fw-bold mt-2 d-inline-block">Lifetime</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center">
                            <div class="icon-box p-3 rounded-circle bg-info text-white mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-hand-holding-usd fa-lg"></i>
                            </div>
                            <h6 class="text-muted small">Monthly Rental</h6>
                            <h3 class="fw-bold mb-0">₹<?php echo number_format($monthly_income, 2); ?></h3>
                            <span class="text-muted small fw-bold mt-2 d-inline-block">This Month</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-muted small mb-2">Referral Earnings(Life Time)</h6>
                            <h4 class="fw-bold mb-0">₹<?php echo number_format($referral_earnings, 2); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-muted small mb-2">Active Plan</h6>
                            <h4 class="fw-bold mb-0 text-primary"><?php echo $active_plan; ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm h-100 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-muted small mb-2">Lock-in Period</h6>
                            <h4 class="fw-bold mb-0">3 Years</h4>
                            <small class="text-muted fw-normal mt-1" style="font-size: 0.7rem;">Countdown active</small>
                        </div>
                    </div>
                </div>

                <!-- KYC Status Bar -->
                <div class="premium-card p-4 bg-white border-0 shadow-sm mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-secondary" style="font-size: 1.5rem;"><i class="fas fa-id-card"></i></div>
                            <div>
                                <h6 class="mb-1 fw-bold">KYC Verification Status</h6>
                                <p class="mb-0 text-muted small">Update your PAN and Bank details to enable withdrawals.</p>
                            </div>
                        </div>
                        <div class="align-self-start align-self-md-auto">
                            <span class="badge <?php echo (($user['kyc_status'] ?? 'pending') == 'accepted') ? 'bg-success' : 'bg-danger'; ?> p-2 px-3 text-nowrap">
                                <i class="fas <?php echo (($user['kyc_status'] ?? 'pending') == 'accepted') ? 'fa-check-circle' : 'fa-clock'; ?> me-2"></i> 
                                <?php echo strtoupper($user['kyc_status'] ?? 'PENDING'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bank Details Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="premium-card p-4 bg-white border-0 shadow-sm">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                                <h5 class="fw-bold mb-0 text-nowrap"><i class="fas fa-university me-2 text-primary"></i> Bank Information</h5>
                                <a href="bank-details.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 text-nowrap align-self-start align-self-md-auto">
                                    <i class="fas fa-edit me-1"></i> Update Info
                                </a>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="text-muted small d-block mb-1">Bank Name</label>
                                    <span class="fw-bold"><?php echo htmlspecialchars($user['bank_name'] ?? 'Not Added'); ?></span>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="text-muted small d-block mb-1">Account Number</label>
                                    <span class="fw-bold"><?php echo htmlspecialchars($user['account_number'] ?? 'Not Added'); ?></span>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="text-muted small d-block mb-1">IFSC Code</label>
                                    <span class="fw-bold"><?php echo htmlspecialchars($user['ifsc_code'] ?? 'Not Added'); ?></span>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small d-block mb-1">PAN Number</label>
                                    <span class="fw-bold text-uppercase"><?php echo htmlspecialchars($user['pan_number'] ?? 'Not Added'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include('include/footer.php'); ?>


