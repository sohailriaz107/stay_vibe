<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM withdrawals WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Withdrawal record deleted.</div>';
        }
    } else {
        $status = ($action == 'approve') ? 'approved' : 'rejected';
        
        // If rejected, we must refund the amount back to user's wallet
        if ($status == 'rejected') {
            $req_stmt = $conn->query("SELECT user_id, amount, status FROM withdrawals WHERE id = $id");
            $req_data = $req_stmt->fetch_assoc();
            
            // Ensure we only refund if it was pending (to avoid double refund)
            if ($req_data && $req_data['status'] == 'pending') {
                $uid = $req_data['user_id'];
                $amt = $req_data['amount'];
                $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amt WHERE id = $uid");
            }
        }
        
        $stmt = $conn->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Withdrawal status updated to ' . $status . '.</div>';
        }
    }
}

// Fetch Stats
$stats = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_req,
        SUM(CASE WHEN status = 'approved' THEN final_amount ELSE 0 END) as total_withdrawn,
        SUM(CASE WHEN status = 'approved' THEN fee ELSE 0 END) as total_fees
    FROM withdrawals
")->fetch_assoc();

$pending_req = $stats['pending_req'] ?? 0;
$total_withdrawn = $stats['total_withdrawn'] ?? 0;
$total_fees = $stats['total_fees'] ?? 0;

// Fetch All Withdrawals
$query = "SELECT w.*, u.full_name, u.email, b.bank_name, b.account_number, b.ifsc_code 
          FROM withdrawals w 
          JOIN users u ON w.user_id = u.id 
          LEFT JOIN bank_details b ON w.user_id = b.user_id 
          ORDER BY w.created_at DESC";
$result = $conn->query($query);
?>

<div id="content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Withdrawal Management</h2>
            <p class="text-muted">Approve or reject fund withdrawal requests from users.</p>
        </div>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <!-- Summary Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Pending Requests</h6>
                <h4 class="fw-bold mb-0"><?php echo $pending_req; ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Withdrawn</h6>
                <h4 class="fw-bold mb-0 text-success">₹<?php echo number_format($total_withdrawn, 2); ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Platform Fees Earned</h6>
                <h4 class="fw-bold mb-0 text-primary">₹<?php echo number_format($total_fees, 2); ?></h4>
            </div>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Requested Amount</th>
                        <th>Platform Fee (5%)</th>
                        <th>Final Payout</th>
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
                                <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <?php if(!empty($row['bank_name'])): ?>
                                    <div class="small text-muted"><?php echo htmlspecialchars($row['bank_name']); ?> - <?php echo htmlspecialchars($row['account_number']); ?></div>
                                <?php else: ?>
                                    <div class="small text-danger">No Bank Details</div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold">₹<?php echo number_format($row['amount'], 2); ?></td>
                            <td class="text-danger small">- ₹<?php echo number_format($row['fee'], 2); ?></td>
                            <td class="fw-bold text-success">₹<?php echo number_format($row['final_amount'], 2); ?></td>
                            <td>
                                <?php 
                                $status_class = '';
                                if($row['status'] == 'pending') $status_class = 'bg-warning text-dark';
                                elseif($row['status'] == 'approved') $status_class = 'bg-success';
                                elseif($row['status'] == 'rejected') $status_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm rounded-pill px-2" title="Approve" onclick="return confirm('Approve withdrawal?')"><i class="fas fa-check"></i></a>
                                    <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm rounded-pill px-2 text-dark" title="Reject & Refund" onclick="return confirm('Reject this withdrawal and refund amount to wallet?')"><i class="fas fa-times"></i></a>
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
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No withdrawal requests found.</td>
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
