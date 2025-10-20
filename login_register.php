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
        $_SESSION['register_error'] = "Email already exists.";
        $_SESSION['active_form'] = 'register';
    } else {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, birth_date, gender, email, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $first_name, $last_name, $birth_date, $gender, $email, $password, $role, $account_status);
        $stmt->execute();
    }

    $stmt->close();
    header("Location: login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM tbl_users WHERE email = '$email'");

    // ✅ Check if email exists first
    if ($result->num_rows == 0) {
        $_SESSION['login_error'] = "No account found with that email.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    // ✅ If email exists, fetch user info
    $user = $result->fetch_assoc();

    // ✅ Check if account is still pending
    if ($user['account_status'] === 'pending') {
        $_SESSION['login_error'] = "Your account is still pending approval. Please wait for verification.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    // ✅ Check password
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'official') {
            header("Location: users/admin/official_dashboard.php");
        } else if ($user['role'] === 'Staff') {
            header("Location: users/staff/staff_dashboard.php");
        } else {
            header("Location: users/user/user_dashboard.php");
        }

        exit();
    } else {
        // ✅ Wrong password
        $_SESSION['login_error'] = "Wrong password. Please try again.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }
}

?>
