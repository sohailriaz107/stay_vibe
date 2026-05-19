<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

$message = '';

// Handle Employee Actions (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Add Employee
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $designation = sanitize_input($_POST['designation']);

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM employees WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> An employee with this email already exists!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, designation) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $designation);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Employee added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> Error adding employee: ' . $conn->error . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
            $stmt->close();
        }
        $check->close();
    }

    // 2. Edit Employee
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $designation = sanitize_input($_POST['designation']);

        // Check if email already exists for another employee
        $check = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Email already in use by another employee!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, designation = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $designation, $id);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Employee updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> Error updating employee: ' . $conn->error . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
            $stmt->close();
        }
        $check->close();
    }

    // 3. Log Daily Activity
    if (isset($_POST['action']) && $_POST['action'] === 'log') {
        $employee_id = (int)$_POST['employee_id'];
        $log_date = sanitize_input($_POST['log_date']);
        $business_amount = (float)sanitize_input($_POST['business_amount']);
        $registrations_count = (int)sanitize_input($_POST['registrations_count']);

        // Insert or Update on duplicate key
        $stmt = $conn->prepare("
            INSERT INTO employee_logs (employee_id, log_date, business_amount, registrations_count) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                business_amount = VALUES(business_amount), 
                registrations_count = VALUES(registrations_count)
        ");
        $stmt->bind_param("isdi", $employee_id, $log_date, $business_amount, $registrations_count);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Daily business logs updated successfully for ' . date('d M Y', strtotime($log_date)) . '!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Error logging business stats: ' . $conn->error . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        $stmt->close();
    }
}

// Handle GET Actions (Delete Employee or Delete Log)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Delete Employee
    if ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Employee and related logs deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Error deleting employee: ' . $conn->error . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        $stmt->close();
    }

    // Delete Log
    if ($action === 'delete_log' && isset($_GET['log_id'])) {
        $log_id = (int)$_GET['log_id'];
        $stmt = $conn->prepare("DELETE FROM employee_logs WHERE id = ?");
        $stmt->bind_param("i", $log_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Daily log entry deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Error deleting entry: ' . $conn->error . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        $stmt->close();
    }
}

// Helper Sanitization function in case not defined globally
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

// Fetch general stats
$tot_emp_res = $conn->query("SELECT COUNT(id) as c FROM employees");
$tot_employees = $tot_emp_res->fetch_assoc()['c'] ?? 0;

$tot_biz_res = $conn->query("SELECT SUM(business_amount) as s, SUM(registrations_count) as r FROM employee_logs");
$tot_biz_row = $tot_biz_res->fetch_assoc();
$total_company_business = $tot_biz_row['s'] ?? 0;
$total_company_registrations = $tot_biz_row['r'] ?? 0;

// Top performer logic (Highest business_amount brought by an employee)
$top_perf_query = "
    SELECT e.name, e.designation, SUM(el.business_amount) as total_biz, SUM(el.registrations_count) as total_reg
    FROM employees e
    JOIN employee_logs el ON e.id = el.employee_id
    GROUP BY e.id
    ORDER BY total_biz DESC, total_reg DESC
    LIMIT 1
";
$top_perf_res = $conn->query($top_perf_query);
$top_performer = null;
if ($top_perf_res && $top_perf_res->num_rows > 0) {
    $top_performer = $top_perf_res->fetch_assoc();
}

// Fetch all employees and aggregate stats
$emp_list_query = "
    SELECT e.*, 
           COALESCE(SUM(el.business_amount), 0) as total_business,
           COALESCE(SUM(el.registrations_count), 0) as total_registrations,
           MAX(el.log_date) as last_activity_date
    FROM employees e
    LEFT JOIN employee_logs el ON e.id = el.employee_id
    GROUP BY e.id
    ORDER BY total_business DESC, e.name ASC
";
$employees_res = $conn->query($emp_list_query);

// Fetch logs for active sub-view if requested (History view modal)
$selected_employee_logs = [];
$selected_employee_info = null;
if (isset($_GET['view_history_id'])) {
    $hist_id = (int)$_GET['view_history_id'];
    // Info
    $info_stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $info_stmt->bind_param("i", $hist_id);
    $info_stmt->execute();
    $selected_employee_info = $info_stmt->get_result()->fetch_assoc();
    $info_stmt->close();

    if ($selected_employee_info) {
        $logs_stmt = $conn->prepare("SELECT * FROM employee_logs WHERE employee_id = ? ORDER BY log_date DESC");
        $logs_stmt->bind_param("i", $hist_id);
        $logs_stmt->execute();
        $logs_res = $logs_stmt->get_result();
        while ($row = $logs_res->fetch_assoc()) {
            $selected_employee_logs[] = $row;
        }
        $logs_stmt->close();
    }
}
?>

