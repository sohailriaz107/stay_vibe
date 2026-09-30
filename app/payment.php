<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');



$plan_name = isset($_GET['plan']) ? sanitize_input($_GET['plan']) : '';
$user_id = $_SESSION['user_id'];
$message = '';
$success = false;

// Fetch plan details from DB
$stmt_plan = $conn->prepare("SELECT * FROM plans WHERE plan_name = ?");
$stmt_plan->bind_param("s", $plan_name);
$stmt_plan->execute();
$plan_db = $stmt_plan->get_result()->fetch_assoc();
$stmt_plan->close();

if (!$plan_db) {
    header("Location: plans.php");
    exit();
}

$plan_id = $plan_db['id'];
$plan_amount = $plan_db['plan_price'];
$plan_rental = $plan_db['yearly_return_percent'] . '%';
$plan_referral = $plan_db['referral_percent'] . '%';
$plan_stay = $plan_db['free_stay_nights'] . 'N/' . $plan_db['free_stay_days'] . 'D';

// Check pending payments
$stmt_pending = $conn->prepare("SELECT COUNT(id) as pending_count FROM payments WHERE user_id = ? AND plan_id = ? AND status = 'pending'");
$stmt_pending->bind_param("ii", $user_id, $plan_id);
$stmt_pending->execute();
$has_pending = $stmt_pending->get_result()->fetch_assoc()['pending_count'] > 0;
$stmt_pending->close();

// Calculate total paid
$stmt_paid = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE user_id = ? AND plan_id = ? AND status = 'approved'");
$stmt_paid->bind_param("ii", $user_id, $plan_id);
$stmt_paid->execute();
$total_paid = $stmt_paid->get_result()->fetch_assoc()['total_paid'] ?? 0;
$stmt_paid->close();

$is_premium = (strpos($plan_name, 'Plan D') !== false || strpos($plan_name, 'Plan E') !== false);
$remaining_amount = $plan_amount - $total_paid;
$installments = [];

