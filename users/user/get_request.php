<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (isset($_GET['r_id'])) {
    $user_id = $_SESSION['user_id'];
    $r_id = intval($_GET['r_id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_requests WHERE r_id=? AND id=?");
    $stmt->bind_param("ii", $r_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Request not found']);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing request ID']);
}
?>
