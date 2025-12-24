<?php
// Start buffering so that ANY accidental output (warnings, spaces, etc.)
// can be cleared before we send our JSON.
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '0'); // do not print PHP warnings to the client

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"        => false,
    "message"        => "",
    "doctor_id"      => "",
    "full_name"      => "",
    "email"          => "",
    "phone"          => "",
    "specialization" => ""
];

try {
    // Read JSON body
    $input = file_get_contents("php://input");
    if ($input === false || $input === "") {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $doctor_id = trim($data["doctor_id"] ?? "");
    $password  = trim($data["password"]  ?? "");

    if ($doctor_id === "" || $password === "") {
        throw new Exception("Doctor ID and Password are required");
    }

    // Validate doctor_id pattern: doc_1234
    if (!preg_match('/^doc_\d{4}$/i', $doctor_id)) {
        throw new Exception("Invalid Doctor ID format");
    }

    // Check credentials
    $stmt = $mysqli->prepare(
        "SELECT doctor_id, full_name, email, phone, specialization, password
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

    $stmt->bind_result($db_id, $db_name, $db_email, $db_phone, $db_spec, $db_pass);
    $stmt->fetch();

    // Adjust this check if you later use password_hash()
    if ($password !== $db_pass) {
        throw new Exception("Incorrect password");
    }

    $stmt->close();

    // Success response
    $response["success"]        = true;
    $response["message"]        = "Login successful";
    $response["doctor_id"]      = $db_id;
    $response["full_name"]      = $db_name;
    $response["email"]          = $db_email;
    $response["phone"]          = $db_phone;
    $response["specialization"] = $db_spec;

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

// Remove any previous output (warnings, spaces, etc.) and send clean JSON
ob_clean();
echo json_encode($response);
exit;
