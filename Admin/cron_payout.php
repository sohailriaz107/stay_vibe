<?php
// This file should be run via Cron Job once every day (e.g., at midnight).
require_once(__DIR__ . '/../Frontend/includes/connect.php');

$today = date('Y-m-d');

$query = "SELECT u.id, u.wallet_balance, p.plan_price, p.yearly_return_percent, up.next_payout_date, up.id as user_plan_id 
          FROM users u 
          JOIN user_plans up ON u.id = up.user_id AND up.status = 'active'
          JOIN plans p ON up.plan_id = p.id 
          WHERE u.status = 'active' 
          AND up.activation_date IS NOT NULL 
          AND up.next_payout_date <= CURDATE()";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
        $user_id = $user['id'];
        $plan_price = $user['plan_price'];
        $yearly_return = $user['yearly_return_percent'];
        $current_balance = $user['wallet_balance'];
        
        // Calculate monthly payout
        // (Plan Price * Yearly Percentage) / 12
        $yearly_amount = ($plan_price * $yearly_return) / 100;
        $monthly_amount = $yearly_amount / 12;
        
        // Next payout will be exactly 1 month from the *current scheduled date* (not today), to keep it consistent.
        $next_date = date('Y-m-d', strtotime($user['next_payout_date'] . ' +1 month'));

        // Update User Balance and Total Earnings
        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ?, total_earnings = total_earnings + ? WHERE id = ?");
        $stmt->bind_param("ddi", $monthly_amount, $monthly_amount, $user_id);
        
        if ($stmt->execute()) {
            // Update next_payout_date in user_plans
            $up_id = $user['user_plan_id'];
            $conn->query("UPDATE user_plans SET next_payout_date = '$next_date' WHERE id = $up_id");
            // Insert into transactions/wallet history (Assuming a transactions table exists or will exist)
            // If you don't have a transactions table yet, you can create one. For now, we just update the balance.
            
            // Example transaction insert:
            /*
            $type = 'credit';
            $desc = 'Monthly Rental Income for ' . $user['active_plan'];
            $t_stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)");
            $t_stmt->bind_param("idss", $user_id, $monthly_amount, $type, $desc);
            $t_stmt->execute();
            */
        }
    }
    echo "Cron completed successfully. Payouts processed.";
} else {
    echo "Cron completed. No payouts scheduled for today.";
}
?>
