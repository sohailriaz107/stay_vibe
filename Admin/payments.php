<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

// Handle Approval/Rejection/Deletion
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Payment record deleted successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Error deleting record.</div>';
        }
        $stmt->close();
    } else {
        $status = ($action == 'approve') ? 'approved' : 'rejected';
        
        // Update payment status
        $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            // If approved, activate the user account
            if ($status == 'approved') {
                $res = $conn->query("SELECT user_id, plan_id FROM payments WHERE id = $id");
                $pay = $res->fetch_assoc();
                if ($pay) {
                    $uid = $pay['user_id'];
                    $pid = $pay['plan_id'];
                    
                    // Get plan info from plans table
                    $pres = $conn->query("SELECT * FROM plans WHERE id = $pid");
                    $plan_row = $pres->fetch_assoc();
                    $pname = $plan_row ? $plan_row['plan_name'] : 'Unknown Plan';
                    $pprice = $plan_row ? $plan_row['plan_price'] : 0;
                    $lockin_years = ($plan_row && isset($plan_row['lockin_years'])) ? $plan_row['lockin_years'] : 3;
                    
                    // Check total paid
                    $paid_res = $conn->query("SELECT SUM(amount) as tp FROM payments WHERE user_id = $uid AND plan_id = $pid AND status = 'approved'");
                    $total_paid = $paid_res->fetch_assoc()['tp'] ?? 0;
                    
                    
                    if ($total_paid >= $pprice && $pprice > 0) {
                        // Fully paid. Insert/Update plan info in user_plans
                        $today = date('Y-m-d');
                        $next_payout = date('Y-m-d', strtotime('+1 month'));
                        $end_date = date('Y-m-d', strtotime("+$lockin_years years"));
                        
                        // Check if a user_plans record already exists for this user & plan
                        $plan_check = $conn->query("SELECT id FROM user_plans WHERE user_id = $uid AND plan_id = $pid");
                        if ($plan_check && $plan_check->num_rows > 0) {
                            $plan_row = $plan_check->fetch_assoc();
                            $conn->query("UPDATE user_plans SET activation_date = '$today', next_payout_date = '$next_payout', end_date = '$end_date', status = 'active' WHERE id = {$plan_row['id']}");
                        } else {
                            $conn->query("INSERT INTO user_plans (user_id, plan_id, activation_date, next_payout_date, end_date, status) VALUES ($uid, $pid, '$today', '$next_payout', '$end_date', 'active')");
                        }
                        
                        // Keep user status updated (active_plan column is removed)
                        $conn->query("UPDATE users SET status = 'active' WHERE id = $uid");
                        
                        // ---- REFERRAL COMMISSION AUTOMATION ----
                        // If the referee has a reference_id, ensure the referral in referrals table is set to appropriate status
                        $user_info = $conn->query("SELECT reference_id FROM users WHERE id = $uid")->fetch_assoc();
                        $reference_id = $user_info['reference_id'] ?? '';
                        if (!empty($reference_id)) {
                            // Find the referrer
                            $search_id = $reference_id;
                            if (strtoupper(substr($reference_id, 0, 2)) === 'SV') {
                                $search_id = (int)substr($reference_id, 2);
                            }
                            $ref_stmt = $conn->prepare("
                                SELECT u.id, p.plan_name as active_plan 
                                FROM users u 
                                LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active' 
                                LEFT JOIN plans p ON up.plan_id = p.id 
                                WHERE u.id = ? OR u.email = ? LIMIT 1
                            ");
                            $ref_stmt->bind_param("ss", $search_id, $reference_id);
                            $ref_stmt->execute();
                            $ref_res = $ref_stmt->get_result();
                            if ($ref_res && $ref_res->num_rows > 0) {
                                $referrer = $ref_res->fetch_assoc();
                                $referrer_id = $referrer['id'];
                                $referrer_plan = $referrer['active_plan'] ?? '';
                                
                                // Determine commission percentage based on referrer's active plan
                                $commission_percent = ($referrer_plan == 'Plan C') ? 10 : 5;
                                $commission_amount = ($pprice * $commission_percent) / 100;
                                
                                // Check if a referral entry exists
                                $ref_check = $conn->query("SELECT id FROM referrals WHERE user_id = $referrer_id AND referred_user_id = $uid");
                                if ($ref_check && $ref_check->num_rows > 0) {
                                    // Update existing referral with calculated commission and keep status as pending
                                    $conn->query("UPDATE referrals SET commission = $commission_amount WHERE user_id = $referrer_id AND referred_user_id = $uid AND status = 'pending'");
                                } else {
                                    // Insert new pending referral
                                    $conn->query("INSERT INTO referrals (user_id, referred_user_id, commission, status) VALUES ($referrer_id, $uid, $commission_amount, 'pending')");
                                }
                            }
                            $ref_stmt->close();
                        }
                    } else {
                        // Installment paid, but not full yet
                        $conn->query("UPDATE users SET status = 'active' WHERE id = $uid");
                    }
                }
            }
            $msg = '<div class="alert alert-success">Payment status updated successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Error updating status.</div>';
        }
        $stmt->close();
    }
}

