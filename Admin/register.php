<?php
session_start();
require_once('../Frontend/includes/connect.php');

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name     = sanitize_input($_POST['name']);
    $email    = sanitize_input($_POST['email']);
    $phone    = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "An admin with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
            if ($stmt->execute()) {
                $success = "Admin account created successfully! <a href='login.php' class='fw-bold text-success'>Login now &rarr;</a>";
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register | Stay Vibes</title>
    <link rel="icon" type="image/jpeg" href="assets/css/imgs/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0b2c4d 0%, #1a4a7a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 520px;
        }
        .admin-brand { color: #0b2c4d; font-weight: 700; text-align: center; margin-bottom: 6px; }
        .admin-brand span { color: #c5a059; }
        .form-control, .input-group-text {
            background: #f8f9fa;
            border: 0;
        }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(11,44,77,0.12); }
        .btn-register {
            background: linear-gradient(135deg, #0b2c4d, #1a4a7a);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-register:hover { opacity: 0.9; color: white; }
        .secret-note {
            background: #fff8e1;
            border-left: 4px solid #c5a059;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            color: #7a6100;
        }
    </style>
</head>
<body>

<div class="register-card">
    <!-- Brand -->
    <div class="text-center mb-4">
        <img src="assets/css/imgs/logo.png" alt="Stay Vibes" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 12px;">
        <h3 class="admin-brand">STAY <span>VIBES</span></h3>
        <p class="text-muted small">Create a new Admin Account</p>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger text-center small fw-bold py-2 rounded-3"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success text-center small py-2 rounded-3"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Full Name</label>
            <div class="input-group rounded-3 overflow-hidden">
                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="name" class="form-control py-2" placeholder="Admin Full Name" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Email Address</label>
            <div class="input-group rounded-3 overflow-hidden">
                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control py-2" placeholder="admin@stayvibes.com" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Phone Number</label>
            <div class="input-group rounded-3 overflow-hidden">
                <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                <input type="text" name="phone" class="form-control py-2" placeholder="+91 XXXXX XXXXX">
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-6">
                <label class="form-label small fw-bold text-muted">Password</label>
                <input type="password" name="password" class="form-control py-2" placeholder="Min 6 chars" required>
            </div>
            <div class="col-6">
                <label class="form-label small fw-bold text-muted">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control py-2" placeholder="Repeat" required>
            </div>
        </div>
        <button type="submit" name="register" class="btn btn-register w-100 py-2 mb-3">
            <i class="fas fa-user-plus me-2"></i> Create Admin Account
        </button>
        <div class="text-center small text-muted">
            Already have an account? <a href="login.php" class="fw-bold text-decoration-none" style="color: #0b2c4d;">Login Here</a>
        </div>
    </form>
</div>

</body>
</html>
