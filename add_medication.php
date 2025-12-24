<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success" => false,
    "message" => ""
];

try {
    // 1. Read raw JSON body
    $raw = file_get_contents("php://input");
    if (!$raw) {
        throw new Exception("No JSON body received");
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    // 2. Extract fields
    $patient_id = isset($data['patient_id']) ? trim($data['patient_id']) : '';
    $name       = isset($data['name'])       ? trim($data['name'])       : '';
    $dose       = isset($data['dose'])       ? trim($data['dose'])       : '-';
    $period     = isset($data['period'])     ? trim($data['period'])     : '-';

    if ($patient_id === '' || $name === '') {
        throw new Exception("patient_id and name are required");
    }

    // 3. Insert into DB
    $stmt = $mysqli->prepare(
        "INSERT INTO medications (patient_id, name, dose, period)
         VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssss", $patient_id, $name, $dose, $period);

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $response["success"] = true;
    $response["message"] = "Medication added successfully";

    $stmt->close();

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
