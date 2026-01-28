<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success" => false,
    "message" => "",
    "doctor_id" => ""
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

    $doctor_id      = trim($data["doctor_id"] ?? "");
    $full_name      = trim($data["full_name"] ?? "");
    $email          = trim($data["email"] ?? "");
    $phone          = trim($data["phone"] ?? "");
    $specialization = trim($data["specialization"] ?? "");
    $password       = trim($data["password"] ?? "");

    // 🔹 EMPTY CHECK
    if (
        $doctor_id === "" || $full_name === "" || $email === "" ||
        $phone === "" || $specialization === "" || $password === ""
    ) {
        throw new Exception("All fields are required");
    }

    // 🔹 Doctor ID validation
    if (!preg_match("/^doc_\\d{4}$/i", $doctor_id)) {
        throw new Exception("Doctor ID must be like doc_1001");
    }

    // 🔹 Name validation (ONLY letters and spaces)
    if (!preg_match("/^[A-Za-z ]+$/", $full_name)) {
        throw new Exception("Name should contain only letters and spaces");
    }

    // 🔹 Email validation (letters + numbers + @gmail.com)
    if (!preg_match("/^[A-Za-z][A-Za-z0-9]*@gmail\\.com$/", $email)) {
        throw new Exception("Email must be in format abc123@gmail.com");
    }

    // 🔹 Phone validation (exactly 10 digits)
    if (!preg_match("/^\\d{10}$/", $phone)) {
        throw new Exception("Phone number must be exactly 10 digits");
    }

    // 🔹 Phone validation (not all digits same)
    if (preg_match("/(\\d)\\1{9}/", $phone)) {
        throw new Exception("Phone number cannot have all digits same");
    }

    // 🔹 Password validation (same as Android)
    if (!preg_match(
        "/^(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*()_+\\-={}\\[\\]:;\"'<>,.?\\/])[A-Za-z\\d!@#$%^&*()_+\\-={}\\[\\]:;\"'<>,.?\\/]{6,10}$/",
        $password
    )) {
        throw new Exception(
            "Password must be 6–10 chars with 1 capital letter, 1 digit and 1 special character"
        );
    }

    // 🔹 Duplicate check
    $check = $mysqli->prepare(
        "SELECT id FROM doctors WHERE doctor_id = ? OR email = ? LIMIT 1"
    );
    $check->bind_param("ss", $doctor_id, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception("Doctor ID or Email already registered");
    }
    $check->close();

    // 🔹 Insert
    $insert = $mysqli->prepare(
        "INSERT INTO doctors 
        (doctor_id, full_name, email, phone, specialization, password)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $insert->bind_param(
        "ssssss",
        $doctor_id,
        $full_name,
        $email,
        $phone,
        $specialization,
        $password
    );

    if (!$insert->execute()) {
        throw new Exception("Insert failed: " . $insert->error);
    }

    $insert->close();

    $response["success"]   = true;
    $response["message"]   = "Registration successful";
    $response["doctor_id"] = $doctor_id;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
