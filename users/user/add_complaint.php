<?php
session_start();
require_once '../../config.php';

// ✅ Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // 🧩 Sanitize form inputs
    $complaint_type = trim($_POST['complaint_type']);
    $complaint_details = trim($_POST['complaint_details']);
    $complaint_date = trim($_POST['complaint_date']);
    $complaint_location = trim($_POST['complaint_location']);
    $status = "Pending";

    // ✅ Optional: generate a reference number for tracking
    $reference_no = 'CMP-' . strtoupper(uniqid());

    // 🧠 Validate inputs
    if (empty($complaint_type) || empty($complaint_details) || empty($complaint_date) || empty($complaint_location)) {
        echo "<script>
                alert('Please fill in all required fields.');
                window.location.href = 'user_dashboard.php';
              </script>";
        exit();
    }

    // 💾 Insert into database
    $stmt = $conn->prepare("INSERT INTO tbl_complaints (user_id, reference_no, complaint_type, details, date_of_incident, location, status, date_filed)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssss", $user_id, $reference_no, $complaint_type, $complaint_details, $complaint_date, $complaint_location, $status);

    if ($stmt->execute()) {
        echo "<script>
                alert('Complaint filed successfully!');
                window.location.href = 'user_dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Error filing complaint. Please try again.');
                window.location.href = 'user_dashboard.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // 🚫 If accessed directly, go back to dashboard
    header("Location: user_dashboard.php");
    exit();
}
?>
