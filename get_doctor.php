<?php
// get_doctor.php

ob_start(); // avoid stray output

error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";   // same as doctor_login.php

$response = [
    "success" => false,
    "message" => "",
    "doctor"  => null
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

    // Query doctor
    $stmt = $mysqli->prepare(
        "SELECT doctor_id, full_name, email, phone, specialization
         FROM doctors
         WHERE doctor_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception("DB error: " . $mysqli->error);
    }

    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception("Doctor not found");
    }

    $stmt->bind_result($db_id, $db_name, $db_email, $db_phone, $db_spec);
    $stmt->fetch();
    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Doctor fetched";
    $response["doctor"] = [
        "doctor_id"      => $db_id,
        "full_name"      => $db_name,
        "email"          => $db_email,
        "phone"          => $db_phone,
        "specialization" => $db_spec
    ];

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;
