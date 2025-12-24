<?php
// db_connect.php

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "jointcare";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

?>