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
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $patient_id = trim($data["patient_id"] ?? "");
    $name       = trim($data["name"] ?? "");
    $email      = trim($data["email"] ?? "");
    $age_str    = trim($data["age"] ?? "");
    $sex        = trim($data["sex"] ?? "");
    $occupation = trim($data["occupation"] ?? "");
    $address    = trim($data["address"] ?? "");
    $mobile     = trim($data["mobile"] ?? "");
    $password   = trim($data["password"] ?? "");

    // 🔹 Empty check
    if (
        $patient_id === "" || $name === "" || $email === "" || $age_str === "" ||
        $sex === "" || $occupation === "" || $address === "" ||
        $mobile === "" || $password === ""
    ) {
        throw new Exception("All fields are required");
    }

    // 🔹 Patient ID (pat_1001)
    if (!preg_match("/^pat_\\d{4}$/i", $patient_id)) {
        throw new Exception("Patient ID must be like pat_1001");
    }

    // 🔹 Name (letters & spaces)
    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        throw new Exception("Name should contain only letters and spaces");
    }

    // 🔹 Email (gmail only)
    if (!preg_match("/^[A-Za-z][A-Za-z0-9]*@gmail\\.com$/", $email)) {
        throw new Exception("Email must be abc123@gmail.com");
    }

    // 🔹 Age (1–120)
    if (!ctype_digit($age_str)) {
        throw new Exception("Invalid age");
    }
    $age = intval($age_str);
    if ($age < 1 || $age > 120) {
        throw new Exception("Age must be between 1 and 120");
    }

    // 🔹 Sex
    if (!in_array(strtolower($sex), ["male", "female", "other"])) {
        throw new Exception("Sex must be Male, Female or Other");
    }

    // 🔹 Occupation
    if (!preg_match("/^[A-Za-z ]+$/", $occupation)) {
        throw new Exception("Occupation should contain only letters");
    }

    // 🔹 Address length
    if (strlen($address) < 5) {
        throw new Exception("Address is too short");
    }

    // 🔹 Mobile (10 digits)
    if (!preg_match("/^\\d{10}$/", $mobile)) {
        throw new Exception("Mobile number must be exactly 10 digits");
    }

    // 🔹 Mobile (not all digits same)
    if (preg_match("/(\\d)\\1{9}/", $mobile)) {
        throw new Exception("Mobile number cannot have all digits same");
    }

    // 🔹 Strong password (same as Android)
    if (!preg_match("/^(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).{6,10}$/", $password)) {
        throw new Exception(
            "Password must be 6–10 chars with capital letter, digit and special character"
        );
    }

    // 🔹 Duplicate check
    $check = $mysqli->prepare(
        "SELECT id FROM patients WHERE patient_id = ? OR email = ? OR mobile = ? LIMIT 1"
    );
    $check->bind_param("sss", $patient_id, $email, $mobile);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception("Patient ID, Email or Mobile already registered");
    }
    $check->close();

    // 🔹 Insert
    $stmt = $mysqli->prepare(
        "INSERT INTO patients 
        (patient_id, name, email, age, sex, occupation, address, mobile, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

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
        $password
    );

    $stmt->execute();
    $stmt->close();

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
