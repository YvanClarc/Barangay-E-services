<?php
require_once __DIR__ . '/../../config.php';
session_start();

// Check if admin or official is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $c_id = intval($_GET['c_id'] ?? 0);

    if (!$c_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid complaint ID.']);
        exit();
    }

    // Get all hearings for this complaint
    $stmt = $conn->prepare("
        SELECT h_id, hearing_no, date, time, status, created_at
        FROM tbl_hearings
        WHERE c_id = ?
        ORDER BY hearing_no ASC
    ");
    $stmt->bind_param('i', $c_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $hearings = [];
    while ($row = $result->fetch_assoc()) {
        $hearings[] = [
            'h_id' => $row['h_id'],
            'hearing_no' => $row['hearing_no'],
            'date' => $row['date'],
            'time' => $row['time'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode(['success' => true, 'hearings' => $hearings]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
