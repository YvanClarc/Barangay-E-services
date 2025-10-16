<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) exit('Unauthorized');
if (isset($_GET['r_id'])) {
    $user_id = $_SESSION['user_id'];
    $r_id = intval($_GET['r_id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_requests WHERE r_id=? AND id=?");
    $stmt->bind_param("ii", $r_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo json_encode($data);
    $stmt->close();
}
?>