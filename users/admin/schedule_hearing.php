<?php
session_start();
require_once '../../config.php';

// Basic safety: ensure $conn exists
if (!isset($conn)) {
    die('Database connection not configured.');
}

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

header('Content-Type: application/json');

// Get POST data
$c_id = isset($_POST['c_id']) ? intval($_POST['c_id']) : 0;
$hearing_no = isset($_POST['hearing_no']) ? intval($_POST['hearing_no']) : 0;
$hearing_date = isset($_POST['hearing_date']) ? trim($_POST['hearing_date']) : '';
$hearing_time = isset($_POST['hearing_time']) ? trim($_POST['hearing_time']) : '';

// Validate input
if (!$c_id || !$hearing_no || !$hearing_date || !$hearing_time) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate hearing number (1-3)
if ($hearing_no < 1 || $hearing_no > 3) {
    echo json_encode(['success' => false, 'message' => 'Invalid hearing number']);
    exit();
}

// Validate date (not in the past)
$current_date = date('Y-m-d');
if ($hearing_date < $current_date) {
    echo json_encode(['success' => false, 'message' => 'Hearing date cannot be in the past']);
    exit();
}

// Check if complaint exists and is in progress
$complaint_check = $conn->prepare("SELECT status, complaint_type FROM tbl_complaints WHERE c_id = ?");
$complaint_check->bind_param("i", $c_id);
$complaint_check->execute();
$complaint_result = $complaint_check->get_result();

if ($complaint_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Complaint not found']);
    exit();
}

$complaint = $complaint_result->fetch_assoc();
if (strtolower($complaint['status']) !== 'in-progress') {
    echo json_encode(['success' => false, 'message' => 'Complaint must be in progress to schedule hearings']);
    exit();
}

// Check if complaint type is conflict-related
$complaint_type = strtolower($complaint['complaint_type']);
if ($complaint_type !== 'conflict with neighbor' && $complaint_type !== 'conflict with persons') {
    echo json_encode(['success' => false, 'message' => 'Hearings can only be scheduled for conflict complaints']);
    exit();
}

// Check if hearing number already exists for this complaint
$hearing_check = $conn->prepare("SELECT h_id FROM tbl_hearings WHERE c_id = ? AND hearing_no = ?");
$hearing_check->bind_param("ii", $c_id, $hearing_no);
$hearing_check->execute();

if ($hearing_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => "Hearing {$hearing_no} already exists for this complaint"]);
    exit();
}

// Check if maximum hearings (3) have been reached
$hearing_count_check = $conn->prepare("SELECT COUNT(*) as hearing_count FROM tbl_hearings WHERE c_id = ?");
$hearing_count_check->bind_param("i", $c_id);
$hearing_count_check->execute();
$hearing_count = $hearing_count_check->get_result()->fetch_assoc()['hearing_count'];

if ($hearing_count >= 3) {
    echo json_encode(['success' => false, 'message' => 'Maximum of 3 hearings allowed per complaint']);
    exit();
}

// Insert hearing record
$insert_hearing = $conn->prepare("INSERT INTO tbl_hearings (c_id, hearing_no, date, time, status, created_at) VALUES (?, ?, ?, ?, 'Scheduled', NOW())");
$insert_hearing->bind_param("iiss", $c_id, $hearing_no, $hearing_date, $hearing_time);

if ($insert_hearing->execute()) {
    echo json_encode(['success' => true, 'message' => "Hearing {$hearing_no} scheduled successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to schedule hearing: ' . $conn->error]);
}

$insert_hearing->close();
$complaint_check->close();
$hearing_check->close();
$hearing_count_check->close();
$conn->close();
?>
