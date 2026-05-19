<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

$msg = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE bank_details SET kyc_status = 'accepted' WHERE id = $id");
        $msg = '<div class="alert alert-success">KYC Status officially Accepted!</div>';
    } elseif ($action == 'reject') {
        $conn->query("UPDATE bank_details SET kyc_status = 'rejected' WHERE id = $id");
        $msg = '<div class="alert alert-danger">KYC Status Rejected!</div>';
    }
}

// Fetch all bank details
$query = "SELECT bd.*, u.full_name, u.email, u.mobile_number 
          FROM bank_details bd
          JOIN users u ON bd.user_id = u.id
          ORDER BY bd.created_at DESC";
$result = $conn->query($query);
?>

<div id="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-5 w-100">
        <div>
            <h2 class="fw-bold mb-1">User Bank Information</h2>
            <p class="text-muted">Review user bank details and verify KYC status for withdrawals.</p>
        </div>
    </div>

    <?php if ($msg) echo $msg; ?>

    <div class="premium-table-card">
        <div class="table-responsive">
            <table id="bankInfoTable" class="table w-100">
                <thead>
                    <tr>
                        <th>SR</th>
                        <th>User Identity</th>
                        <th>Bank Name</th>
                        <th>Account No.</th>
                        <th>IFSC Code</th>
                        <th>PAN Number</th>
                        <th>KYC Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $sr = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $sr++; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($row['mobile_number']); ?></div>
                                </td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['bank_name']); ?></td>
                                <td class="font-monospace fw-bold text-primary"><?php echo htmlspecialchars($row['account_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['ifsc_code']); ?></td>
                                <td class="text-uppercase font-monospace"><?php echo htmlspecialchars($row['pan_number']); ?></td>
                                <td>
                                    <?php 
                                        $s = $row['kyc_status'];
                                        if($s == 'accepted') echo '<span class="badge-status badge-active"><i class="fas fa-check-circle me-1"></i> Accepted</span>';
                                        elseif($s == 'rejected') echo '<span class="badge-status badge-pending text-danger bg-danger bg-opacity-10"><i class="fas fa-times-circle me-1"></i> Rejected</span>';
                                        else echo '<span class="badge-status badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['kyc_status'] == 'pending'): ?>
                                        <div class="d-flex gap-2">
                                            <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" onclick="return confirm('Approve this KYC?')">Approve</a>
                                            <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" onclick="return confirm('Reject this KYC?')">Reject</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small italic"><i class="fas fa-lock"></i> Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-university fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No bank details submitted yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#bankInfoTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search bank details..."
            }
        });
    });
</script>
</body>
</html>
