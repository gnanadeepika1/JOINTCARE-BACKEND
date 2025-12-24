<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"     => false,
    "message"     => "",
    "medications" => []
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

    // 2. Extract patient_id
    $patient_id = isset($data['patient_id']) ? trim($data['patient_id']) : '';
    if ($patient_id === '') {
        throw new Exception("patient_id is required");
    }

    // 3. Fetch medications
    $stmt = $mysqli->prepare(
        "SELECT name, dose, period
           FROM medications
          WHERE patient_id = ?
       ORDER BY created_at DESC, id DESC"
    );

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $meds = [];
    while ($row = $result->fetch_assoc()) {
        $meds[] = [
            "name"   => $row["name"],
            "dose"   => $row["dose"],
            "period" => $row["period"]
        ];
    }

    $stmt->close();

    $response["success"]     = true;
    $response["medications"] = $meds;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
