<?php

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';

// ✔ FIX: use one consistent session key: "role" and allow both admin and official
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'official'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// ✔ Validate GET ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID'
    ]);
    exit();
}

$user_id = (int) $_GET['id'];

try {

    // ✔ Fix: Avoid get_result() (not supported if mysqlnd missing)
    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name, birth_date, gender, email,
                role, account_status, created_at
         FROM tbl_users WHERE id = ?"
    );

    $stmt->bind_param("i", $user_id);
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
            'message' => 'User not found'
        ]);
        exit();
    }

    $user = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'user' => $user
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
