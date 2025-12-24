<?php
// assign_doctor_to_patient.php

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"  => false,
    "message"  => ""
];

try {
    // Expect JSON: { "patient_id": "pat_0001", "doctor_id": "doc_1333" }
    $input = file_get_contents("php://input");
    if ($input === false || $input === "") {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $patient_id = trim($data["patient_id"] ?? "");
    $doctor_id  = trim($data["doctor_id"]  ?? "");

    if ($patient_id === "" || $doctor_id === "") {
        throw new Exception("patient_id and doctor_id are required");
    }

    // Update the patients table
    $stmt = $mysqli->prepare(
        "UPDATE patients
         SET doctor_id = ?
         WHERE patient_id = ?"
    );

    if (!$stmt) {
        throw new Exception("DB error: " . $mysqli->error);
    }

    $stmt->bind_param("ss", $doctor_id, $patient_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        // patient_id not found or already had same doctor_id
        throw new Exception("No patient updated (check patient_id)");
    }

    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Doctor assigned to patient";

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;
