<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db_connect.php";

if (!isset($_GET['patient_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "patient_id is required"]);
    exit;
}

$patient_id = $_GET['patient_id'];

$sql = "SELECT id, patient_id, tjc, sjc, pga, ea, crp, created_at
        FROM save_disease_scores_graph
        WHERE patient_id = ?
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    $tjc = (float)$row['tjc'];
    $sjc = (float)$row['sjc'];
    $pga = (float)$row['pga'];
    $ea  = (float)$row['ea'];
    $crp = (float)$row['crp'];

    // ✅ SDAI
    $sdai = $tjc + $sjc + $pga + $ea + $crp;
    $row['sdai'] = round($sdai, 2);

    // ✅ DAS28-CRP
    $das28 =
        (0.56 * sqrt($tjc)) +
        (0.28 * sqrt($sjc)) +
        (0.36 * log($crp + 1)) +
        (0.014 * $pga) +
        0.96;

    $row['das28_crp'] = round($das28, 2);

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