<?php
session_start();
require_once '../../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// --- Helper function to send JSON response and exit ---
function json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// --- Security Check: Ensure user is a logged-in admin ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    json_response('error', 'Unauthorized access.');
}

// --- Handle POST request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids_to_delete = [];

    // Case 1: Single ID from form-urlencoded data
    if (isset($_POST['id'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $ids_to_delete[] = $id;
        }
    }
    // Case 2: Bulk IDs from JSON data
    elseif (isset($_POST['ids'])) {
        $decoded_ids = json_decode($_POST['ids'], true);
        if (is_array($decoded_ids)) {
            foreach ($decoded_ids as $id) {
                $validated_id = filter_var($id, FILTER_VALIDATE_INT);
                if ($validated_id) {
                    $ids_to_delete[] = $validated_id;
                }
            }
        }
    }

    // --- Perform deletion if there are valid IDs ---
    if (empty($ids_to_delete)) {
        json_response('error', 'No valid user IDs provided for deletion.');
    }

    // Prevent admin from deleting themselves
    $current_user_id = $_SESSION['user_id'];
    if (in_array($current_user_id, $ids_to_delete)) {
        json_response('error', 'You cannot delete your own account.');
    }

    // --- Prepare and execute the delete statement ---
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
    $types = str_repeat('i', count($ids_to_delete));

    $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids_to_delete);

    if ($stmt->execute()) {
        $count = $stmt->affected_rows;
        json_response('success', "$count user(s) deleted successfully.");
    } else {
        json_response('error', 'Failed to delete user(s): ' . $stmt->error);
    }

    $stmt->close();
} else {
    json_response('error', 'Invalid request method.');
}

$conn->close();
?>
