<?php
// referralhistory_add.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// use your existing DB config (mysqli)
require_once "db_config.php";

// Read JSON body: { "patient_id": "P0001", "message": "..." }
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["patient_id"], $input["message"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "patient_id and message are required"
    ]);
    exit;
}

$patient_id = trim($input["patient_id"]);
$message    = trim($input["message"]);

if ($patient_id === "" || $message === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "patient_id and message cannot be empty"
    ]);
    exit;
}

// use mysqli (same as other files)
$stmt = $mysqli->prepare(
    "INSERT INTO referrals (patient_id, message) VALUES (?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare statement: " . $mysqli->error
    ]);
    exit;
}

$stmt->bind_param("ss", $patient_id, $message);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Referral added successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to insert referral: " . $stmt->error
    ]);
}

$stmt->close();
$mysqli->close();
