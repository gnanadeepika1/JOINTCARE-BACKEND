<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db_connect.php";

if (!isset($_GET['patient_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "patient_id is required"]);
    exit;
}

$user_id = $_GET['patient_id'];

$sql = "SELECT id, user_id, patient_id, pga, crp, created_at
        FROM save_disease_scores_graph
        WHERE patient_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "count" => count($data),
    "data" => $data
]);

$stmt->close();
$conn->close();

?>