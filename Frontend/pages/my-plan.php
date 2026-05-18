<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('login.php');
include('../includes/connect.php');



$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$has_plan = false;
$plan_details = null;
$activation_date = $user['created_at']; // fallback

// Check user_plans for active fully-paid plan
$stmt = $conn->prepare("
    SELECT up.activation_date as up_activation_date, up.end_date, up.next_payout_date, p.* 
    FROM user_plans up 
    JOIN plans p ON up.plan_id = p.id 
    WHERE up.user_id = ? AND up.status = 'active'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$plan_res = $stmt->get_result();

if ($plan_res && $plan_res->num_rows > 0) {
    $plan_details = $plan_res->fetch_assoc();
    $has_plan = true;
    if (!empty($plan_details['up_activation_date'])) {
        $activation_date = $plan_details['up_activation_date'];
    }
    // Override legacy string with actual database plan name
    $user['active_plan'] = $plan_details['plan_name'];
}
$stmt->close();

$start_date = new DateTime($activation_date);
$end_date = clone $start_date;
$lockin_years = $plan_details ? $plan_details['lockin_years'] : 3;
$end_date->modify("+$lockin_years years");

$now = new DateTime();
$total_days = $end_date->diff($start_date)->days;
$passed_days = $now->diff($start_date)->days;

if ($total_days == 0) $total_days = 1; // avoid division by zero

if ($now < $start_date) {
    $progress_percent = 0;
} elseif ($now > $end_date) {
    $progress_percent = 100;
} else {
    $progress_percent = min(100, max(0, ($passed_days / $total_days) * 100));
}

$months_remaining = 0;
if ($now < $end_date) {
    // Normalize to midnight to avoid time-based fractional month miscalculations
    $now_date = (clone $now)->setTime(0, 0, 0);
    $end_date_clean = (clone $end_date)->setTime(0, 0, 0);
    
    $interval = $now_date->diff($end_date_clean);
    $months_remaining = ($interval->y * 12) + $interval->m;
    
    // If there are remaining days, count it as a partial active month
    if ($interval->d > 0) {
        $months_remaining++;
    }
}

$ins_val = $plan_details ? $plan_details['insurance_amount'] : 0;
$insurance_text = ($ins_val >= 100000) ? '₹' . ($ins_val / 100000) . ' Lakh' : '₹' . number_format($ins_val);
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
                    <h2 class="fw-bold mb-1">Active Portfolio</h2>
                    <p class="text-muted small mb-0">Review your current investment plan and associated benefits.</p>
                </div>

                <?php if (!$has_plan): ?>
                    <div class="premium-card p-5 bg-white border-0 shadow-sm text-center">
                        <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(201, 162, 39, 0.1); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                            <i class="fas fa-search-dollar"></i>
                        </div>
                        <h4 class="fw-bold">No Active Plan Found</h4>
                        <p class="text-muted mb-4">You haven't activated any investment plan yet. Start your journey today to unlock passive income.</p>
                        <div class="text-center mt-2">
                            <a href="plans.php" class="btn btn-gold btn-sm px-4 py-2 shadow-sm rounded-pill">Explore Investment Plans</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <div class="col-lg-12">
                            <div class="premium-card p-5 bg-white border-0 shadow-sm overflow-hidden position-relative">
                                <div class="badge bg-success position-absolute top-0 end-0 m-4 p-2 px-3">ACTIVE</div>
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h6 class="text-secondary text-uppercase fw-bold mb-2">My Current Plan</h6>
                                        <h2 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($user['active_plan'] ?? 'Premium Plan B'); ?></h2>
                                        <div class="d-flex align-items-baseline mb-4">
                                            <span class="h2 fw-bold mb-0">₹<?php echo number_format($plan_details['plan_price'] ?? 0); ?></span>
                                            <span class="text-muted ms-2">Investment Amount</span>
                                        </div>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-6">
                                                <div class="small text-muted">Activation Date</div>
                                                <div class="fw-bold"><?php echo $start_date->format('d M Y'); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="small text-muted">Lock-in Expiry</div>
                                                <div class="fw-bold"><?php echo $end_date->format('d M Y'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="p-4 rounded-4" style="background: var(--bg-light);">
                                            <h6 class="fw-bold mb-3">Portfolio Benefits</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2 small d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i> <?php echo $plan_details['yearly_return_percent'] ?? 0; ?>% Annual Rental</li>
                                                <li class="mb-2 small d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i> <?php echo $plan_details['free_stay_nights'] ?? 0; ?>N/<?php echo $plan_details['free_stay_days'] ?? 0; ?>D Free Stay / Year</li>
                                                <li class="mb-2 small d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i> <?php echo $insurance_text; ?> Insurance</li>
                                                <li class="mb-0 small d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i> <?php echo $plan_details['referral_percent'] ?? 0; ?>% Referral Bonus</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lock-in Timer Visual -->
                        <div class="col-lg-12">
                            <div class="premium-card p-4 bg-white border-0 shadow-sm">
                                <h5 class="fw-bold mb-4">Lock-in Progress</h5>
                                <div class="progress mb-3" style="height: 10px; border-radius: 10px;">
                                    <div class="progress-bar bg-gold" role="progressbar" style="width: <?php echo $progress_percent; ?>%;" aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Started: <?php echo $start_date->format('d M Y'); ?></span>
                                    <span><?php echo $months_remaining; ?> Months Remaining</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
