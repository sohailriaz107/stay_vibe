<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');

$user_id = $_SESSION['user_id'];

// Get user general info for header display
$stmt = $conn->prepare("SELECT full_name, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding d-flex align-items-center" style="min-height: calc(100vh - 75px); background: #f7f9fc;">
    <style>
    .app-hub-container {
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
    }
    .app-hub-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-top: 30px;
    }
    .app-hub-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 35px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(0,0,0,0.01);
    }
    .app-hub-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(201, 162, 39, 0.12);
        border-color: rgba(201, 162, 39, 0.25);
    }
    .app-hub-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 1.8rem;
        transition: all 0.3s ease;
    }
    .app-hub-label {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2d3748;
        text-align: center;
        line-height: 1.3;
    }
    
    @media (max-width: 991px) {
        .app-hub-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .app-hub-card {
            padding: 30px 15px;
        }
    }
    
    @media (max-width: 576px) {
        .app-hub-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .app-hub-card {
            padding: 25px 12px;
            border-radius: 20px;
        }
        .app-hub-icon-wrapper {
            width: 60px;
            height: 60px;
            font-size: 1.50rem;
            margin-bottom: 12px;
        }
        .app-hub-label {
            font-size: 0.85rem;
        }
    }
    </style>
    
    <div class="container py-4">
        <div class="app-hub-container">
            <div class="text-center mb-4">
                <h2 class="fw-extrabold mb-1" style="font-weight: 800; letter-spacing: -0.5px; color: #1a202c;">Welcome to Stay Vibes Hub</h2>
                <p class="text-muted small">Manage your investment portfolio, withdrawals, and referrals</p>
            </div>
            
            <div class="app-hub-grid">
                <!-- 1. Overview -->
                <a href="dashboard.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(201, 162, 39, 0.08); color: var(--secondary-color);">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div class="app-hub-label">Overview</div>
                </a>
                
                <!-- 2. Active Plan -->
                <a href="my-plan.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(13, 110, 253, 0.08); color: #0d6efd;">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="app-hub-label">Active Plan</div>
                </a>
                
                
             
                
                <!-- 4. Withdrawal -->
                <a href="withdraw.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(220, 53, 69, 0.08); color: #dc3545;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="app-hub-label">Withdrawal</div>
                </a>
                
                <!-- 5. Wallet History -->
                <a href="wallet.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(13, 202, 240, 0.08); color: #0dcaf0;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="app-hub-label">Wallet History</div>
                </a>
                
                <!-- 6. Referral -->
                <a href="referrals.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(111, 66, 193, 0.08); color: #6f42c1;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="app-hub-label">Referral</div>
                </a>
                
                <!-- 7. Bank Details -->
                <a href="bank-details.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(108, 117, 125, 0.08); color: #6c757d;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="app-hub-label">Bank Details</div>
                </a>
                 <!-- 8. all Plans -->
              
              
                <!-- 10. change password -->
                <a href="change_password.php" class="app-hub-card">
                    <div class="app-hub-icon-wrapper" style="background: rgba(111, 66, 193, 0.08); color: #6f42c1;">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="app-hub-label">Change Password</div>
                </a>
            </div>

            <!-- Back to Corporate Website Action -->
            <div class="text-center mt-5 mb-2">
                <a href="../index.php" class="btn btn-outline-gold rounded-pill px-4 py-2.5 fw-semibold shadow-sm" style="font-size: 0.82rem; border-color: rgba(201, 162, 39, 0.4); color: var(--secondary-color); transition: all 0.3s ease;">
                    <i class="fas fa-arrow-left me-2"></i> Back to Website
                </a>
            </div>
        </div>
    </div>
</section>
<?php include('include/footer.php'); ?>