$query = "SELECT p.*, u.full_name, u.email, pl.plan_name, pl.plan_price,
          (SELECT SUM(amount) FROM payments p2 WHERE p2.user_id = p.user_id AND p2.plan_id = p.plan_id AND p2.status = 'approved') as total_paid
          FROM payments p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN plans pl ON p.plan_id = pl.id 
          ORDER BY p.created_at DESC";
$result = $conn->query($query);
if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<div id="content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Payment Management</h2>
            <p class="text-muted">Review and verify investment payment proofs from users.</p>
        </div>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <!-- Payments Table -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Submission Date</th>
                        <th>User Details</th>
                        <th>Plan Name</th>
                        <th>Payment Details</th>
                        <th>UTR / Transaction ID</th>
                        <th>Proof Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                        </td>
                        <td>
                            <div class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?php echo htmlspecialchars($row['plan_name'] ?? 'Custom'); ?></div>
                        </td>
                        <td>
                            <div class="small fw-bold text-dark mb-1">Paid: ₹<?php echo number_format($row['amount']); ?></div>
                            <?php if(isset($row['plan_price']) && $row['plan_price'] > 0): ?>
                                <?php 
                                    $percent = round(($row['amount'] / $row['plan_price']) * 100); 
                                    $total_paid = $row['total_paid'] ?? 0;
                                    $remaining = max(0, $row['plan_price'] - $total_paid);
                                ?>
                                <div class="x-small-text text-muted">Total Plan: ₹<?php echo number_format($row['plan_price']); ?></div>
                                <div class="badge bg-light text-dark border mt-1 mb-1"><?php echo $percent; ?>% Installment</div>
                                
                                <?php if ($remaining == 0 && $row['status'] == 'approved'): ?>
                                    <div class="badge bg-success w-100 mt-1"><i class="fas fa-check-circle me-1"></i> Completed</div>
                                <?php else: ?>
                                    <div class="x-small-text text-danger fw-bold mt-1">Remaining: ₹<?php echo number_format($remaining); ?></div>
                                <?php endif; ?>
                                
                            <?php endif; ?>
                        </td>
                        <td><code class="text-primary fw-bold"><?php echo htmlspecialchars($row['transaction_id']); ?></code></td>
                        <td>
                            <button class="btn btn-sm btn-outline-dark rounded-3" data-bs-toggle="modal" data-bs-target="#viewProof<?php echo $row['id']; ?>">
                                <i class="fas fa-image me-1"></i> View
                            </button>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            if($row['status'] == 'pending') $status_class = 'badge-pending';
                            elseif($row['status'] == 'approved') $status_class = 'badge-success';
                            elseif($row['status'] == 'rejected') $status_class = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                        </td>
                        <td>
                            <?php if($row['status'] == 'pending'): ?>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm rounded-pill px-2" title="Approve" onclick="return confirm('Approve this payment?')"><i class="fas fa-check"></i></a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm rounded-pill px-2 text-dark" title="Reject" onclick="return confirm('Reject this payment?')"><i class="fas fa-times"></i></a>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-2" title="Delete" onclick="return confirm('Delete this record permanently?')"><i class="fas fa-trash"></i></a>
                            </div>
                            <?php else: ?>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="text-muted small fw-bold">Processed</span>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-2" title="Delete" onclick="return confirm('Delete this record permanently?')"><i class="fas fa-trash"></i></a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- View Proof Modal for each row -->
                    <div class="modal fade" id="viewProof<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0" style="border-radius: 25px; overflow: hidden;">
                                <div class="modal-header border-0 p-4">
                                    <h5 class="modal-title fw-bold">Payment Proof Screenshot</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-0 text-center bg-light">
                                    <img src="../Frontend/<?php echo $row['screenshot']; ?>" class="img-fluid" style="max-height: 80vh;" alt="Proof">
                                </div>
                                <div class="modal-footer border-0 p-4">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                    <a href="../Frontend/<?php echo $row['screenshot']; ?>" download class="btn btn-primary rounded-pill px-4">Download</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.badge-pending { background: #fff3cd; color: #856404; }
.badge-success { background: #d4edda; color: #155724; }
.badge-danger { background: #f8d7da; color: #721c24; }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
                            