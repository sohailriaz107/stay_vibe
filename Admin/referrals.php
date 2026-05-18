<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

// Handle Actions (Approve / Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Fetch the referral details first to ensure it's still pending
    $ref_stmt = $conn->query("SELECT user_id, referred_user_id, status FROM referrals WHERE id = $id");
    $ref_data = $ref_stmt->fetch_assoc();
    
    if ($ref_data && ($ref_data['status'] == 'pending' || $ref_data['status'] == '')) {
        $referrer_id = $ref_data['user_id'];
        $referee_id = $ref_data['referred_user_id'];
        
        if ($action == 'approve') {
            // Find referee's active plan in user_plans (which is only created/activated upon 100% payment)
            $plan_res = $conn->query("
                SELECT up.plan_id, p.plan_name, p.plan_price 
                FROM user_plans up 
                JOIN plans p ON up.plan_id = p.id 
                WHERE up.user_id = $referee_id AND up.status = 'active'
            ");
            $ref_plan_info = $plan_res->fetch_assoc();
            
            if ($ref_plan_info) {
                $plan_price = $ref_plan_info['plan_price'];
                $plan_name = $ref_plan_info['plan_name'];
                
                // Double check total payments
                $paid_res = $conn->query("SELECT SUM(amount) as tp FROM payments WHERE user_id = $referee_id AND plan_id = {$ref_plan_info['plan_id']} AND status = 'approved'");
                $total_paid = $paid_res->fetch_assoc()['tp'] ?? 0;
                
                if ($total_paid >= $plan_price && $plan_price > 0) {
                    // Find referrer's plan to determine percentage
                    $referrer_res = $conn->query("
                        SELECT p.plan_name as active_plan 
                        FROM users u 
                        LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active' 
                        LEFT JOIN plans p ON up.plan_id = p.id 
                        WHERE u.id = $referrer_id
                    ");
                    $referrer_plan = $referrer_res->fetch_assoc()['active_plan'] ?? '';
                    
                    $commission_percent = ($referrer_plan == 'Plan C') ? 10 : 5;
                    $commission_amount = ($plan_price * $commission_percent) / 100;
                    
                    // Update referral status and commission
                    $conn->query("UPDATE referrals SET commission = $commission_amount, status = 'approved' WHERE id = $id");
                    
                    // Add to referrer's wallet
                    $conn->query("UPDATE users SET wallet_balance = wallet_balance + $commission_amount, total_earnings = total_earnings + $commission_amount WHERE id = $referrer_id");
                    
                    $msg = '<div class="alert alert-success">Referral Approved! ₹' . number_format($commission_amount, 2) . ' credited to Referrer.</div>';
                } else {
                    $msg = '<div class="alert alert-danger">Cannot approve. The referred user has only paid a partial installment (₹' . number_format($total_paid) . ' / ₹' . number_format($plan_price) . '). 100% payment is required.</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">Cannot approve. The referred user does not have an active, fully paid plan in user_plans yet. 100% payment must be approved first.</div>';
            }
        } elseif ($action == 'reject') {
            $conn->query("UPDATE referrals SET status = 'rejected' WHERE id = $id");
            $msg = '<div class="alert alert-success">Referral request rejected.</div>';
        }
    }
}

// Fetch Stats
$stats = $conn->query("
    SELECT 
        COUNT(id) as total_referrals,
        SUM(commission) as total_commission_paid
    FROM referrals 
    WHERE status = 'approved'
")->fetch_assoc();

$total_referrals = $stats['total_referrals'] ?? 0;
$total_commission = $stats['total_commission_paid'] ?? 0;

// Fetch Referrals Data
$query = "SELECT r.*, 
                 u1.full_name as referrer_name, u1.email as referrer_email, p1.plan_name as referrer_plan,
                 u2.full_name as referred_name, u2.email as referred_email
          FROM referrals r
          JOIN users u1 ON r.user_id = u1.id
          LEFT JOIN user_plans up1 ON u1.id = up1.user_id AND up1.status = 'active'
          LEFT JOIN plans p1 ON up1.plan_id = p1.id
          JOIN users u2 ON r.referred_user_id = u2.id
          ORDER BY r.created_at DESC";
$result = $conn->query($query);
?>

<div id="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Referral Network</h2>
            <p class="text-muted">Track user referrals and commission distributions.</p>
        </div>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <!-- Summary Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Successful Referrals</h6>
                <h4 class="fw-bold mb-0 text-primary"><?php echo number_format($total_referrals); ?> Users</h4>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Commission Distributed</h6>
                <h4 class="fw-bold mb-0 text-success">₹<?php echo number_format($total_commission, 2); ?></h4>
            </div>
        </div>
    </div>

    <!-- Referrals Table -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Referrer (Who invited)</th>
                        <th>Referred User (Who joined)</th>
                        <th>Commission Payout</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['referrer_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['referrer_email']); ?></div>
                                <div class="badge bg-light text-dark border mt-1" style="font-size: 0.65rem;"><?php echo htmlspecialchars($row['referrer_plan'] ?? 'No Plan'); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['referred_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['referred_email']); ?></div>
                            </td>
                            <td class="fw-bold text-success">+ ₹<?php echo number_format($row['commission'], 2); ?></td>
                            <td>
                                <?php 
                                $status = empty($row['status']) ? 'pending' : $row['status'];
                                $status_class = '';
                                if($status == 'pending') $status_class = 'bg-warning text-dark';
                                elseif($status == 'approved') $status_class = 'bg-success';
                                elseif($status == 'rejected') $status_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                            </td>
                            <td>
                                <?php if($status == 'pending'): ?>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm rounded-pill px-2" title="Approve" onclick="return confirm('Approve this referral and pay commission?')"><i class="fas fa-check"></i></a>
                                    <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-2" title="Reject" onclick="return confirm('Reject this referral?')"><i class="fas fa-times"></i></a>
                                </div>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No referral history found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
