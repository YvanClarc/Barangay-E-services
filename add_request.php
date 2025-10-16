<?php
// filepath: e:\xampp\htdocs\HTML-CSS-PHP-projects\Barangay E-services\add_request.php
session_start();
require_once 'config.php';

// Make sure the user is logged in and has an ID in session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];

    // Get and sanitize form inputs
    $first_name   = trim($_POST['first_name']);
    $second_name  = trim($_POST['second_name']);
    $last_name    = trim($_POST['last_name']);
    $gender       = trim($_POST['gender']);
    $age          = intval($_POST['age']);
    $address      = trim($_POST['address']);
    $doc_type     = trim($_POST['doc_type']);
    $purpose      = trim($_POST['purpose']);
    $date_requested = date('Y-m-d H:i:s'); // or just 'Y-m-d' if you want only the date

    // Get user's email from tbl_users (for data consistency)
    $stmt = $conn->prepare("SELECT email FROM tbl_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if (isset($_POST['edit_r_id']) && !empty($_POST['edit_r_id'])) {
        // Update existing request
        $edit_r_id = intval($_POST['edit_r_id']);
        $stmt = $conn->prepare("UPDATE tbl_requests SET first_name=?, second_name=?, last_name=?, gender=?, age=?, address=?, document_type=?, purpose=? WHERE r_id=? AND id=?");
        $stmt->bind_param("ssssisssii", $first_name, $second_name, $last_name, $gender, $age, $address, $doc_type, $purpose, $edit_r_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Request updated successfully!";
        header("Location: user_dashboard.php");
        exit();
    } else {
        // Insert into tbl_requests
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO tbl_requests (id, first_name, second_name, last_name, gender, age, address, email, document_type, purpose, r_status, date_requested) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issssissssss",
            $user_id,
            $first_name,
            $second_name,
            $last_name,
            $gender,
            $age,
            $address,
            $email,
            $doc_type,
            $purpose,
            $status,
            $date_requested
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "Request submitted successfully!";
        } else {
            $_SESSION['message'] = "Failed to submit request. Please try again.";
        }
        $stmt->close();

        // Redirect back to dashboard (or show a message)
        header("Location: user_dashboard.php");
        exit();
    }
} else {
    // If accessed directly, redirect
    header("Location: user_dashboard.php");
    exit();
}
?>