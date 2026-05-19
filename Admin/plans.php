<?php 
include('includes/header.php'); 
include('includes/sidebar.php'); 
require_once('../Frontend/includes/connect.php');

$msg = '';

// Handle Plan Deletion
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Delete features first
    $stmt = $conn->prepare("DELETE FROM plan_features WHERE plan_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM plans WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success">Plan deleted successfully.</div>';
    }
    $stmt->close();
}

// Handle Status Toggle
if(isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $stmt = $conn->prepare("UPDATE plans SET status = NOT status WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: plans.php");
    exit();
}

// Handle Plan Save (Add/Edit)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_plan'])) {
    $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
    $name = sanitize_input($_POST['plan_name']);
    $price = $_POST['plan_price'];
    $yearly_return = $_POST['yearly_return_percent'];
    $referral = $_POST['referral_percent'];
    $insurance = $_POST['insurance_amount'];
    $nights = $_POST['free_stay_nights'];
    $days = $_POST['free_stay_days'];
    $hotel_type = sanitize_input($_POST['hotel_type']);
    $lockin = $_POST['lockin_years'];
    $membership = $_POST['membership_years'];
    $adults = (int)$_POST['adults'];
    $children = (int)$_POST['children'];
    $physical_land = isset($_POST['physical_land']) ? 1 : 0;
    $company_buyback = isset($_POST['company_buyback']) ? 1 : 0;
    
    if($plan_id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE plans SET plan_name=?, plan_price=?, yearly_return_percent=?, referral_percent=?, insurance_amount=?, free_stay_nights=?, free_stay_days=?, hotel_type=?, lockin_years=?, membership_years=?, adults=?, children=?, physical_land=?, company_buyback=? WHERE id=?");
        $stmt->bind_param("sddddiisssiiiii", $name, $price, $yearly_return, $referral, $insurance, $nights, $days, $hotel_type, $lockin, $membership, $adults, $children, $physical_land, $company_buyback, $plan_id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO plans (plan_name, plan_price, yearly_return_percent, referral_percent, insurance_amount, free_stay_nights, free_stay_days, hotel_type, lockin_years, membership_years, adults, children, physical_land, company_buyback) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddddiisssiiii", $name, $price, $yearly_return, $referral, $insurance, $nights, $days, $hotel_type, $lockin, $membership, $adults, $children, $physical_land, $company_buyback);
    }

    if($stmt->execute()) {
        $current_plan_id = ($plan_id > 0) ? $plan_id : $conn->insert_id;
        
        // Handle Features
        if($plan_id > 0) {
            $conn->query("DELETE FROM plan_features WHERE plan_id = $plan_id");
        }
        
        if(isset($_POST['features']) && is_array($_POST['features'])) {
            $feat_stmt = $conn->prepare("INSERT INTO plan_features (plan_id, feature_title) VALUES (?, ?)");
            foreach($_POST['features'] as $feature) {
                if(!empty(trim($feature))) {
                    $feat_stmt->bind_param("is", $current_plan_id, $feature);
                    $feat_stmt->execute();
                }
            }
            $feat_stmt->close();
        }
        $msg = '<div class="alert alert-success">Plan saved successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error saving plan: ' . $conn->error . '</div>';
    }
    $stmt->close();
}

$all_plans = $conn->query("SELECT * FROM plans");
?>

