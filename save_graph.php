<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php';

/* Allow only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

/* Try JSON input first */
$input = json_decode(file_get_contents("php://input"), true);

/* 🔥 FALLBACK TO FORM POST (CRITICAL FIX) */
if (empty($input)) {
    $input = $_POST;
}

/* Required fields */
$requiredFields = ['patient_id', 'tjc', 'sjc', 'pga', 'ea', 'crp'];

foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

/* Assign */
$patient_id = trim($input['patient_id']);
$tjc = (int)$input['tjc'];
$sjc = (int)$input['sjc'];
$pga = (float)$input['pga'];
$ea  = (float)$input['ea'];
$crp = (float)$input['crp'];

/* Insert */
$sql = "INSERT INTO save_disease_scores_graph
        (patient_id, tjc, sjc, pga, ea, crp, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed"
    ]);
    exit;
}

$stmt->bind_param(
    "siiddd",
    $patient_id,
    $tjc,
    $sjc,
    $pga,
    $ea,
    $crp
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Disease score saved successfully",
        "insert_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Insert failed",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
