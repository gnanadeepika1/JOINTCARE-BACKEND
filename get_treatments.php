<?php
header("Content-Type: application/json");
require_once "db_config.php";

try {
    // Read and decode JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON payload");
    }

    // Validate patient_id
    if (!isset($data["patient_id"]) || trim($data["patient_id"]) === "") {
        throw new Exception("patient_id is required");
    }

    // Prepare statement
    $stmt = $mysqli->prepare("
        SELECT *
        FROM treatments
        WHERE patient_id = ?
        ORDER BY created_at DESC
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    // Bind param
    if (!$stmt->bind_param("s", $data["patient_id"])) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    // Execute
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Fetch results
    $res = $stmt->get_result();
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }

    echo json_encode([
        "success" => true,
        "treatments" => $out
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
