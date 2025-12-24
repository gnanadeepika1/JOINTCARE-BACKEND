<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success" => false,
    "message" => ""
];

try {
    $input = file_get_contents("php://input");
    if ($input === false || $input === "") {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $patient_id  = trim($data["patient_id"]  ?? "");
    $doctor_id   = trim($data["doctor_id"]   ?? "");
    $title       = trim($data["title"]       ?? "");
    $description = trim($data["description"] ?? "");

    if ($patient_id === "" || $title === "") {
        throw new Exception("patient_id and title are required");
    }

    $sql = "INSERT INTO complaints (patient_id, doctor_id, title, description)
            VALUES (?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("DB prepare error: " . $mysqli->error);
    }

    $stmt->bind_param("ssss", $patient_id, $doctor_id, $title, $description);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        throw new Exception("Insert failed");
    }

    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Complaint added";

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;
