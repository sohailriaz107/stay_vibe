<?php 
require_once(__DIR__ . '/../includes/connect.php');

$msg = '';

// Delete Logic — MUST run before any HTML output
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $conn->query("DELETE FROM payment_collected WHERE id = $del_id");
    $_SESSION['flash_msg'] = 'Record deleted successfully.';
    header('Location: payment_summary.php');
    exit();
}

include('includes/header.php'); 
include('includes/sidebar.php'); 

// Add Collection Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_collection'])) {
    $amount = (float)$_POST['amount'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $date = $conn->real_escape_string($_POST['date']);
    
    if ($amount > 0 && !empty($description) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO payment_collected (amount, description, date) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("dss", $amount, $description, $date);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Collection added successfully.</div>';
            } else {
                $msg = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Failed to add collection.</div>';
            }
            $stmt->close();
        } else {
            $msg = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Database error.</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Please fill all fields.</div>';
    }
}

// Fetch Date Filter
$from_date = isset($_GET['from_date']) ? $conn->real_escape_string($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? $conn->real_escape_string($_GET['to_date']) : '';

// Base query for DataTable
$query = "SELECT * FROM payment_collected";
$conditions = [];
if (!empty($from_date)) {
    $conditions[] = "date >= '$from_date'";
}
if (!empty($to_date)) {
    $conditions[] = "date <= '$to_date'";
}
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY date DESC, id DESC";
$collections_res = $conn->query($query);

// Fetch Total Summary (regardless of date filter)
$sum_res = $conn->query("SELECT SUM(amount) as total FROM payment_collected");
$total_amount = $sum_res->fetch_assoc()['total'] ?? 0;

// Fetch settings for dynamic percentages
$settings_res = $conn->query("SELECT * FROM site_settings WHERE setting_key LIKE 'summary_%'");
$pcts = [
    'summary_real_estate' => 45,
    'summary_lease_hotels' => 20,
    'summary_others' => 15,
    'summary_interest' => 10,
    'summary_office_exp' => 10
];
if ($settings_res) {
    while ($row = $settings_res->fetch_assoc()) {
        $pcts[$row['setting_key']] = (float)$row['setting_value'];
    }
}

// Calculate the 5 parts
$real_estate = $total_amount * ($pcts['summary_real_estate'] / 100);  // Dynamic %
$lease_hotels = $total_amount * ($pcts['summary_lease_hotels'] / 100); // Dynamic %
$others = $total_amount * ($pcts['summary_others'] / 100);       // Dynamic %
$interest = $total_amount * ($pcts['summary_interest'] / 100);     // Dynamic %
$office_exp = $total_amount * ($pcts['summary_office_exp'] / 100);   // Dynamic %

?>

<div id="content">
    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert" id="flashMsg">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>setTimeout(() => { const el = document.getElementById('flashMsg'); if(el){ new bootstrap.Alert(el).close(); } }, 3000);</script>
    <?php unset($_SESSION['flash_msg']); endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-4 w-100">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-chart-pie text-primary me-2"></i>Payment Summary</h2>
            <p class="text-muted mb-0">Overview of collected payments and their allocations.</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addCollectionModal">
                <i class="fas fa-plus me-2"></i> Add Collection
            </button>
        </div>
    </div>

    <?php if ($msg) echo $msg; ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #2c3e50, #3498db); color: white;">
                <div class="card-body p-4 text-center">
                    <h5 class="opacity-75 mb-1 text-uppercase fw-bold" style="letter-spacing: 2px;">Total Collection</h5>
                    <h1 class="display-4 fw-bold mb-0">₹<?php echo number_format($total_amount, 2); ?></h1>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted fw-bold mb-0">Real Estate / Land</h6>
                        <span class="badge bg-primary rounded-pill"><?php echo $pcts['summary_real_estate']; ?>%</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo number_format($real_estate, 2); ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted fw-bold mb-0">Lease Hotels</h6>
                        <span class="badge bg-success rounded-pill"><?php echo $pcts['summary_lease_hotels']; ?>%</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo number_format($lease_hotels, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted fw-bold mb-0">Others</h6>
                        <span class="badge bg-warning text-dark rounded-pill"><?php echo $pcts['summary_others']; ?>%</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo number_format($others, 2); ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-danger">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted fw-bold mb-0">Interest Payment</h6>
                        <span class="badge bg-danger rounded-pill"><?php echo $pcts['summary_interest']; ?>%</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo number_format($interest, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted fw-bold mb-0">Office Expense</h6>
                        <span class="badge bg-info text-white rounded-pill"><?php echo $pcts['summary_office_exp']; ?>%</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo number_format($office_exp, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Records -->
    <div class="premium-table-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
            <h5 class="fw-bold mb-0">Collection History</h5>
            <form method="GET" action="payment_summary.php" class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-md-end align-items-center">
                <div class="d-flex flex-column flex-sm-row gap-2 w-100" style="max-width: 480px;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold flex-shrink-0" style="font-size: 12px; letter-spacing: 0.5px;">FROM</span>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($from_date); ?>" required>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold flex-shrink-0" style="font-size: 12px; letter-spacing: 0.5px;">TO</span>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to_date); ?>" required>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2 mt-2 mt-sm-0">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    <?php if(!empty($from_date) || !empty($to_date)): ?>
                        <a href="payment_summary.php" class="btn btn-light btn-sm border text-muted px-3">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <table id="collectionsTable" class="table align-middle w-100">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">Date</th>
                    <th class="text-start">Amount</th>
                    <th class="text-start">Description</th>
                    <th class="text-start">Added On</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($collections_res && $collections_res->num_rows > 0): ?>
                    <?php while($row = $collections_res->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start">#<?php echo $row['id']; ?></td>
                            <td class="text-start"><span class="badge bg-light text-dark border"><i class="far fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($row['date'])); ?></span></td>
                            <td class="text-start fw-bold text-success">₹<?php echo number_format($row['amount'], 2); ?></td>
                            <td class="text-start text-muted"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td class="text-start small text-muted"><?php echo date('d M Y h:i A', strtotime($row['timestamp'])); ?></td>
                            <td class="text-end">
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger rounded-circle shadow-sm" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- No rows, DataTables will display emptyTable message -->
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Collection Modal -->
<div class="modal fade" id="addCollectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i> Add New Collection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="payment_summary.php">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Amount (₹)</label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-lg bg-light border-0" placeholder="e.g. 50000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Date of Collection</label>
                        <input type="date" name="date" class="form-control bg-light border-0" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Description / Source</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Brief description of the payment source..." required></textarea>
                    </div>
                </div>
                <style>
                    @media (max-width: 576px) {
                        .btn-mobile-text {
                            font-size: 13px !important;
                            padding-left: 0.5rem !important;
                            padding-right: 0.5rem !important;
                        }
                    }
                </style>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between flex-nowrap w-100 gap-2">
                    <button type="button" class="btn btn-light rounded-pill w-100 btn-mobile-text" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_collection" class="btn btn-primary rounded-pill w-100 shadow-sm btn-mobile-text">Save Collection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        $('#collectionsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, "desc"]],
            columnDefs: [
                { targets: -1, orderable: false, searchable: false }
            ],
            language: {
                emptyTable: "<div class='text-center py-5 text-muted'><i class='fas fa-receipt fa-3x mb-3 opacity-25'></i><p class='mb-0'>No collections found.</p></div>",
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            }
        });
    });
</script>
</body>
</html>
