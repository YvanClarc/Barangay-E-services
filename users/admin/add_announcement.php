<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: official_dashboard.php');
    exit();
}

$created_by = $_SESSION['user_id'] ?? null;
$ann_id = intval($_POST['ann_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$details = trim($_POST['details'] ?? '');
$status = in_array($_POST['status'] ?? 'draft', ['published','draft']) ? $_POST['status'] : 'draft';

if (!$title || !$details) {
    $_SESSION['message'] = 'Title and details required.';
    $_SESSION['status'] = 'error';
    header('Location: official_dashboard.php');
    exit();
}

$image_path = null;

// Handle file upload
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            $_SESSION['message'] = 'Only JPG/PNG/WEBP/GIF allowed.';
            $_SESSION['status'] = 'error';
            header('Location: official_dashboard.php');
            exit();
        }
        $uploadsDir = __DIR__ . '/../../uploads/announcements';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = 'ann_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadsDir . '/' . $safeName;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $image_path = 'uploads/announcements/' . $safeName;
        }
    }
}

// Insert or Update
if ($ann_id > 0) {
    // Update existing
    if ($image_path) {
        $stmt = $conn->prepare("UPDATE tbl_announcements SET title=?, details=?, image_path=?, status=? WHERE ann_id=?");
        $stmt->bind_param("ssssi", $title, $details, $image_path, $status, $ann_id);
    } else {
        $stmt = $conn->prepare("UPDATE tbl_announcements SET title=?, details=?, status=? WHERE ann_id=?");
        $stmt->bind_param("sssi", $title, $details, $status, $ann_id);
    }
    $ok = $stmt->execute();
    $_SESSION['message'] = $ok ? 'Announcement updated.' : 'Failed to update.';
} else {
    // Insert new
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO tbl_announcements (title, details, image_path, status, created_at, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $details, $image_path, $status, $created_at, $created_by);
    $ok = $stmt->execute();
    $_SESSION['message'] = $ok ? 'Announcement posted.' : 'Failed to post.';
}
$stmt->close();
$_SESSION['status'] = $ok ? 'success' : 'error';

header('Location: official_dashboard.php');
exit();
?>