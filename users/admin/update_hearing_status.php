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
    $h_id = intval($_POST['h_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if (!$h_id || !$status) {
        echo "Invalid request.";
        exit();
    }

    // Validate status
    $allowed_status = ['Scheduled', 'Completed', 'Rescheduled'];
    if (!in_array($status, $allowed_status)) {
        echo "Invalid status value.";
        exit();
    }

    $stmt = $conn->prepare("UPDATE tbl_hearings SET status = ? WHERE h_id = ?");
    $stmt->bind_param('si', $status, $h_id);

    if ($stmt->execute()) {
        echo "Hearing status updated to $status.";

        // If marking as completed, check if all hearings are completed and update complaint status if needed
        if ($status === 'Completed') {
            // Get the complaint ID
            $complaint_query = $conn->prepare("SELECT c_id FROM tbl_hearings WHERE h_id = ?");
            $complaint_query->bind_param('i', $h_id);
            $complaint_query->execute();
            $complaint_result = $complaint_query->get_result();
            $complaint = $complaint_result->fetch_assoc();
            $c_id = $complaint['c_id'];

            // Check if all hearings for this complaint are completed
            $check_query = $conn->prepare("
                SELECT COUNT(*) as total_hearings,
                       COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_hearings
                FROM tbl_hearings
                WHERE c_id = ?
            ");
            $check_query->bind_param('i', $c_id);
            $check_query->execute();
            $check_result = $check_query->get_result();
            $check_data = $check_result->fetch_assoc();

            // If all hearings are completed and complaint is still In-Progress, set to Unresolved
            if ($check_data['total_hearings'] > 0 && $check_data['total_hearings'] === $check_data['completed_hearings']) {
                $update_complaint = $conn->prepare("UPDATE tbl_complaints SET status = 'Unresolved' WHERE c_id = ? AND status = 'In-Progress'");
                $update_complaint->bind_param('i', $c_id);
                $update_complaint->execute();
                $update_complaint->close();
            }

            $complaint_query->close();
            $check_query->close();
        }
    } else {
        echo "Failed to update hearing status.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
