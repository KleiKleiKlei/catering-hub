<?php
// Backend configuration file
// Database connection settings

$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'catering_app';

$conn = new mysqli($host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Set charset to utf8
$conn->set_charset("utf8");
?>
