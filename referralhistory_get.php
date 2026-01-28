<?php
// referralhistory_get.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db_config.php";

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["patient_id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "patient_id is required"
    ]);
    exit;
}

$patient_id = trim($input["patient_id"]);

if ($patient_id === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "patient_id cannot be empty"
    ]);
    exit;
}

// ✅ NO `id` HERE
$stmt = $mysqli->prepare("
    SELECT patient_id, message, created_at
    FROM referrals
    WHERE patient_id = ?
    ORDER BY created_at DESC
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare statement: " . $mysqli->error
    ]);
    exit;
}

$stmt->bind_param("s", $patient_id);
$stmt->execute();

$result = $stmt->get_result();
$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $rows
]);

$stmt->close();
$mysqli->close();
