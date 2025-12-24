<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php';

/**
 * Allow only POST
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

/**
 * Get JSON input
 */
$input = json_decode(file_get_contents("php://input"), true);

/**
 * Validate required fields
 */
$requiredFields = ['user_id', 'patient_id', 'pga', 'crp'];

foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400); // Bad Request
        echo json_encode([
            "success" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

/**
 * Sanitize & assign
 */
$user_id    = (int) $input['user_id'];
$patient_id = trim($input['patient_id']);
$pga        = (float) $input['pga'];
$crp        = (float) $input['crp'];

/**
 * Insert using prepared statement
 */
$sql = "INSERT INTO save_disease_scores_graph (user_id, patient_id, pga, crp)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed"
    ]);
    exit;
}

$stmt->bind_param("isdd", $user_id, $patient_id, $pga, $crp);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode([
        "success" => true,
        "message" => "Data inserted successfully",
        "insert_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Insert failed"
    ]);
}

$stmt->close();
$conn->close();
