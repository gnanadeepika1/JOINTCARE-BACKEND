<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"    => false,
    "message"    => "",
    "patient_id" => "",
    "name"       => "",
    "email"      => ""
];

try {
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $patient_id = trim($data["patient_id"] ?? "");
    $password   = trim($data["password"] ?? "");

    if ($patient_id === "" || $password === "") {
        throw new Exception("Patient ID and Password are required");
    }

    if (!preg_match('/^pat_\d{4}$/i', $patient_id)) {
        throw new Exception("Invalid Patient ID format");
    }

    $stmt = $mysqli->prepare(
        "SELECT patient_id, password, name, email
         FROM patients
         WHERE patient_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        throw new Exception("Patient not found");
    }

    $stmt->bind_result(
        $db_patient_id,
        $db_password,
        $db_name,
        $db_email
    );
    $stmt->fetch();
    $stmt->close();

    // ✅ PASSWORD CHECK: SUPPORTS BOTH PLAIN & HASHED
    $loginOk = false;

    // Case 1: plain password
    if ($password === $db_password) {
        $loginOk = true;
    }

    // Case 2: hashed password
    if (!$loginOk && password_verify($password, $db_password)) {
        $loginOk = true;
    }

    if (!$loginOk) {
        throw new Exception("Incorrect password");
    }

    $response["success"]    = true;
    $response["message"]    = "Login successful";
    $response["patient_id"] = $db_patient_id;
    $response["name"]       = $db_name;
    $response["email"]      = $db_email;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
