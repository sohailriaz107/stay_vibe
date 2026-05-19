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

// Handle Add User
$add_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $mobile_number = $conn->real_escape_string($_POST['mobile_number']);
    $password = password_hash('12345678', PASSWORD_DEFAULT);
    $plan_id = (int)$_POST['plan_id'];

    // Check if email or mobile already exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email' OR mobile_number = '$mobile_number'");
    if ($check->num_rows > 0) {
        $add_msg = '<div class="alert alert-danger shadow-sm rounded-3"><i class="fas fa-exclamation-circle me-2"></i>User with this email or mobile number already exists.</div>';
    } else {
        $insert_user = $conn->query("INSERT INTO users (full_name, email, mobile_number, password, status, father_name, dob, pincode, reference_id) VALUES ('$full_name', '$email', '$mobile_number', '$password', 'active', '', '', '', '')");
        
        if ($insert_user) {
            $new_user_id = $conn->insert_id;
            
            // Assign Plan if selected
            if ($plan_id > 0) {
                $pres = $conn->query("SELECT lockin_years FROM plans WHERE id = $plan_id");
                $plan_row = $pres->fetch_assoc();
                $lockin_years = ($plan_row && isset($plan_row['lockin_years'])) ? $plan_row['lockin_years'] : 3;
                
                $today = date('Y-m-d');
                $next_payout = date('Y-m-d', strtotime('+1 month'));
                $end_date = date('Y-m-d', strtotime("+$lockin_years years"));
                
                $conn->query("INSERT INTO user_plans (user_id, plan_id, activation_date, next_payout_date, end_date, status) VALUES ($new_user_id, $plan_id, '$today', '$next_payout', '$end_date', 'active')");
            }
            $add_msg = '<div class="alert alert-success shadow-sm rounded-3"><i class="fas fa-check-circle me-2"></i>User added successfully.</div>';
        } else {
            $add_msg = '<div class="alert alert-danger shadow-sm rounded-3"><i class="fas fa-times-circle me-2"></i>Failed to add user: ' . $conn->error . '</div>';
        }
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
           p.plan_name as active_plan,
           p.plan_price,
           (SELECT SUM(amount) FROM payments p2 WHERE p2.user_id = u.id AND p2.plan_id = p.id AND p2.status = 'approved') as total_paid,
           (SELECT COUNT(*) FROM payments p2 WHERE p2.user_id = u.id AND p2.plan_id = p.id AND p2.status = 'approved') as paid_count
    FROM users u
    LEFT JOIN bank_details bd ON u.id = bd.user_id
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    LEFT JOIN plans p ON up.plan_id = p.id
    ORDER BY u.created_at DESC
";
$users_res = $conn->query($users_query);

// Fetch plans for dropdown
$plans_dropdown_query = "SELECT id, plan_name FROM plans ORDER BY plan_name ASC";
$plans_dropdown_res = $conn->query($plans_dropdown_query);
?>

<div id="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-5 w-100">
        <div>
            <h2 class="fw-bold mb-1">User Management</h2>
            <p class="text-muted">Manage user accounts, toggle status, and verify KYC documents.</p>
        </div>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i> Add User
            </button>
        </div>
    </div>

    <?php if(!empty($add_msg)) echo $add_msg; ?>

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
            <table id="usersTable" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th>SR</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Mobile Number</th>
                        <th>Plan Status</th>
                        <th>Installments</th>
                        <th>Wallet Balance</th>
                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users_res && $users_res->num_rows > 0): ?>
                        <?php $sr = 1; ?>
                        <?php while($user = $users_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $sr++; ?></td>
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
                                        <span class="badge-status badge-active mb-2 d-inline-block"> <?php echo htmlspecialchars($user['active_plan']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted italic small">No Active Plan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!empty($user['active_plan'])): ?>
                                        <?php 
                                            $total_paid = (float)$user['total_paid'];
                                            $plan_price = (float)$user['plan_price'];
                                            $paid_count = (int)$user['paid_count'];
                                            
                                            $percent = 0;
                                            if ($plan_price > 0) {
                                                $percent = min(100, round(($total_paid / $plan_price) * 100));
                                            }
                                        ?>
                                        <div style="font-size: 11px;" class="fw-bold text-muted mb-1">PAID: ₹<?php echo number_format($total_paid); ?> / ₹<?php echo number_format($plan_price); ?></div>
                                        
                                        <?php if($percent >= 100): ?>
                                            <div class="badge bg-success w-100 text-center py-2"><i class="fas fa-check-circle me-1"></i> 100% Fully Paid</div>
                                        <?php else: ?>
                                            <div class="progress mb-1" style="height: 6px; border-radius: 10px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percent; ?>%;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 10px;"><?php echo $percent; ?>% Paid</span>
                                                <span class="x-small-text text-muted" style="font-size: 10px;"><?php echo $paid_count; ?> Installment(s)</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted italic small">-</span>
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
                                            
                                            <li><a class="dropdown-item" href="payments.php?user_id=<?php echo $user['id']; ?>"><i class="fas fa-history me-2"></i> Transaction History</a></li>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header border-0 bg-light p-4">
        <h5 class="modal-title fw-bold" id="addUserModalLabel"><i class="fas fa-user-plus text-primary me-2"></i>Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Full Name</label>
                <input type="text" name="full_name" class="form-control rounded-pill px-4 py-2" required placeholder="Enter full name">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Email Address</label>
                <input type="email" name="email" class="form-control rounded-pill px-4 py-2" required placeholder="Enter email address">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Mobile Number</label>
                <input type="text" name="mobile_number" class="form-control rounded-pill px-4 py-2" required placeholder="Enter mobile number">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Assign Plan (Optional)</label>
                <select name="plan_id" class="form-select rounded-pill px-4 py-2 cursor-pointer">
                    <option value="0">-- No Plan --</option>
                    <?php if($plans_dropdown_res && $plans_dropdown_res->num_rows > 0): ?>
                        <?php while($plan = $plans_dropdown_res->fetch_assoc()): ?>
                            <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['plan_name']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer border-0 p-4 bg-light">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_user" class="btn btn-primary rounded-pill px-4 shadow-sm">Save User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search users..."
            }
        });
    });
</script>
</body>
</html>