<div id="content">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-5 w-100">
        <div>
            <h2 class="fw-bold mb-1">Employee Management</h2>
            <p class="text-muted mb-0">Track field executives, register sales, daily logs, and evaluate department performance.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fas fa-user-plus me-2"></i> Add New Employee
        </button>
    </div>

    <!-- Alert Message -->
    <?php echo $message; ?>

    <!-- Summary Stats -->
    <div class="row g-4 mb-5">
        <!-- Total Employees -->
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 5px solid var(--admin-primary);">
                <div class="d-flex align-items-center mb-2 justify-content-between">
                    <h6 class="text-muted small text-uppercase mb-0 fw-bold">Active Staff</h6>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 small">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0"><?php echo number_format($tot_employees); ?></h2>
                <p class="text-muted small mb-0 mt-2">Registered executives in system</p>
            </div>
        </div>

        <!-- Total Business -->
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 5px solid #28a745;">
                <div class="d-flex align-items-center mb-2 justify-content-between">
                    <h6 class="text-muted small text-uppercase mb-0 fw-bold">Total Sales Volume</h6>
                    <div class="bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-success">₹<?php echo number_format($total_company_business, 2); ?></h2>
                <p class="text-muted small mb-0 mt-2"><?php echo number_format($total_company_registrations); ?> Total accounts converted</p>
            </div>
        </div>

        <!-- Top Performer -->
        <div class="col-md-4">
            <div class="stat-card" style="border-left: 5px solid var(--admin-secondary);">
                <?php if ($top_performer): ?>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($top_performer['name']); ?>&background=c5a059&color=081d33" class="rounded-circle me-3" width="45">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($top_performer['name']); ?></h6>
                                <p class="small text-muted mb-0"><?php echo htmlspecialchars($top_performer['designation']); ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold rounded-pill">Top Performer</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                        <span class="small text-muted">Total Contribution:</span>
                        <span class="small fw-bold text-success">₹<?php echo number_format($top_performer['total_biz'], 2); ?></span>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center py-3 text-muted">
                        <i class="fas fa-trophy fa-lg me-2 opacity-50"></i> No sales recorded yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="premium-table-card">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h5 class="fw-bold mb-0"><i class="fas fa-users-cog text-primary me-2"></i> Executive Performance Directory</h5>
        </div>
        <div class="table-responsive">
            <table id="employeesTable" class="table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr class="text-muted small">
                        <th>SR</th>
                        <th>Employee</th>
                        <th>Designation</th>
                        <th>Contact Info</th>
                        <th class="text-center">Registrations</th>
                        <th class="text-end">Total Business</th>
                        <th>Last Daily Activity</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees_res && $employees_res->num_rows > 0): ?>
                        <?php $sr = 1; ?>
                        <?php while ($emp = $employees_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $sr++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($emp['name']); ?>&background=random" class="rounded-circle me-3" width="40">
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($emp['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-1.5"><?php echo htmlspecialchars($emp['designation']); ?></span>
                                </td>
                                <td>
                                    <div class="small text-dark fw-medium"><i class="fas fa-envelope text-muted me-1.5"></i> <?php echo htmlspecialchars($emp['email']); ?></div>
                                    <div class="small text-muted mt-0.5"><i class="fas fa-phone text-muted me-1.5"></i> <?php echo htmlspecialchars($emp['phone'] ?: 'N/A'); ?></div>
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    <?php echo number_format($emp['total_registrations']); ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₹<?php echo number_format($emp['total_business'], 2); ?>
                                </td>
                                <td class="small text-muted">
                                    <?php echo $emp['last_activity_date'] ? date('d M Y', strtotime($emp['last_activity_date'])) : '<span class="text-muted italic">No activity yet</span>'; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Log Activity Button -->
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="openLogModal(<?php echo $emp['id']; ?>, '<?php echo addslashes($emp['name']); ?>')">
                                            <i class="fas fa-calendar-plus me-1"></i> Log Stats
                                        </button>
                                        <!-- View History Button -->
                                        <a href="?view_history_id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-history me-1"></i> History
                                        </a>
                                        <!-- Edit button -->
                                        <button class="btn btn-sm btn-light rounded-circle" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($emp)); ?>)">
                                            <i class="fas fa-pencil-alt text-muted"></i>
                                        </button>
                                        <!-- Delete button -->
                                        <a href="?action=delete&id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-light rounded-circle text-danger" onclick="return confirm('Are you sure you want to permanently delete this employee and all their logs?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-user-friends fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No employees registered yet.</p>
                                <small>Add employees using the button at the top right to start tracking performance!</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================= MODALS ======================================= -->

<!-- 1. Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="fas fa-user-plus text-primary me-2"></i> Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0" required placeholder="e.g. Arjun Mehta">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control bg-light border-0" required placeholder="e.g. arjun@stayvibes.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control bg-light border-0" placeholder="e.g. 9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Designation</label>
                        <input type="text" name="designation" class="form-control bg-light border-0" value="Field Executive" required placeholder="e.g. Area Manager, Sales Executive">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold mt-2">Create Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="fas fa-user-edit text-primary me-2"></i> Update Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control bg-light border-0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Designation</label>
                        <input type="text" name="designation" id="edit_designation" class="form-control bg-light border-0" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold mt-2">Save Updates</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. Log Daily Stats Modal -->
<div class="modal fade" id="logStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-success"><i class="fas fa-calendar-plus me-2"></i> Daily Performance Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Add or update metrics for <span id="log_emp_name" class="fw-bold text-dark"></span></p>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="log">
                    <input type="hidden" name="employee_id" id="log_emp_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Log Date</label>
                        <input type="date" name="log_date" class="form-control bg-light border-0" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Registrations Completed Today</label>
                        <input type="number" min="0" name="registrations_count" class="form-control bg-light border-0" required placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Total Sales Volume (INR)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₹</span>
                            <input type="number" step="0.01" min="0" name="business_amount" class="form-control bg-light border-0" required placeholder="0.00">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 rounded-pill py-2.5 fw-bold mt-2">Submit Stats</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 4. View History Modal / Subpage Simulation -->
<?php if ($selected_employee_info): ?>
    <div class="modal fade show" id="historyModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-history text-primary me-2"></i> Performance Log: <?php echo htmlspecialchars($selected_employee_info['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($selected_employee_info['designation']); ?> | <?php echo htmlspecialchars($selected_employee_info['email']); ?></p>
                    </div>
                    <a href="employees.php" class="btn-close"></a>
                </div>
                <div class="modal-body py-4">
                    <!-- aggregate cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="bg-primary p-3 rounded-4 text-center text-white border-0">
                                <h6 class="text-white-50 small mb-1">Lifetime business</h6>
                                <h4 class="fw-bold mb-0 text-white">
                                    ₹<?php 
                                        $biz_sum = array_sum(array_column($selected_employee_logs, 'business_amount'));
                                        echo number_format($biz_sum, 2);
                                    ?>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-success p-3 rounded-4 text-center text-white border-0">
                                <h6 class="text-white-50 small mb-1">Lifetime registrations</h6>
                                <h4 class="fw-bold mb-0 text-white">
                                    <?php 
                                        $reg_sum = array_sum(array_column($selected_employee_logs, 'registrations_count'));
                                        echo number_format($reg_sum);
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr class="text-muted small">
                                    <th>Date</th>
                                    <th class="text-center">Registrations</th>
                                    <th class="text-end">Business Volume</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($selected_employee_logs)): ?>
                                    <?php foreach ($selected_employee_logs as $log): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?php echo date('d M Y', strtotime($log['log_date'])); ?></td>
                                            <td class="text-center fw-semibold text-primary"><?php echo number_format($log['registrations_count']); ?></td>
                                            <td class="text-end fw-bold text-success">₹<?php echo number_format($log['business_amount'], 2); ?></td>
                                            <td class="text-center">
                                                <a href="?action=delete_log&log_id=<?php echo $log['id']; ?>&view_history_id=<?php echo $hist_id; ?>" class="btn btn-sm btn-light text-danger rounded-circle" onclick="return confirm('Delete this record entry?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No logs submitted for this employee yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="employees.php" class="btn btn-light rounded-pill px-4">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JS Modal Helpers -->
<script>
function openEditModal(emp) {
    document.getElementById('edit_id').value = emp.id;
    document.getElementById('edit_name').value = emp.name;
    document.getElementById('edit_email').value = emp.email;
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_designation').value = emp.designation || 'Field Executive';
    
    // Trigger modal open
    var editModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    editModal.show();
}

function openLogModal(id, name) {
    document.getElementById('log_emp_id').value = id;
    document.getElementById('log_emp_name').innerText = name;
    
    var logModal = new bootstrap.Modal(document.getElementById('logStatsModal'));
    logModal.show();
}
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#employeesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, "desc"]], // Default sort by Total Business descending (index 5)
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search employees..."
            }
        });
    });
</script>
</body>
</html>
