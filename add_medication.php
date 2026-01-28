<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db_config.php";

$response = ["success" => false, "message" => ""];

try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON");
    }

    $patient_id = strtolower(trim($data["patient_id"] ?? ""));
    $name   = trim($data["name"] ?? "");
    $dose   = trim($data["dose"] ?? "-");
    $period = trim($data["period"] ?? "-");

    if ($patient_id === "" || $name === "") {
        throw new Exception("patient_id and name are required");
    }

    if (!preg_match("/^pat_\d{4}$/", $patient_id)) {
        throw new Exception("Invalid patient_id format");
    }

    $stmt = $mysqli->prepare(
        "INSERT INTO medications (patient_id, name, dose, period)
         VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    $stmt->bind_param("ssss", $patient_id, $name, $dose, $period);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows !== 1) {
        throw new Exception("Insert failed");
    }

    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Inserted";

} catch (Exception $e) {
    http_response_code(400);
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
