<?php
header("Content-Type: application/json");
require_once "db_config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $mysqli->prepare("
SELECT name, dose, route, frequency_number, frequency_text, duration
FROM treatments
WHERE patient_id = ?
ORDER BY created_at DESC
");

$stmt->bind_param("s", $data["patient_id"]);
$stmt->execute();

$res = $stmt->get_result();
$out = [];

while ($row = $res->fetch_assoc()) {
    $out[] = $row;
}

echo json_encode(["treatments" => $out]);
