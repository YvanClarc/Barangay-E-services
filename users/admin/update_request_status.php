<?php
session_start();
require '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['r_id'], $_POST['status'])) {
    $r_id = intval($_POST['r_id']);
    $status = $_POST['status'];
    // Only allow approved or denied
    if (!in_array($status, ['approved', 'denied'])) exit('Invalid status');

    if ($status === 'approved') {
        // For approved, handle scheduling
        $pickup_date = $_POST['pickup_date'] ?? null;
        $pickup_time = $_POST['pickup_time'] ?? null;
        $fees = floatval($_POST['fees'] ?? 0);
        $instructions = $_POST['instructions'] ?? '';

        if (!$pickup_date || !$pickup_time) {
            echo "Pickup date and time are required for approval.";
            exit;
        }

        $pickup_datetime = $pickup_date . ' ' . $pickup_time;

        $stmt = $conn->prepare("UPDATE tbl_requests SET r_status=?, pickup_datetime=?, fees=?, instructions=? WHERE r_id=?");
        $stmt->bind_param("ssdsi", $status, $pickup_datetime, $fees, $instructions, $r_id);
        $stmt->execute();
        $stmt->close();

        // Notification is handled in the dashboard - user can view pickup details

        echo "success";
    } else {
        // For denied, just update status
        $stmt = $conn->prepare("UPDATE tbl_requests SET r_status=? WHERE r_id=?");
        $stmt->bind_param("si", $status, $r_id);
        $stmt->execute();
        $stmt->close();
        echo "success";
    }
} else {
    echo "Invalid request";
}
?>
