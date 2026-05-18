<?php 
$is_subpage = true;
include('../includes/header.php'); 
include('../includes/connect.php');

$message = '';
if (isset($_GET['suspended']) && $_GET['suspended'] == 1) {
    $message = '<div class="alert alert-danger">Your session was terminated because your account has been suspended by the Administration.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Security error: CSRF token mismatch.</div>';
    } else {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, full_name, password, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_status'] = $user['status'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Invalid password.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">User not found.</div>';
        }
        $stmt->close();
    }
}
?>
<?php include('../includes/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="premium-card p-5 bg-white shadow-lg border-0" style="border-radius: 30px;">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Investor Login</h2>
                        <p class="text-muted">Welcome back to Stay Vibes Resort.</p>
                    </div>

                    <?php echo $message; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="Enter your password" required>
                        </div>
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label small" for="rememberMe">Remember Me</label>
                            </div>
                            <a href="#" class="small text-primary fw-bold">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-gold btn-lg w-100 py-3 fw-bold">Login to Account</button>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">Don't have an account? <a href="register.php" class="text-primary fw-bold">Create one now</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
