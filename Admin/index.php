<?php 
// include('includes/auth.php'); // Will create this soon
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

// Fetch Stats
$tot_users = $conn->query("SELECT COUNT(id) as c FROM users")->fetch_assoc()['c'] ?? 0;
$tot_inv = $conn->query("SELECT SUM(amount) as s FROM payments WHERE status = 'approved'")->fetch_assoc()['s'] ?? 0;
$tot_plans = $conn->query("SELECT COUNT(id) as c FROM plans")->fetch_assoc()['c'] ?? 0;
$tot_ref = $conn->query("SELECT COUNT(id) as c FROM referrals WHERE status = 'approved'")->fetch_assoc()['c'] ?? 0;
$tot_withdrawn = $conn->query("SELECT SUM(final_amount) as s FROM withdrawals WHERE status = 'approved'")->fetch_assoc()['s'] ?? 0;
$pen_pay = $conn->query("SELECT COUNT(id) as c FROM payments WHERE status = 'pending'")->fetch_assoc()['c'] ?? 0;
$pen_kyc = $conn->query("SELECT COUNT(id) as c FROM bank_details WHERE kyc_status = 'pending'")->fetch_assoc()['c'] ?? 0;
$pen_with = $conn->query("SELECT COUNT(id) as c FROM withdrawals WHERE status = 'pending'")->fetch_assoc()['c'] ?? 0;

// Recent Payments
$recent_payments = $conn->query("
    SELECT p.*, u.full_name, pl.plan_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    JOIN plans pl ON p.plan_id = pl.id 
    ORDER BY p.created_at DESC LIMIT 5
");
?>

<div id="content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted">Welcome back, Admin. Here's your business summary.</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="text-end me-3">
                <h6 class="mb-0 fw-bold">Admin User</h6>
                <p class="small text-muted mb-0">Super Admin</p>
            </div>
            <img src="https://ui-avatars.com/api/?name=Admin&background=0b2c4d&color=fff" class="rounded-circle shadow-sm" width="45" alt="Admin">
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                    <i class="fas fa-users"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Users</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($tot_users); ?></h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                    <i class="fas fa-coins"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Investment</h6>
                <h3 class="fw-bold mb-0">₹<?php echo number_format($tot_inv, 2); ?></h3>
            </div>
        </div>
         <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Plans</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($tot_plans); ?></h3>
            </div>
        </div>
         <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Referral Joinings</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($tot_ref); ?></h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                    <i class="fas fa-wallet"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Withdrawals Approved</h6>
                <h3 class="fw-bold mb-0">₹<?php echo number_format($tot_withdrawn, 2); ?></h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Pending Payments</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($pen_pay); ?></h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                    <i class="fas fa-id-card"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Pending KYC</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($pen_kyc); ?></h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Withdraw Requests</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($pen_with); ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Payments -->
        <div class="col-lg-12">
            <div class="premium-table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Payment Proofs</h5>
                    <a href="payments.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_payments && $recent_payments->num_rows > 0): ?>
                                <?php while($pay = $recent_payments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle text-center me-3" style="width: 40px; height: 40px; line-height: 40px;">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($pay['full_name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($pay['plan_name']); ?></td>
                                        <td class="fw-bold">₹<?php echo number_format($pay['amount'], 2); ?></td>
                                        <td>
                                            <?php if($pay['status'] == 'approved'): ?>
                                                <span class="badge bg-success rounded-pill px-3">Completed</span>
                                            <?php elseif($pay['status'] == 'rejected'): ?>
                                                <span class="badge bg-danger rounded-pill px-3">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-light rounded-circle"><i class="fas fa-chevron-right text-muted"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No recent payments.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Activity -->
       
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
