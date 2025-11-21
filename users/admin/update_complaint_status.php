<?php
require_once __DIR__ . '/../../config.php';
session_start();

// Check if admin or official is logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo "Unauthorized access.";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $c_id = intval($_POST['c_id'] ?? 0);
  $status = trim($_POST['status'] ?? '');

  if (!$c_id || !$status) {
    echo "Invalid request.";
    exit();
  }

  // Validate status
  $allowed_status = ['Pending', 'In-Progress', 'Resolved', 'Dismissed', 'Unresolved'];
  if (!in_array($status, $allowed_status)) {
    echo "Invalid status value.";
    exit();
  }

  $stmt = $conn->prepare("UPDATE tbl_complaints SET status = ? WHERE c_id = ?");
  $stmt->bind_param('si', $status, $c_id);

  if ($stmt->execute()) {
    echo "Complaint status updated to $status.";
  } else {
    echo "Failed to update complaint status.";
  }

  $stmt->close();
  $conn->close();
} else {
  echo "Invalid request method.";
}
?>
