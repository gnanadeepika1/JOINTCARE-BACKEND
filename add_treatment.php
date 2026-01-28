<?php
header("Content-Type: application/json");
require_once "db_config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $mysqli->prepare("
INSERT INTO treatments
(patient_id, name, dose, route, frequency_number, frequency_text, duration)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $data["patient_id"],
    $data["name"],
    $data["dose"],
    $data["route"],
    $data["frequency_number"],
    $data["frequency_text"],
    $data["duration"]
);

$stmt->execute();

echo json_encode(["success" => true]);
