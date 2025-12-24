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
    // Read JSON input
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    // Extract values
    $doctor_id      = trim($data["doctor_id"] ?? "");
    $full_name      = trim($data["full_name"] ?? "");
    $email          = trim($data["email"] ?? "");
    $phone          = trim($data["phone"] ?? "");
    $specialization = trim($data["specialization"] ?? "");
    $password       = trim($data["password"] ?? "");

    // Validate fields
    if ($doctor_id === "" || $full_name === "" || $email === "" ||
        $phone === "" || $specialization === "" || $password === "") 
    {
        throw new Exception("All fields are required");
    }

    if (!preg_match("/^doc_\\d{4}$/i", $doctor_id)) {
        throw new Exception("Doctor ID must be like doc_1001");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if (!preg_match("/^\\d+$/", $phone)) {
        throw new Exception("Phone must contain digits only");
    }

    if (strlen($phone) < 10) {
        throw new Exception("Phone must be at least 10 digits");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters");
    }

    // Check existing
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

    // Insert
    $insert = $mysqli->prepare(
        "INSERT INTO doctors (doctor_id, full_name, email, phone, specialization, password)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $insert->bind_param("ssssss",
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

    $response["success"] = true;
    $response["message"] = "Registration successful";
    $response["doctor_id"] = $doctor_id;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