<div id="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center text-center text-md-start gap-3 mb-4 w-100">
        <div>
            <h2 class="fw-bold mb-1">Investment Plans</h2>
            <p class="text-muted">Manage investment portfolios and benefits.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#planModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i> Create New Plan
        </button>
    </div>

    <?php echo $msg; ?>

    <div class="row g-4">
        <?php while($plan = $all_plans->fetch_assoc()): ?>
        <div class="col-lg-4 col-md-6">
            <div class="premium-table-card h-100 <?php echo $plan['status'] == 0 ? 'opacity-75' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($plan['plan_name']); ?></h5>
                        <div class="display-6 fw-bold text-primary my-2">₹<?php echo number_format($plan['plan_price']); ?></div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick='editPlan(<?php echo json_encode($plan); ?>)'><i class="fas fa-edit me-2 text-primary"></i> Edit Plan</a></li>
                            <li><a class="dropdown-item" href="?toggle_status=<?php echo $plan['id']; ?>"><i class="fas fa-power-off me-2 <?php echo $plan['status'] ? 'text-warning' : 'text-success'; ?>"></i> <?php echo $plan['status'] ? 'Disable' : 'Enable'; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?delete=<?php echo $plan['id']; ?>" onclick="return confirm('Are you sure?')"><i class="fas fa-trash me-2"></i> Delete</a></li>
                        </ul>
                    </div>
                </div>

                <div class="plan-details small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Yearly Return:</span>
                        <span class="fw-bold text-success"><?php echo $plan['yearly_return_percent']; ?>%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Referral Bonus:</span>
                        <span class="fw-bold text-primary"><?php echo $plan['referral_percent']; ?>%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stay:</span>
                        <span class="fw-bold"><?php echo $plan['free_stay_nights']; ?>N/<?php echo $plan['free_stay_days']; ?>D (<?php echo $plan['hotel_type']; ?>)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Occupancy:</span>
                        <span class="fw-bold text-dark"><?php echo $plan['adults']; ?> Adults + <?php echo $plan['children']; ?> Child</span>
                    </div>
                </div>

                <hr class="my-3 opacity-10">
                
                <h6 class="small fw-bold mb-2">Included Features:</h6>
                <ul class="list-unstyled mb-0 small">
                    <?php 
                    $pid = $plan['id'];
                    $feats = $conn->query("SELECT * FROM plan_features WHERE plan_id = $pid");
                    while($f = $feats->fetch_assoc()):
                    ?>
                    <li class="mb-1 text-muted"><i class="fas fa-check-circle text-success me-2"></i><?php echo htmlspecialchars($f['feature_title']); ?></li>
                    <?php endwhile; ?>
                    <?php if($plan['physical_land']): ?>
                        <li class="mb-1 text-muted"><i class="fas fa-map-marked-alt text-secondary me-2"></i> Physical Land Registry</li>
                    <?php endif; ?>
                    <?php if($plan['company_buyback']): ?>
                        <li class="mb-1 text-muted"><i class="fas fa-undo text-info me-2"></i> Company Buyback Guarantee</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="planForm" action="" method="POST">
                    <input type="hidden" name="plan_id" id="plan_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Plan Name</label>
                            <input type="text" name="plan_name" id="plan_name" class="form-control bg-light border-0" placeholder="e.g. Luxury Plan A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Price (₹)</label>
                            <input type="number" step="0.01" name="plan_price" id="plan_price" class="form-control bg-light border-0" placeholder="20999" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Yearly Return (%)</label>
                            <input type="number" step="0.01" name="yearly_return_percent" id="yearly_return_percent" class="form-control bg-light border-0" placeholder="18" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Referral (%)</label>
                            <input type="number" step="0.01" name="referral_percent" id="referral_percent" class="form-control bg-light border-0" placeholder="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Insurance Amount (₹)</label>
                            <input type="number" step="0.01" name="insurance_amount" id="insurance_amount" class="form-control bg-light border-0" placeholder="300000" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Stay Nights</label>
                            <input type="number" name="free_stay_nights" id="free_stay_nights" class="form-control bg-light border-0" placeholder="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Stay Days</label>
                            <input type="number" name="free_stay_days" id="free_stay_days" class="form-control bg-light border-0" placeholder="2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hotel Type</label>
                            <input type="text" name="hotel_type" id="hotel_type" class="form-control bg-light border-0" placeholder="2-3 Star Hotels" required>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Adults</label>
                                <input type="number" name="adults" id="adults" class="form-control bg-light border-0" value="2" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Children</label>
                                <input type="number" name="children" id="children" class="form-control bg-light border-0" value="1" readonly required>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Lock-in (Years)</label>
                            <input type="number" name="lockin_years" id="lockin_years" class="form-control bg-light border-0" value="3" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Membership (Years)</label>
                            <input type="number" name="membership_years" id="membership_years" class="form-control bg-light border-0" value="5" required>
                        </div>
                        
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="physical_land" id="physical_land">
                                <label class="form-check-label small fw-bold">Physical Land Registry</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="company_buyback" id="company_buyback">
                                <label class="form-check-label small fw-bold">Company Buyback Guarantee</label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label small fw-bold">Additional Features</label>
                            <div id="feature_container">
                                <div class="input-group mb-2">
                                    <input type="text" name="features[]" class="form-control bg-light border-0" placeholder="Enter a feature title">
                                    <button class="btn btn-outline-secondary" type="button" onclick="addFeatureField()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="save_plan" class="btn btn-primary rounded-pill px-5">Save Plan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addFeatureField(value = '') {
    const container = document.getElementById('feature_container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="features[]" class="form-control bg-light border-0" placeholder="Enter a feature title" value="${value}">
        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(div);
}

