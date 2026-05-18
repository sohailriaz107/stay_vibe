<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

// Handle Account Status Toggle & Deletion
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'suspend') {
        $conn->query("UPDATE users SET status = 'suspended' WHERE id = $id");
    } elseif ($_GET['action'] == 'activate') {
        $conn->query("UPDATE users SET status = 'active' WHERE id = $id");
    } elseif ($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM bank_details WHERE user_id = $id");
        $conn->query("DELETE FROM user_plans WHERE user_id = $id");
        $conn->query("DELETE FROM referrals WHERE user_id = $id OR referred_user_id = $id");
        $conn->query("DELETE FROM users WHERE id = $id");
    }
}

// Handle KYC Status Update
if (isset($_GET['kyc_action']) && isset($_GET['bank_id'])) {
    $b_id = (int)$_GET['bank_id'];
    $k_action = $_GET['kyc_action'];
    if ($k_action == 'approve') {
        $conn->query("UPDATE bank_details SET kyc_status = 'accepted' WHERE id = $b_id");
    } elseif ($k_action == 'reject') {
        $conn->query("UPDATE bank_details SET kyc_status = 'rejected' WHERE id = $b_id");
    }
}

// Fetch Stats
$active_res = $conn->query("SELECT count(id) as c FROM users WHERE status = 'active'");
$active_count = $active_res->fetch_assoc()['c'] ?? 0;

$kyc_res = $conn->query("SELECT count(id) as c FROM bank_details WHERE kyc_status = 'pending'");
$kyc_pending_count = $kyc_res->fetch_assoc()['c'] ?? 0;

// Fetch All Users Data
$users_query = "
    SELECT u.*, 
           bd.id as bank_id, bd.kyc_status,
           p.plan_name as active_plan
    FROM users u
    LEFT JOIN bank_details bd ON u.id = bd.user_id
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    LEFT JOIN plans p ON up.plan_id = p.id
    ORDER BY u.created_at DESC
";
$users_res = $conn->query($users_query);
?>

<div id="content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">User Management</h2>
            <p class="text-muted">Manage user accounts, toggle status, and verify KYC documents.</p>
        </div>
    </div>

    <!-- User Stats Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card py-3">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Active Users</h6>
                <h4 class="fw-bold mb-0"><?php echo number_format($active_count); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card py-3 border-start border-warning border-4">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Unverified KYC</h6>
                <h4 class="fw-bold mb-0 text-warning"><?php echo number_format($kyc_pending_count); ?></h4>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Mobile Number</th>
                        <th>Plan Status</th>
                        <th>Wallet Balance</th>
                        

                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users_res && $users_res->num_rows > 0): ?>
                        <?php while($user = $users_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=random" class="rounded-circle me-3" width="40">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    </div>
                                </td>
                                <td class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($user['mobile_number']); ?></td>
                                <td>
                                    <?php if(!empty($user['active_plan'])): ?>
                                        <span class="badge-status badge-active"> <?php echo htmlspecialchars($user['active_plan']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted italic small">No Active Plan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">₹<?php echo number_format($user['wallet_balance'] ?? 0, 2); ?></span>
                                </td>
                                <td>
                                    <?php if($user['status'] == 'active'): ?>
                                        <div class="form-check form-switch d-flex align-items-center m-0 p-0">
                                            <a href="?action=suspend&id=<?php echo $user['id']; ?>" class="text-decoration-none" onclick="return confirm('Suspend user?')">
                                                <i class="fas fa-toggle-on text-success fs-4 me-2"></i>
                                                <span class="small text-success fw-bold">Active</span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-check form-switch d-flex align-items-center m-0 p-0">
                                            <a href="?action=activate&id=<?php echo $user['id']; ?>" class="text-decoration-none" onclick="return confirm('Activate user?')">
                                                <i class="fas fa-toggle-off text-muted fs-4 me-2"></i>
                                                <span class="small text-danger fw-bold">Suspended</span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu border-0 shadow-sm rounded-3">
                                            
                                            <li><a class="dropdown-item" href="payments.php"><i class="fas fa-history me-2"></i> Transaction History</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger fw-bold" href="?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('WARNING: Are you sure you want to permanently delete this user and all their related data (Bank Details, Plans, Referrals)? This action cannot be undone.')"><i class="fas fa-trash-alt me-2"></i> Delete User</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
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