if ($remaining_amount > 0) {
    if ($is_premium) { // 20-20-60
        $inst1 = $plan_amount * 0.20;
        if ($total_paid == 0) {
            $installments["1st Installment (20%) - ₹" . number_format($inst1)] = $inst1;
            $installments["Full Payment - ₹" . number_format($plan_amount)] = $plan_amount;
        } elseif (abs($total_paid - $inst1) < 10) {
            $installments["2nd Installment (20%) - ₹" . number_format($inst1)] = $inst1;
            $installments["Remaining Full (80%) - ₹" . number_format($remaining_amount)] = $remaining_amount;
        } else {
            $installments["Final Installment (60%) - ₹" . number_format($remaining_amount)] = $remaining_amount;
        }
    } else { // 40-30-30
        $inst1 = $plan_amount * 0.40;
        $inst2 = $plan_amount * 0.30;
        if ($total_paid == 0) {
            $installments["1st Installment (40%) - ₹" . number_format($inst1)] = $inst1;
            $installments["Full Payment - ₹" . number_format($plan_amount)] = $plan_amount;
        } elseif (abs($total_paid - $inst1) < 10) {
            $installments["2nd Installment (30%) - ₹" . number_format($inst2)] = $inst2;
            $installments["Remaining Full (60%) - ₹" . number_format($remaining_amount)] = $remaining_amount;
        } else {
            $installments["Final Installment (30%) - ₹" . number_format($remaining_amount)] = $remaining_amount;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Security verification failed.</div>';
    } else {
        $utr = sanitize_input($_POST['utr_number']);
        $method = sanitize_input($_POST['payment_method']);
        
        // Handle file upload
        $target_dir = "../assets/uploads/payments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["screenshot"]["name"]);
        $target_file = $target_dir . $file_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image
        $check = getimagesize($_FILES["screenshot"]["tmp_name"]);
        if($check === false) {
            $message = '<div class="alert alert-danger">File is not an image.</div>';
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
                $screenshot_path = "assets/uploads/payments/" . $file_name;
                $payment_amount = (float)$_POST['payment_amount'];
                
                // Fetch plan ID from DB (already fetched earlier)
                // Insert payment record with plan ID
                $stmt = $conn->prepare("INSERT INTO payments (user_id, plan_id, amount, transaction_id, payment_method, screenshot, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("iidsss", $user_id, $plan_id, $payment_amount, $utr, $method, $screenshot_path);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $message = '<div class="alert alert-danger">Database error. Please try again.</div>';
                }
                $stmt->close();
            } else {
                $message = '<div class="alert alert-danger">Error uploading screenshot.</div>';
            }
        }
    }
}
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <?php if ($success): ?>
            <div class="row justify-content-center pt-5">
                <div class="col-lg-6 text-center">
                    <div class="premium-card p-5 bg-white border-0 shadow-lg" style="border-radius: 30px;">
                        <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(40, 167, 69, 0.1); color: #28a745; border-radius: 50%; font-size: 2.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Payment Submitted!</h2>
                        <p class="text-muted mb-4 lead">Your payment has been submitted successfully. Our team will verify your transaction shortly.</p>
                        <div class="p-3 mb-4 rounded-3 text-start" style="background: var(--bg-light); border-left: 5px solid var(--secondary-color);">
                            <div class="small fw-bold">Current Status:</div>
                            <div class="text-uppercase fw-bold text-warning" style="letter-spacing: 1px;">Pending Verification</div>
                        </div>
                        <a href="dashboard.php" class="btn btn-primary px-5 rounded-pill py-3 fw-bold">Go to Dashboard</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Left Column: Instructions & Details -->
                <div class="col-lg-7 px-2 px-sm-3">
                    <div class="premium-card p-3 p-sm-5 bg-white border-0 shadow-sm mb-4" style="border-radius: 25px;">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <span class="badge bg-primary px-3 rounded-pill mb-2">Selected Portfolio</span>
                                <h2 class="fw-bold mb-0"><?php echo $plan_name; ?></h2>
                            </div>
                            <div class="text-end">
                                <div class="mall mb-1 text-primary" style="line-height: 1;">Remaining Balance:  ₹<?php echo number_format($remaining_amount); ?></div>
                                <div class="small text-muted mb-1">(Total: ₹<?php echo number_format($plan_amount); ?>)</div>
                            </div>
                        </div>
                        
                        <div class="row gx-2 gy-2 mb-4">
                            <div class="col-4">
                                <div class="p-2 p-sm-3 rounded-3 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-muted mb-1" style="font-size: 0.72rem; font-weight: 500; line-height: 1.2;">Rental Income</div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem; font-size: calc(0.8rem + 0.15vw);"><?php echo $plan_rental; ?> / Yr</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 p-sm-3 rounded-3 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-muted mb-1" style="font-size: 0.72rem; font-weight: 500; line-height: 1.2;">Referral Bonus</div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem; font-size: calc(0.8rem + 0.15vw);"><?php echo $plan_referral; ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 p-sm-3 rounded-3 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-muted mb-1" style="font-size: 0.72rem; font-weight: 500; line-height: 1.2;">Free Stay</div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem; font-size: calc(0.8rem + 0.15vw);"><?php echo $plan_stay; ?></div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-10">

                        <h5 class="fw-bold mb-4"><i class="fas fa-university me-2 text-primary"></i> Company Bank Details</h5>
                        <div class="bg-primary text-white p-3 p-sm-4 rounded-4 shadow-sm mb-4">
                            <div class="row gx-2 gy-3">
                                <div class="col-7 col-sm-6">
                                    <div class="x-small-text opacity-75" style="font-size: 0.7rem;">Account Name</div>
                                    <div class="fw-bold" style="font-size: 0.8rem; font-size: calc(0.78rem + 0.12vw); line-height: 1.3;">STAY VIBES RESORT PVT LTD</div>
                                </div>
                                <div class="col-5 col-sm-6">
                                    <div class="x-small-text opacity-75" style="font-size: 0.7rem;">Bank Name</div>
                                    <div class="fw-bold" style="font-size: 0.8rem; font-size: calc(0.78rem + 0.12vw); line-height: 1.3;">HDFC BANK</div>
                                </div>
                                <div class="col-7 col-sm-6">
                                    <div class="x-small-text opacity-75" style="font-size: 0.7rem;">Account Number</div>
                                    <div class="fw-bold" style="font-size: 0.8rem; font-size: calc(0.78rem + 0.12vw); line-height: 1.3;">50200088997766</div>
                                </div>
                                <div class="col-5 col-sm-6">
                                    <div class="x-small-text opacity-75" style="font-size: 0.7rem;">IFSC Code</div>
                                    <div class="fw-bold" style="font-size: 0.8rem; font-size: calc(0.78rem + 0.12vw); line-height: 1.3;">HDFC0001234</div>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4"><i class="fas fa-qrcode me-2 text-primary"></i> UPI Payment (QR Code)</h5>
                        <div class="row align-items-center">
                            <div class="col-md-5 text-center mb-3 mb-md-0">
                                <img src="../assets/imgs/qr_placeholder.png" alt="Payment QR" class="img-fluid rounded-4 shadow-sm" style="max-width: 180px; border: 5px solid white;">
                            </div>
                            <div class="col-md-7">
                                <div class="p-3 rounded-4 border-dashed border-2 text-center" style="border: 2px dashed #ccc;">
                                    <div class="small text-muted mb-1">UPI ID</div>
                                    <div class="h5 fw-bold mb-0">stayvibes@upi</div>
                                </div>
                                <p class="small text-muted mt-3 mb-0">Scan the QR code or use the UPI ID to make the payment from any UPI app like PhonePe, Google Pay, or Paytm.</p>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card p-4 bg-white border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-3">Simple Steps to Invest:</h6>
                        <ul class="list-unstyled mb-0 x-small-text text-muted">
                            <li class="mb-2 d-flex align-items-center"><i class="fas fa-arrow-right text-primary me-2"></i> Make payment to the above Bank or UPI details.</li>
                            <li class="mb-2 d-flex align-items-center"><i class="fas fa-arrow-right text-primary me-2"></i> Save the transaction screenshot.</li>
                            <li class="mb-2 d-flex align-items-center"><i class="fas fa-arrow-right text-primary me-2"></i> Note down the UTR/Transaction ID.</li>
                            <li class="mb-0 d-flex align-items-center"><i class="fas fa-arrow-right text-primary me-2"></i> Upload proof using the form on the right.</li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Submission Form -->
                <div class="col-lg-5 px-2 px-sm-3">
                    <div class="premium-card p-3 p-sm-4 bg-white border-0 shadow-lg sticky-top" style="border-radius: 25px; top: 100px;">
                        <h4 class="fw-bold mb-3 mb-sm-4 text-center">Submit Proof</h4>
                        <?php echo $message; ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                            
                            <?php if($remaining_amount <= 0): ?>
                                <div class="alert alert-success mt-3 text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                    You have fully paid for this plan!
                                </div>
                            <?php elseif($has_pending): ?>
                                <div class="alert alert-warning mt-3 text-center">
                                    <i class="fas fa-clock fa-2x mb-2"></i><br>
                                    Your previous payment is pending approval.<br>Please wait before submitting another installment.
                                </div>
                            <?php else: ?>
                            
                            <div class="mb-3 mb-sm-4">
                                <label class="form-label small fw-bold mb-1.5">Select Installment Amount</label>
                                <select name="payment_amount" class="form-select border-0 bg-light py-2.5 px-3 rounded-3" required style="font-size: 0.95rem;">
                                    <option value="">Choose Amount to Pay</option>
                                    <?php foreach($installments as $label => $amt): ?>
                                        <option value="<?php echo $amt; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3 mb-sm-4">
                                <label class="form-label small fw-bold mb-1.5">Payment Method</label>
                                <select name="payment_method" class="form-select border-0 bg-light py-2.5 px-3 rounded-3" required style="font-size: 0.95rem;">
                                    <option value="">Select Method</option>
                                    <option value="UPI">UPI (PhonePe/GPay)</option>
                                    <option value="Bank Transfer">Bank Transfer (IMPS/NEFT)</option>
                                    <option value="Scanner">QR Scanner</option>
                                </select>
                            </div>

                            <div class="mb-3 mb-sm-4">
                                <label class="form-label small fw-bold mb-1.5">Transaction ID / UTR Number</label>
                                <input type="text" name="utr_number" class="form-control border-0 bg-light py-2.5 px-3 rounded-3" placeholder="Enter 12-digit UTR" required style="font-size: 0.95rem;">
                            </div>

                            <div class="mb-3 mb-sm-4">
                                <label class="form-label small fw-bold mb-1.5">Upload Payment Screenshot</label>
                                <div class="upload-area p-3 p-sm-4 rounded-4 text-center bg-light border-dashed border-2 position-relative" style="border: 2px dashed #dee2e6;">
                                    <input type="file" name="screenshot" id="fileInput" class="d-none" accept="image/*" required>
                                    <label for="fileInput" id="uploadLabel" class="mb-0 cursor-pointer w-100" style="cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <div class="fw-bold text-primary" style="font-size: 0.95rem;">Click to Upload</div>
                                        <div class="x-small-text text-muted">JPG, PNG (Max 2MB)</div>
                                    </label>
                                    <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded-3 mt-3 d-none mx-auto shadow-sm" style="max-height: 180px; object-fit: contain;">
                                    <div id="fileName" class="mt-2 small fw-bold text-success"></div>
                                </div>
                            </div>

                            <button type="submit" name="submit_payment" class="btn btn-primary w-100 py-2.5 fw-bold text-uppercase shadow-sm" style="border-radius: 12px; font-size: 0.95rem; letter-spacing: 0.5px;">Submit Payment Proof</button>
                            <p class="text-center x-small-text text-muted mt-3 mb-0">By clicking submit, you confirm the payment details are accurate.</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.getElementById('fileInput').onchange = function (event) {
  const file = event.target.files[0];
  if (file) {
      document.getElementById('fileName').innerText = "Selected: " + file.name;
      const reader = new FileReader();
      reader.onload = function(e) {
          const imgPreview = document.getElementById('imagePreview');
          imgPreview.src = e.target.result;
          imgPreview.classList.remove('d-none');
          document.getElementById('uploadLabel').classList.add('d-none');
      }
      reader.readAsDataURL(file);
  }
};
</script>

<style>
.border-dashed { border-style: dashed !important; }
.cursor-pointer { cursor: pointer; }
.upload-area:hover { background-color: #f8f9fa !important; border-color: var(--primary-color) !important; }
.x-small-text { font-size: 0.75rem; }
</style>

<?php include('include/footer.php'); ?>
