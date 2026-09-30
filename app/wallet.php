<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');

$user_id = $_SESSION['user_id'];

// Get User Wallet and General Info
$stmt = $conn->prepare("
    SELECT u.wallet_balance, u.total_earnings, u.created_at, up.activation_date 
    FROM users u 
    LEFT JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$balance = $user['wallet_balance'] ?? 0;
$total_earnings = $user['total_earnings'] ?? 0;
$activation_date = $user['activation_date'] ?? null;

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
if ($activation_date) {
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

    $lock_3year_end_date = date('Y-m-d', strtotime($activation_date . " +$lockin_years years"));
    if (date('Y-m-d') >= $lock_3year_end_date) {
        $three_years_passed = true;
    }
}

// Add unlocked principal to available balance and empty locked principal
if ($three_years_passed) {
    $balance += $locked_balance;
    $locked_balance = 0;
}

// Check if user has an active fully-paid plan
$plan_stmt = $conn->prepare("
    SELECT up.activation_date as up_activation_date, up.end_date, p.plan_name, p.plan_price, p.yearly_return_percent 
    FROM user_plans up 
    JOIN plans p ON up.plan_id = p.id 
    WHERE up.user_id = ? AND up.status = 'active'
");
$plan_stmt->bind_param("i", $user_id);
$plan_stmt->execute();
$plan_res = $plan_stmt->get_result();
$has_plan = false;
$plan_details = null;
if ($plan_res && $plan_res->num_rows > 0) {
    $plan_details = $plan_res->fetch_assoc();
    $has_plan = true;
}
$plan_stmt->close();

// Fetch transactions from DB (Payments, Referrals, Withdrawals)
$tx_query = "
    (SELECT 
        'payment' AS tx_source,
        p.amount,
        p.status,
        p.created_at,
        pl.plan_name AS detail,
        'deposit' AS flow
    FROM payments p
    LEFT JOIN plans pl ON p.plan_id = pl.id
    WHERE p.user_id = ?)
    
    UNION ALL
    
    (SELECT 
        'referral' AS tx_source,
        r.commission AS amount,
        r.status,
        r.created_at,
        u.full_name AS detail,
        'credit' AS flow
    FROM referrals r
    LEFT JOIN users u ON r.referred_user_id = u.id
    WHERE r.user_id = ?)
    
    UNION ALL
    
    (SELECT 
        'withdrawal' AS tx_source,
        w.amount,
        w.status,
        w.created_at,
        NULL AS detail,
        'debit' AS flow
    FROM withdrawals w
    WHERE w.user_id = ?)
";

$tx_stmt = $conn->prepare($tx_query);
$tx_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$tx_stmt->execute();
$tx_result = $tx_stmt->get_result();
$transactions = [];
if ($tx_result) {
    while ($row = $tx_result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
$tx_stmt->close();

// Dynamically generate Simulated Rental Income payouts if a plan is active
if ($has_plan && !empty($plan_details['up_activation_date'])) {
    $act_date = new DateTime($plan_details['up_activation_date']);
    $today = new DateTime();
    
    // Calculate monthly rental amount
    $plan_price = $plan_details['plan_price'];
    $yearly_return = $plan_details['yearly_return_percent'];
    $monthly_amount = ($plan_price * $yearly_return) / 100 / 12;
    
    // Payout interval is 1 month
    $interval = new DateInterval('P1M');
    $current_payout = clone $act_date;
    $current_payout->add($interval); // First payout is 1 month after activation
    
    $index = 1;
    while ($current_payout <= $today) {
        $transactions[] = [
            'tx_source' => 'rental',
            'amount' => $monthly_amount,
            'status' => 'approved',
            'created_at' => $current_payout->format('Y-m-d H:i:s'),
            'detail' => $plan_details['plan_name'] . " - Month " . $index,
            'flow' => 'credit'
        ];
        $current_payout->add($interval);
        $index++;
    }
}

// Sort all transactions by created_at DESC
usort($transactions, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
                    <h2 class="fw-bold mb-1">Wallet & Earnings</h2>
                    <p class="text-muted small mb-0">Track your credits, debits, and overall balance.</p>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Balance Cards -->
                    <div class="col-md-4">
                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-primary text-white border-0 shadow-sm h-100 text-center" style="border-radius: 20px;">
                            <h6 class="text-white-50 small mb-2" style="font-size: 0.78rem;">Available for Withdrawal</h6>
                            <h2 class="fw-extrabold mb-0 text-white" style="font-weight: 800; font-size: 1.8rem;">₹<?php echo number_format($balance, 2); ?></h2>
                            <hr class="border-white opacity-25 my-3">
                            <a href="withdraw.php" class="btn btn-gold btn-sm rounded-pill px-4 py-2 text-dark fw-bold shadow-sm" style="font-size: 0.78rem; width: auto; display: inline-block;">Withdraw Funds</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm h-100 text-center" style="border-radius: 20px;">
                            <h6 class="text-muted small mb-2" style="font-size: 0.78rem;">Locked Balance (Principal)</h6>
                            <h2 class="fw-extrabold mb-0 text-dark" style="font-weight: 800; font-size: 1.8rem; color: #2d3748 !important;">₹<?php echo number_format($locked_balance, 2); ?></h2>
                            <hr class="border-light opacity-10 my-3" style="border-top: 1px solid rgba(0,0,0,0.08);">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill" style="font-size: 0.68rem; font-weight: 700;"><i class="fas fa-lock me-1"></i> Locked for 3 Years</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-card p-3 px-2.5 p-sm-4 bg-white border-0 shadow-sm h-100 text-center" style="border-radius: 20px;">
                            <h6 class="text-muted small mb-2" style="font-size: 0.78rem;">Total Earnings History</h6>
                            <h2 class="fw-extrabold mb-0 text-success" style="font-weight: 800; font-size: 1.8rem;">₹<?php echo number_format($total_earnings, 2); ?></h2>
                            <hr class="border-light opacity-10 my-3" style="border-top: 1px solid rgba(0,0,0,0.08);">
                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill" style="font-size: 0.68rem; font-weight: 700;"><i class="fas fa-check-circle me-1"></i> Cumulative Income</span>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="premium-card p-3 px-2 px-sm-4 py-sm-4 bg-white border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 w-100">
                        <h5 class="fw-bold mb-0 text-center w-100 w-sm-auto" style="font-size: 1.1rem;">Full Transaction Log</h5>
                        <div class="mx-auto mx-sm-0">
                            <select class="form-select form-select-sm border border-secondary-subtle bg-light rounded-pill px-3 py-2" id="txTypeFilter" onchange="filterTransactions()">
                                <option value="all">All Types</option>
                                <option value="deposit">Plan Payments</option>
                                <option value="credit">Earnings & Bonuses</option>
                                <option value="debit">Withdrawals</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="border-0 px-3">Date</th>
                                    <th class="border-0 px-3">Type</th>
                                    <th class="border-0 px-3 text-end">Amount</th>
                                    <th class="border-0 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="txTableBody">
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $tx): 
                                        // Determine transaction label, colors, badges
                                        $source = $tx['tx_source'];
                                        $flow = $tx['flow'];
                                        $status = strtolower($tx['status']);
                                        
                                        $type_label = '';
                                        $type_icon = '';
                                        $amount_class = '';
                                        $amount_prefix = '';
                                        $status_class = '';

                                        if ($source == 'payment') {
                                            $type_label = 'Plan Payment (' . ($tx['detail'] ?: 'Processing') . ')';
                                            $type_icon = '<i class="fas fa-wallet text-primary me-2"></i>';
                                            $amount_class = 'text-dark';
                                            $amount_prefix = '- ';
                                        } elseif ($source == 'referral') {
                                            $type_label = 'Referral Commission (' . ($tx['detail'] ?: 'Bonus') . ')';
                                            $type_icon = '<i class="fas fa-users text-success me-2"></i>';
                                            $amount_class = 'text-success fw-bold';
                                            $amount_prefix = '+ ';
                                        } elseif ($source == 'withdrawal') {
                                            $type_label = 'Withdrawal Payout';
                                            $type_icon = '<i class="fas fa-arrow-down text-danger me-2"></i>';
                                            $amount_class = 'text-danger fw-bold';
                                            $amount_prefix = '- ';
                                        } elseif ($source == 'rental') {
                                            $type_label = 'Monthly Rental Payout (' . ($tx['detail'] ?: 'Rent') . ')';
                                            $type_icon = '<i class="fas fa-hotel text-success me-2"></i>';
                                            $amount_class = 'text-success fw-bold';
                                            $amount_prefix = '+ ';
                                        }

                                        // Status badge colors
                                        if ($status == 'approved' || $status == 'accepted') {
                                            $status_class = 'bg-success';
                                            $status_label = 'Approved';
                                        } elseif ($status == 'rejected') {
                                            $status_class = 'bg-danger';
                                            $status_label = 'Rejected';
                                        } else {
                                            $status_class = 'bg-warning text-dark';
                                            $status_label = 'Pending';
                                        }
                                    ?>
                                    <tr class="tx-row" data-flow="<?php echo htmlspecialchars($flow); ?>">
                                        <td class="px-3 py-3 small text-muted text-nowrap">
                                            <?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?>
                                        </td>
                                        <td class="px-3 py-3 font-medium">
                                            <?php echo $type_icon . htmlspecialchars($type_label); ?>
                                        </td>
                                        <td class="px-3 py-3 text-end font-semibold <?php echo $amount_class; ?>">
                                            <?php echo $amount_prefix; ?>₹<?php echo number_format($tx['amount'], 2); ?>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="badge <?php echo $status_class; ?> rounded-pill px-3 py-2 text-capitalize" style="font-size: 0.72rem;">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="noTxRow">
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-file-invoice-dollar fa-3x mb-3 opacity-25"></i>
                                                <p class="mb-0">No transaction history found yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No transactions match the selected filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function filterTransactions() {
    const filterValue = document.getElementById('txTypeFilter').value;
    const rows = document.querySelectorAll('.tx-row');
    const noResultsRow = document.getElementById('noResultsRow');
    const noTxRow = document.getElementById('noTxRow');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const flow = row.getAttribute('data-flow');
        if (filterValue === 'all' || flow === filterValue) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (noResultsRow) {
        if (visibleCount === 0 && rows.length > 0) {
            noResultsRow.style.display = '';
        } else {
            noResultsRow.style.display = 'none';
        }
    }
}
</script>
<?php include('include/footer.php'); ?>


