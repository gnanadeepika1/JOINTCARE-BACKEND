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

    // 2. Extract fields (exact names from your Java code)
    $patient_id       = trim($data["patient_id"]       ?? "");
    $medication_name  = trim($data["medication_name"]  ?? "");
    $dose             = trim($data["dose"]             ?? "");
    $route            = trim($data["route"]            ?? "");
    $frequency_number = trim($data["frequency_number"] ?? "");
    $frequency_text   = trim($data["frequency_text"]   ?? "");
    $time_weeks       = trim($data["time_period_weeks"] ?? "");

    if ($patient_id === "") {
        throw new Exception("patient_id is required");
    }
    if ($medication_name === "") {
        throw new Exception("medication_name is required");
    }

    // 3. Insert into DB
    $sql = "INSERT INTO treatments (
                patient_id,
                medication_name,
                dose,
                route,
                frequency_number,
                frequency_text,
                time_period_weeks
            ) VALUES (?,?,?,?,?,?,?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param(
        "sssssss",
        $patient_id,
        $medication_name,
        $dose,
        $route,
        $frequency_number,
        $frequency_text,
        $time_weeks
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Treatment added successfully";

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
