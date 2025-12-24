<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"    => false,
    "message"    => "",
    "treatments" => []
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

    $patient_id = trim($data["patient_id"] ?? "");
    if ($patient_id === "") {
        throw new Exception("patient_id is required");
    }

    // 2. Query DB
    $sql = "SELECT
                medication_name,
                dose,
                route,
                frequency_number,
                frequency_text,
                time_period_weeks,
                created_at
            FROM treatments
            WHERE patient_id = ?
            ORDER BY created_at DESC, id DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            "medication_name"   => $row["medication_name"],
            "dose"              => $row["dose"],
            "route"             => $row["route"],
            "frequency_number"  => $row["frequency_number"],
            "frequency_text"    => $row["frequency_text"],
            "time_period_weeks" => $row["time_period_weeks"],
            "created_at"        => $row["created_at"]
        ];
    }

    $stmt->close();

    $response["success"]    = true;
    $response["treatments"] = $rows;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
