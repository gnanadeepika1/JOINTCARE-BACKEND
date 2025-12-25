<?php
header("Content-Type: application/json");

// Database connection
require_once "db_connect.php";

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    !isset($data['user_id']) ||
    !isset($data['pain_value'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

$user_id = trim($data['user_id']);
$pain_value = $data['pain_value'];

// Validate user_id
if ($user_id === "" || strlen($user_id) > 200) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Invalid user_id"
    ]);
    exit;
}

// Validate pain_value (example: 0–10 scale)
if (!is_numeric($pain_value) || $pain_value < 0 || $pain_value > 10) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Invalid pain_value (must be between 0 and 10)"
    ]);
    exit;
}

// Prepare SQL statement
$stmt = $conn->prepare(
    "INSERT INTO daily_pain (user_id, pain_value) VALUES (?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "SQL prepare failed"
    ]);
    exit;
}

$stmt->bind_param("si", $user_id, $pain_value);

// Execute
if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        "status" => true,
        "message" => "Pain record inserted successfully",
        "id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Insert failed"
    ]);
}

$stmt->close();
$conn->close();
?>