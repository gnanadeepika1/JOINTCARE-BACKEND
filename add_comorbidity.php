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
$doctor_id  = isset($data['doctor_id'])  ? trim($data['doctor_id'])  : '';
$text       = isset($data['text'])       ? trim($data['text'])       : '';
$title      = isset($data['title'])      ? trim($data['title'])      : 'Comorbidity';

if ($patient_id === '' || $doctor_id === '' || $text === '') {
    echo json_encode([
        "success" => false,
        "message" => "patient_id, doctor_id and text are required"
    ]);
    exit;
}

$sql = "INSERT INTO comorbidities (patient_id, doctor_id, title, text)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ssss", $patient_id, $doctor_id, $title, $text);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Comorbidity added successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
