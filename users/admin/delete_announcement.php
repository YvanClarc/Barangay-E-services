<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "ERROR: Not a POST request";
    exit();
}

if (!isset($_POST['ann_id']) || empty($_POST['ann_id'])) {
    echo "ERROR: ann_id missing";
    exit();
}

$ann_id = intval($_POST['ann_id']);

// STEP 1 — Fetch image path
$stmt = $conn->prepare("SELECT image_path FROM tbl_announcements WHERE ann_id=?");
$stmt->bind_param("i", $ann_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "ERROR: Announcement not found";
    exit();
}

// STEP 2 — Delete image file (if exists)
if (!empty($row['image_path'])) {
    $cleanPath = ltrim($row['image_path'], '/');
    $filePath = realpath(__DIR__ . '/../../' . $cleanPath);

    if ($filePath && file_exists($filePath)) {
        unlink($filePath);
    }
}

// STEP 3 — Delete database record
$stmt = $conn->prepare("DELETE FROM tbl_announcements WHERE ann_id=?");
$stmt->bind_param("i", $ann_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "DB ERROR: " . $stmt->error;
}

$stmt->close();
?>
