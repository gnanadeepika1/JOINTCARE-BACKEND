<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"    => false,
    "message"    => "",
    "patient_id" => ""
];

try {
    // 1. Read JSON body
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    // 2. Extract fields
    $patient_id = trim($data["patient_id"] ?? "");
    $name       = trim($data["name"] ?? "");
    $email      = trim($data["email"] ?? "");
    $age_str    = trim($data["age"] ?? "");
    $sex        = trim($data["sex"] ?? "");
    $occupation = trim($data["occupation"] ?? "");
    $address    = trim($data["address"] ?? "");
    $mobile     = trim($data["mobile"] ?? "");
    $password   = trim($data["password"] ?? "");

    // 3. Validation
    if ($patient_id === "" || $name === "" || $email === "" || $age_str === "" ||
        $sex === "" || $occupation === "" || $address === "" ||
        $mobile === "" || $password === "") {
        throw new Exception("All fields are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if (!ctype_digit($age_str) || intval($age_str) <= 0) {
        throw new Exception("Invalid age");
    }
    $age = intval($age_str);

    if (!preg_match('/^[0-9]{10,}$/', $mobile)) {
        throw new Exception("Invalid mobile number");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters");
    }

    // 4. Check if patient already exists
    $check = $mysqli->prepare(
        "SELECT id FROM patients WHERE patient_id = ? OR email = ? OR mobile = ? LIMIT 1"
    );
    if (!$check) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $check->bind_param("sss", $patient_id, $email, $mobile);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        throw new Exception("Patient ID, Email or Mobile already registered");
    }
    $check->close();

    // 5. Insert patient
    $stmt = $mysqli->prepare(
        "INSERT INTO patients (patient_id, name, email, age, sex, occupation, address, mobile, password)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param(
        "sssisssss",
        $patient_id,
        $name,
        $email,
        $age,
        $sex,
        $occupation,
        $address,
        $mobile,
        $password      // in real apps use password_hash()
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Insert failed: " . $stmt->error);
    }
    $stmt->close();

    // 6. Success
    $response["success"]    = true;
    $response["message"]    = "Patient registered successfully";
    $response["patient_id"] = $patient_id;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
