<?php
session_start();
require_once 'config.php';

// FUNCTION to normalize roles
function normalizeRole($role) {
    return strtolower(trim($role));
}

/* ===========================
   USER REGISTRATION
=========================== */
if (isset($_POST['register'])) {

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = normalizeRole($_POST['role']);
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
        $stmt = $conn->prepare(
            "INSERT INTO tbl_users (first_name, last_name, birth_date, gender, email, password, role, account_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssssss", $first_name, $last_name, $birth_date, $gender, $email, $password, $role, $account_status);
        $stmt->execute();
    }

    $stmt->close();
    header("Location: login.php");
    exit();
}


/* ===========================
   USER LOGIN
=========================== */
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✔ FIX SQL Injection
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['login_error'] = "No account found with that email.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    $user = $result->fetch_assoc();

    // pending account
    if ($user['account_status'] === 'pending') {
        $_SESSION['login_error'] = "Your account is still pending approval.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = "Wrong password.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    // ✔ FIX: Use consistent session key
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'];
    $_SESSION['role'] = normalizeRole($user['role']); // Already using 'role'

    // ✔ FIX role routing
    switch ($_SESSION['role']) {
        case 'admin':
        case 'official':  // same dashboard
            header("Location: users/admin/official_dashboard.php");
            break;

        case 'staff':
            header("Location: users/staff/staff_dashboard.php");
            break;

        default:
            header("Location: users/user/user_dashboard.php");
            break;
    }

    exit();
}

?>
