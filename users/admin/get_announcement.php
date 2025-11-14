<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (isset($_GET['ann_id'])) {
    $ann_id = intval($_GET['ann_id']);
    $stmt = $conn->prepare("SELECT ann_id, title, details, image_path, status FROM tbl_announcements WHERE ann_id=?");
    $stmt->bind_param("i", $ann_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo json_encode($data ?: []);
    $stmt->close();
}
?>