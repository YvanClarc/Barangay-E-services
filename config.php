<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'barangay_e-services';

mysqli_report(MYSQLI_REPORT_OFF);  // disable auto warnings

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    error_log("DB Connection Failed: " . $conn->connect_error);
    
    // DO NOT echo any HTML/Warning/Plain-text
    // Let the API decide how to respond
    $conn = null;
}
?>
