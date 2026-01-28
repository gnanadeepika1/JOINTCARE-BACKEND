<?php
header("Content-Type: application/json");
require_once "db_config.php";

try {
    // Read and decode JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON payload");
    }

    // Required fields
    $requiredFields = [
        "patient_id",
        "name",
        "dose",
        "route",
        "frequency_number",
        "frequency_text",
        "duration"
    ];

    // Check for missing or empty fields
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === "") {
            throw new Exception("Field '{$field}' is required");
        }
    }

    // Optional: type validation
    // if (!is_numeric($data["frequency_number"])) {
    //     throw new Exception("frequency_number must be numeric");
    // }

    // Prepare statement
    $stmt = $mysqli->prepare("
        INSERT INTO treatments
        (patient_id, medication_name, dose, route, frequency_number, frequency_text, time_period_weeks)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssss",
        $data["patient_id"],
        $data["name"],
        $data["dose"],
        $data["route"],
        $data["frequency_number"],
        $data["frequency_text"],
        $data["duration"]
    );

    // Execute
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Treatment added successfully"
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

