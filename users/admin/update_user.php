<?php
session_start();
require_once '../../config.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// --- Helper function to send JSON response and exit ---
function json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// --- Security Check: Ensure user is a logged-in admin or official ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'official'])) {
    json_response('error', 'Unauthorized access.');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($id && $status) {
        // Validate inputs
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            json_response('error', 'Invalid user ID.');
        }

        // Validate status values
        $valid_statuses = ['active', 'pending', 'denied'];
        if (!in_array($status, $valid_statuses)) {
            json_response('error', 'Invalid status value.');
        }

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE tbl_users SET account_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            json_response('success', 'User status updated successfully.');
        } else {
            json_response('error', 'Failed to update user status: ' . $stmt->error);
        }

        $stmt->close();
    } else {
        json_response('error', 'Missing required parameters.');
    }
} else {
    json_response('error', 'Invalid request method.');
}

$conn->close();
?>
