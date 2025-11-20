<?php

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';

// Check if user is admin or official
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'official'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Validate GET r_id
if (!isset($_GET['r_id']) || !is_numeric($_GET['r_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request ID'
    ]);
    exit();
}

$r_id = (int) $_GET['r_id'];

try {
    // Fetch request details
    $stmt = $conn->prepare(
        "SELECT r.r_id, r.first_name, r.second_name, r.last_name, r.gender, r.age, r.address, r.email,
                r.document_type, r.purpose, r.r_status, r.date_requested, r.pickup_datetime, r.fees, r.instructions,
                u.first_name AS user_first, u.last_name AS user_last, u.birth_date, u.gender AS user_gender
         FROM tbl_requests r
         LEFT JOIN tbl_users u ON r.id = u.id
         WHERE r.r_id = ?"
    );

    $stmt->bind_param("i", $r_id);
    $stmt->execute();

    $result = $stmt->get_result();
    if (!$result) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: failed to fetch result'
        ]);
        exit();
    }

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
        exit();
    }

    $request = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'request' => $request
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
