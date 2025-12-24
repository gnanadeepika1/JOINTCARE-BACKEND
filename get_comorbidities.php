<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON body"
    ]);
    exit;
}

$patient_id = isset($data['patient_id']) ? trim($data['patient_id']) : '';

if ($patient_id === '') {
    echo json_encode([
        "success" => false,
        "message" => "patient_id is required"
    ]);
    exit;
}

$sql = "SELECT title, text
        FROM comorbidities
        WHERE patient_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$comorbidities = [];
while ($row = $result->fetch_assoc()) {
    $comorbidities[] = [
        "title" => $row['title'],
        "text"  => $row['text']
    ];
}

echo json_encode([
    "success"       => true,
    "comorbidities" => $comorbidities
]);

$stmt->close();
$conn->close();
