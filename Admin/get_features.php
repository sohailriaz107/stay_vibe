<?php
require_once('../Frontend/includes/connect.php');

if(isset($_GET['plan_id'])) {
    $plan_id = (int)$_GET['plan_id'];
    $stmt = $conn->prepare("SELECT feature_title FROM plan_features WHERE plan_id = ?");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $features = [];
    while($row = $result->fetch_assoc()) {
        $features[] = $row;
    }
    echo json_encode($features);
} else {
    echo json_encode([]);
}
?>
