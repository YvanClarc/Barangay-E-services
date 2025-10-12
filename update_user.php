<?php
require 'config.php'; // make sure this connects to your database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($id && $status) {
        // Sanitize inputs
        $id = intval($id);
        $status = mysqli_real_escape_string($conn, $status);

        // Update user status
        $query = "UPDATE tbl_users SET account_status = '$status' WHERE id = $id";

        if ($conn->query($query)) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "invalid";
    }
} else {
    echo "forbidden";
}
?>
