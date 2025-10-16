<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['r_id'], $_POST['status'])) {
    $r_id = intval($_POST['r_id']);
    $status = $_POST['status'];
    // Only allow approved or denied
    if (!in_array($status, ['approved', 'denied'])) exit('Invalid status');
    $stmt = $conn->prepare("UPDATE tbl_requests SET r_status=? WHERE r_id=?");
    $stmt->bind_param("si", $status, $r_id);
    $stmt->execute();
    $stmt->close();
    echo "success";
} else {
    echo "Invalid request";
}
?>