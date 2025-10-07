<?php
session_start();
require_once 'config.php';

if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $account_status = 'pending';

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Email already exists!";
        $status = "error";
    } else {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, birth_date, gender, email, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $first_name, $last_name, $birth_date, $gender, $email, $password, $role, $account_status);

        if ($stmt->execute()) {
            $message = "Resident registered successfully!";
            $status = "success";
        } else {
            $message = "Error: Could not save data.";
            $status = "error";
        }
    }

    $stmt->close();
    $conn->close();

    // Store in session to show message in dashboard
    $_SESSION['message'] = $message;
    $_SESSION['status'] = $status;

    header("Location: official_dashboard.php?modal=residentModal");
    exit();
}
?>