function resetForm() {
    document.getElementById('planForm').reset();
    document.getElementById('plan_id').value = '';
    document.getElementById('modalTitle').innerText = 'Create New Plan';
    document.getElementById('feature_container').innerHTML = `
        <div class="input-group mb-2">
            <input type="text" name="features[]" class="form-control bg-light border-0" placeholder="Enter a feature title">
            <button class="btn btn-outline-secondary" type="button" onclick="addFeatureField()"><i class="fas fa-plus"></i></button>
        </div>
    `;
}

function editPlan(plan) {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Edit Plan: ' + plan.plan_name;
    document.getElementById('plan_id').value = plan.id;
    document.getElementById('plan_name').value = plan.plan_name;
    document.getElementById('plan_price').value = plan.plan_price;
    document.getElementById('yearly_return_percent').value = plan.yearly_return_percent;
    document.getElementById('referral_percent').value = plan.referral_percent;
    document.getElementById('insurance_amount').value = plan.insurance_amount;
    document.getElementById('free_stay_nights').value = plan.free_stay_nights;
    document.getElementById('free_stay_days').value = plan.free_stay_days;
    document.getElementById('hotel_type').value = plan.hotel_type;
    document.getElementById('lockin_years').value = plan.lockin_years;
    document.getElementById('membership_years').value = plan.membership_years;
    document.getElementById('adults').value = plan.adults || 2;
    document.getElementById('children').value = plan.children || 1;
    document.getElementById('physical_land').checked = plan.physical_land == 1;
    document.getElementById('company_buyback').checked = plan.company_buyback == 1;

    // Fetch features via AJAX or pass them if available
    // For now, let's assume we need to fetch them or we could have passed them in the JSON
    // A better way is to pass them along with the plan object if possible, but let's do a quick fetch
    fetch('get_features.php?plan_id=' + plan.id)
        .then(response => response.json())
        .then(features => {
            const container = document.getElementById('feature_container');
            container.innerHTML = ''; // Clear defaults
            if(features.length === 0) {
                addFeatureField();
            } else {
                features.forEach((f, index) => {
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2';
                    div.innerHTML = `
                        <input type="text" name="features[]" class="form-control bg-light border-0" placeholder="Enter a feature title" value="${f.feature_title}">
                        ${index === 0 ? 
                            '<button class="btn btn-outline-secondary" type="button" onclick="addFeatureField()"><i class="fas fa-plus"></i></button>' : 
                            '<button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>'}
                    `;
                    container.appendChild(div);
                });
            }
        });

    var myModal = new bootstrap.Modal(document.getElementById('planModal'));
    myModal.show();
}
</script>

<?php include('includes/footer.php'); ?>
