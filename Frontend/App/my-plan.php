<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
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
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
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
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <!-- Premium Compact Plan Card -->
                            <div class="premium-card p-3 p-sm-4 bg-white border-0 shadow-sm overflow-hidden position-relative" style="border-radius: 20px;">
                                <!-- Top-Right Absolute Badge -->
                                <span class="badge bg-success px-3 py-1.5 rounded-pill position-absolute" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; top: 20px; right: 20px; z-index: 5;">ACTIVE</span>
                                
                                <div class="row align-items-stretch g-3">
                                    <div class="col-md-7 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="text-secondary text-uppercase fw-bold mb-2" style="font-size: 0.78rem; letter-spacing: 0.5px;">My Current Plan</h6>
                                            <h2 class="fw-extrabold mb-3 text-dark" style="font-weight: 800; font-size: calc(1.35rem + 0.6vw); letter-spacing: -0.5px;"><?php echo htmlspecialchars($user['active_plan'] ?? 'Premium Plan B'); ?></h2>
                                        </div>

                                        <div class="bg-light p-3 rounded-4 mb-3 text-center" style="border: 1px solid rgba(0,0,0,0.02);">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">Investment Amount</div>
                                            <div class="h3 fw-extrabold text-primary mb-0" style="font-weight: 800;">₹<?php echo number_format($plan_details['plan_price'] ?? 0); ?></div>
                                        </div>
                                        
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="p-2.5 bg-light rounded-4 h-100 text-center" style="border: 1px solid rgba(0,0,0,0.01); padding: 10px 5px;">
                                                    <div class="small text-muted mb-1" style="font-size: 0.7rem; font-weight: 600;">Activation Date</div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.82rem;"><?php echo $start_date->format('d M Y'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2.5 bg-light rounded-4 h-100 text-center" style="border: 1px solid rgba(0,0,0,0.01); padding: 10px 5px;">
                                                    <div class="small text-muted mb-1" style="font-size: 0.7rem; font-weight: 600;">Lock-in Expiry</div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.82rem;"><?php echo $end_date->format('d M Y'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="p-3 rounded-4 h-100 d-flex flex-column justify-content-center" style="background: rgba(201, 162, 39, 0.03); border: 1px dashed rgba(201, 162, 39, 0.25);">
                                            <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.82rem;"><i class="fas fa-gem me-2 text-warning"></i>Portfolio Benefits</h6>
                                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                                <li class="small d-flex align-items-center" style="font-size: 0.82rem; color: #4a5568;"><i class="fas fa-check-circle text-success me-2 fs-6"></i><span><strong><?php echo $plan_details['yearly_return_percent'] ?? 0; ?>%</strong> Annual Rental</span></li>
                                                <li class="small d-flex align-items-center" style="font-size: 0.82rem; color: #4a5568;"><i class="fas fa-check-circle text-success me-2 fs-6"></i><span><strong><?php echo $plan_details['free_stay_nights'] ?? 0; ?>N/<?php echo $plan_details['free_stay_days'] ?? 0; ?>D</strong> Free Stay / Year</span></li>
                                                <li class="small d-flex align-items-center" style="font-size: 0.82rem; color: #4a5568;"><i class="fas fa-check-circle text-success me-2 fs-6"></i><span><strong><?php echo $insurance_text; ?></strong> Insurance</span></li>
                                                <li class="small d-flex align-items-center" style="font-size: 0.82rem; color: #4a5568;"><i class="fas fa-check-circle text-success me-2 fs-6"></i><span><strong><?php echo $plan_details['referral_percent'] ?? 0; ?>%</strong> Referral Bonus</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lock-in Timer Visual -->
                        <div class="col-lg-12">
                            <div class="premium-card p-3 p-md-4 bg-white border-0 shadow-sm" style="border-radius: 20px;">
                                <h6 class="fw-bold mb-3" style="font-size: 0.88rem; color: #2d3748;"><i class="fas fa-hourglass-half text-warning me-2"></i>Lock-in Progress</h6>
                                <div class="progress mb-2" style="height: 8px; border-radius: 10px; background: #e9ecef;">
                                    <div class="progress-bar bg-gold progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $progress_percent; ?>%;" aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted" style="font-size: 0.78rem;">
                                    <span>Started: <?php echo $start_date->format('d M Y'); ?></span>
                                    <span class="fw-bold text-dark"><?php echo $months_remaining; ?> Months Remaining</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include('include/footer.php'); ?>

