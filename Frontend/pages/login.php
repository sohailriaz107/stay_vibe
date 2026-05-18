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
                
                header("Location: ../App/index.php");
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

<section class="section-padding bg-light" style="min-height: 100vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 px-2 px-sm-3">
                <div class="premium-card p-3 p-sm-5 bg-white shadow-lg border-0" style="border-radius: 30px;">
                    <div class="text-center mb-4">
                        <a href="../index.php">
                            <img src="../assets/imgs/logo.png" alt="Stay Vibes Logo" class="mb-3" style="max-height: 70px; object-fit: contain;">
                        </a>
                        <h2 class="fw-bold">Investor Login</h2>
                        <p class="text-muted">Welcome back to Stay Vibes Resort.</p>
                    </div>

                    <?php echo $message; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Enter your email" required style="font-size: 0.95rem;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control bg-light border-0 py-2.5 px-3 rounded-3" placeholder="Enter your password" required style="font-size: 0.95rem;">
                        </div>
                        <div class="mb-4 d-flex flex-column flex-sm-row justify-content-sm-between align-items-start align-items-sm-center gap-2 gap-sm-0">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label small" for="rememberMe">Remember Me</label>
                            </div>
                            <a href="#" class="small text-primary fw-bold text-decoration-none">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 py-2.5 fw-bold text-uppercase" style="border-radius: 12px; font-size: 0.95rem; letter-spacing: 0.5px;">Login to Account</button>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">Don't have an account? <a href="register.php" class="text-primary fw-bold">Create one now</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


