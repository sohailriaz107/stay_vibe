<?php
include('includes/header.php');
include('includes/sidebar.php');
require_once('../Frontend/includes/connect.php');

// Mark as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE inquiries SET status='read' WHERE id=$id");
    header("Location: inquiries.php");
    exit();
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM inquiries WHERE id=$id");
    header("Location: inquiries.php");
    exit();
}

// Count new
$new_count = $conn->query("SELECT COUNT(*) as c FROM inquiries WHERE status='new'")->fetch_assoc()['c'];

// Fetch all
$inquiries = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
?>

<div id="content">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-1">Inquiry Messages</h2>
        <p class="text-muted">Contact form submissions from potential investors.</p>
        <?php if($new_count > 0): ?>
        <span class="badge bg-danger p-2 px-3 rounded-pill mt-2">
            <i class="fas fa-envelope me-1"></i> <?php echo $new_count; ?> New Message<?php echo $new_count > 1 ? 's' : ''; ?>
        </span>
        <?php endif; ?>
    </div>

    <div class="premium-card bg-white border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="table-responsive">
            <table id="inquiriesTable" class="table table-hover mb-0 w-100">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-bold text-center">SR</th>
                        <th class="px-3 py-3 text-muted small fw-bold">Name</th>
                        <th class="px-3 py-3 text-muted small fw-bold">Email / Phone</th>
                        <th class="px-3 py-3 text-muted small fw-bold text-center">Plan</th>
                        <th class="px-3 py-3 text-muted small fw-bold">Message</th>
                        <th class="px-3 py-3 text-muted small fw-bold text-center">Date</th>
                        <th class="px-3 py-3 text-muted small fw-bold text-center">Status</th>
                        <th class="px-3 py-3 text-muted small fw-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inquiries && $inquiries->num_rows > 0): ?>
                        <?php $sr = 1; ?>
                        <?php while ($row = $inquiries->fetch_assoc()): ?>
                        <tr class="<?php echo $row['status'] == 'new' ? 'table-warning' : ''; ?>">
                            <td class="px-3 py-3 fw-bold text-muted small text-center"><?php echo $sr++; ?></td>
                            <td class="px-3 py-3 fw-bold" style="white-space: nowrap;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td class="px-3 py-3 small">
                                <div><?php echo htmlspecialchars($row['email']); ?></div>
                                <div class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="badge bg-primary text-nowrap rounded-pill px-3">
                                    <?php echo htmlspecialchars($row['plan'] ?: 'N/A'); ?>
                                </span>
                            </td>
                            <td class="px-3 py-3 small text-muted" style="max-width: 200px;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;" title="<?php echo htmlspecialchars($row['message']); ?>">
                                    <?php echo htmlspecialchars($row['message']); ?>
                                </div>
                            </td>
                            <td class="px-3 py-3 small text-muted text-center text-nowrap">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?><br>
                                <small class="text-secondary"><?php echo date('h:i A', strtotime($row['created_at'])); ?></small>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($row['status'] == 'new'): ?>
                                    <span class="badge bg-danger rounded-pill px-3">New</span>
                                <?php elseif($row['status'] == 'read'): ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-3">Replied</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <?php if($row['status'] == 'new'): ?>
                                    <a href="?mark_read=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    <!-- Copy Email button (no new browser) -->
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                        title="Copy Email: <?php echo htmlspecialchars($row['email']); ?>"
                                        onclick="copyEmail('<?php echo htmlspecialchars($row['email']); ?>', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Delete" onclick="return confirm('Delete this inquiry?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                No inquiry messages yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyEmail(email, btn) {
    navigator.clipboard.writeText(email).then(function() {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 1500);
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#inquiriesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, "desc"]], // Default sort by Date column descending
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search inquiries..."
            }
        });
    });
</script>
</body>
</html>
