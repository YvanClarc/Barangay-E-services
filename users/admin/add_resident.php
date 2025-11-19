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

// --- Validate required fields ---
$required_fields = ['first_name', 'last_name', 'birth_date', 'gender', 'role', 'account_status'];
foreach ($required_fields as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        json_response('error', "Field '$field' is required.");
    }
}

// --- Assign variables from POST data ---
$user_id        = $_POST['user_id'] ?? null;
$first_name     = trim($_POST['first_name']);
$last_name      = trim($_POST['last_name']);
$birth_date     = $_POST['birth_date'];
$gender         = $_POST['gender'];
$role           = $_POST['role'];
$account_status = $_POST['account_status'];
$email          = trim($_POST['email'] ?? '');
$password       = $_POST['password'] ?? '';


/*
|--------------------------------|
| UPDATE EXISTING RESIDENT       |
|--------------------------------|
*/
if (!empty($user_id)) {
    $stmt = $conn->prepare(
        "UPDATE tbl_users 
         SET first_name = ?, last_name = ?, birth_date = ?, gender = ?, role = ?, account_status = ?
         WHERE id = ?"
    );
    $stmt->bind_param("ssssssi", $first_name, $last_name, $birth_date, $gender, $role, $account_status, $user_id);

    if ($stmt->execute()) {
        json_response('success', 'Resident updated successfully!');
    } else {
        json_response('error', 'Error updating resident: ' . $stmt->error);
    }
    $stmt->close();
}


/*
|--------------------------------|
| CREATE NEW RESIDENT            |
|--------------------------------|
*/
else {
    // --- Validate email and password for new users ---
    if (empty($email)) {
        json_response('error', 'Email is required for new users.');
    }
    if (empty($password)) {
        json_response('error', 'Password is required for new users.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response('error', 'Invalid email format.');
    }

    // --- Check for duplicate email ---
    $stmt = $conn->prepare("SELECT id FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        json_response('error', 'Email already exists!');
    }
    $stmt->close();

    // --- Insert new resident ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT INTO tbl_users (first_name, last_name, birth_date, gender, email, password, role, account_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssssss", $first_name, $last_name, $birth_date, $gender, $email, $hashed_password, $role, $account_status);

    if ($stmt->execute()) {
        json_response('success', 'Resident added successfully!');
    } else {
        json_response('error', 'Error saving resident: ' . $stmt->error);
    }
    $stmt->close();
}

$conn->close();
?>
