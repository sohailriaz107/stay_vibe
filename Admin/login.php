<?php
session_start();
require_once('../Frontend/includes/connect.php');

// If already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No admin found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Stay Vibes</title>
    <link rel="icon" type="image/jpeg" href="assets/css/imgs/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #0b2c4d 0%, #1a4a7a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: white; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); padding: 40px; width: 100%; max-width: 450px; }
        .admin-brand { color: #0b2c4d; font-weight: 700; text-align: center; margin-bottom: 6px; }
        .admin-brand span { color: #c5a059; }
        .form-control, .input-group-text { background: #f8f9fa; border: 0; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(11,44,77,0.12); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="assets/css/imgs/logo.png" alt="Stay Vibes" style="width: 65px; height: 65px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
        <h3 class="admin-brand">STAY <span>VIBES</span></h3>
    </div>
    <h5 class="text-center fw-bold mb-4">Admin Login</h5>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger text-center small fw-bold py-2"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="admin@stayvibes.com" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Enter password" required>
            </div>
        </div>
        <button type="submit" name="login" class="btn w-100 py-2 fw-bold text-white rounded-pill mb-3" style="background: #0b2c4d;">Login to Dashboard</button>
        <div class="text-center small text-muted">
            New admin? <a href="register.php" class="fw-bold text-decoration-none" style="color: #0b2c4d;">Create Account</a>
        </div>
    </form>
</div>

</body>
</html>
