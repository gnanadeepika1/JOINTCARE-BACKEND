<?php
header("Content-Type: application/json");

// Database connection
require_once "db_connect.php";

// Allow only GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// Validate user_id
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit;
}

$user_id = trim($_GET['user_id']);

if ($user_id === "" || strlen($user_id) > 200) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Invalid user_id"
    ]);
    exit;
}

// Prepare SQL
$stmt = $conn->prepare(
    "SELECT id, pain_value, record_date 
     FROM daily_pain 
     WHERE user_id = ? 
     ORDER BY record_date DESC"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "SQL prepare failed"
    ]);
    exit;
}

$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "message" => "No pain records found"
    ]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

http_response_code(200);
echo json_encode([
    "status" => true,
    "user_id" => $user_id,
    "count" => count($data),
    "data" => $data
]);

$stmt->close();
$conn->close();
?>