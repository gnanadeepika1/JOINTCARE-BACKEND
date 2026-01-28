<?php
// db_config.php

$DB_HOST = "localhost";
$DB_USER = "root";        // change if needed
$DB_PASS = "";            // change if needed
$DB_NAME = "jointcare";   // ✅ your database name

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]);
    exit();
}

$mysqli->set_charset("utf8mb4");

?>