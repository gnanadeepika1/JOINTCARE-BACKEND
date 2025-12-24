<?php
// get_patients.php

ob_start();  // avoid stray output

error_reporting(E_ALL);
ini_set('display_errors', '0');  // don't print warnings in output

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"  => false,
    "message"  => "",
    "patients" => []
];

try {
    // Android sends JSON: { "doctor_id": "doc_1333" }
    $input = file_get_contents("php://input");
    if ($input === false || $input === "") {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $doctor_id = trim($data["doctor_id"] ?? "");
    if ($doctor_id === "") {
        throw new Exception("Doctor ID is required");
    }

    // Now we filter by doctor_id (this column now exists in patients table)
    $stmt = $mysqli->prepare(
        "SELECT patient_id, name, email
         FROM patients
         WHERE doctor_id = ?
         ORDER BY name ASC"
    );

    if (!$stmt) {
        throw new Exception("DB error: " . $mysqli->error);
    }

    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = [
            "patient_id" => $row["patient_id"],
            "name"       => $row["name"],
            "email"      => $row["email"]
        ];
    }

    $stmt->close();

    // Even if $patients is empty, this is still success
    $response["success"]  = true;
    $response["message"]  = "Patients fetched";
    $response["patients"] = $patients;

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;
