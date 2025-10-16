<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) exit('Unauthorized');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['r_id'])) {
    $user_id = $_SESSION['user_id'];
    $r_id = intval($_POST['r_id']);
    $stmt = $conn->prepare("DELETE FROM tbl_requests WHERE r_id=? AND id=?");
    $stmt->bind_param("ii", $r_id, $user_id);
    $stmt->execute();
    $stmt->close();
    echo "success";
}
?>