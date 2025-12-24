<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success" => false,
    "doctors" => []
];

try {
    $sql = "SELECT doctor_id, full_name, specialization FROM doctors ORDER BY created_at DESC";
    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    while ($row = $result->fetch_assoc()) {
        $response["doctors"][] = [
            "doctor_id" => $row["doctor_id"],
            "name" => $row["full_name"],
            "specialization" => $row["specialization"]
        ];
    }

    $response["success"] = true;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
