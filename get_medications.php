<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = ["success" => false, "medications" => [], "message" => ""];

try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) throw new Exception("Invalid JSON");

    $patient_id = strtolower(trim($data["patient_id"] ?? ""));
    if ($patient_id === "") throw new Exception("patient_id required");

    $stmt = $mysqli->prepare(
        "SELECT name, dose, period
         FROM medications
         WHERE patient_id = ?
         ORDER BY created_at DESC"
    );

    if (!$stmt) throw new Exception($mysqli->error);

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();

    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $response["medications"][] = $row;
    }

    $stmt->close();

    $response["success"] = true;

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
