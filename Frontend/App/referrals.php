<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');

$user_id = $_SESSION['user_id'];
$message = '';

// Format User ID to SV0000X
$referral_code = 'SV' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
// Get current domain for the link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $domain . "/Stat_vibe/Frontend/pages/register.php?ref=" . $referral_code;

// Fetch Active Plan
$stmt = $conn->prepare("
    SELECT p.plan_name as active_plan 
    FROM users u 
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    LEFT JOIN plans p ON up.plan_id = p.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate Referral Earnings Dynamically
$sum_stmt = $conn->prepare("SELECT SUM(commission) as total FROM referrals WHERE user_id = ? AND status = 'approved'");
$sum_stmt->bind_param("i", $user_id);
$sum_stmt->execute();
$referral_earnings = $sum_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$sum_stmt->close();

$active_plan = $user_data['active_plan'] ?? 'None';
$commission_rate = ($active_plan == 'Plan C') ? '10%' : '5%';

// Fetch Referral History
$history_stmt = $conn->prepare("
    SELECT u.full_name, p.plan_name as active_plan, r.commission, r.status, r.created_at 
    FROM referrals r 
    JOIN users u ON r.referred_user_id = u.id 
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    LEFT JOIN plans p ON up.plan_id = p.id
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_res = $history_stmt->get_result();
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
                    <h2 class="fw-bold mb-1">Refer & Earn</h2>
                    <p class="text-muted small mb-0">Invite friends and earn <?php echo $commission_rate; ?> commission on their active plans.</p>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Link Sharing Card -->
                    <div class="col-lg-7">
                        <div class="premium-card p-3 px-2 px-sm-4 py-sm-4 bg-white border-0 shadow-sm h-100 text-center" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-3"><i class="fas fa-share-alt text-primary me-2"></i> Your Referral Link</h5>
                            <p class="text-muted small mb-4">Share this unique link with your network. When they register and activate a plan, you will receive commission.</p>
                            
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-light border-0 fw-bold text-primary text-center" id="refLink" value="<?php echo htmlspecialchars($base_url); ?>" readonly>
                                <button class="btn btn-primary px-3" type="button" onclick="copyLink()">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                                <span class="small fw-bold text-muted">Your Code:</span>
                                <span class="badge bg-dark px-3 py-2 fs-6 letter-spacing-1"><?php echo $referral_code; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Earnings Summary -->
                    <div class="col-lg-5">
                        <div class="premium-card p-3 px-2 px-sm-4 py-sm-4 border-0 shadow-sm h-100 text-white text-center d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, var(--primary-color), #1a2533) !important; border-radius: 20px;">
                            <div>
                                <h6 class="text-white-50 small mb-3 text-uppercase letter-spacing-1" style="font-size: 0.78rem;">Total Referral Earnings</h6>
                                <h1 class="fw-extrabold mb-3 text-white" style="font-weight: 800; font-size: 1.8rem;">₹<?php echo number_format($referral_earnings, 2); ?></h1>
                            </div>
                            
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 mt-auto w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-white fw-bold">Your Commission Rate</span>
                                    <span class="fw-extrabold text-white fs-6"><?php echo $commission_rate; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="premium-card p-3 px-2 px-sm-4 py-sm-4 bg-white border-0 shadow-sm mt-4" style="border-radius: 20px;">
                    <h5 class="fw-bold mb-4 text-center">Your Network History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Referred User</th>
                                    <th class="border-0">Plan</th>
                                    <th class="border-0 text-end">Commission</th>
                                    <th class="border-0 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($history_res->num_rows > 0): ?>
                                    <?php while($row = $history_res->fetch_assoc()): ?>
                                    <tr>
                                        <td class="small text-muted"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['active_plan'] ?? 'None'); ?></span></td>
                                        <td class="text-end fw-bold text-success">+ ₹<?php echo number_format($row['commission'], 2); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $row['status'] == 'approved' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">You haven't referred anyone yet.</p>
                                            <small>Share your link above to start earning!</small>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyLink() {
    var copyText = document.getElementById("refLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(copyText.value).then(function() {
        alert("Referral link copied to clipboard!");
    });
}
</script>
<?php include('include/footer.php'); ?>


