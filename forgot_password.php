<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email       = trim($data['email'] ?? '');
$newPassword = trim($data['new_password'] ?? '');
$userType    = trim($data['user_type'] ?? '');

if ($email === '' || $newPassword === '' || $userType === '') {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

/* 🔒 User type mapping */
if ($userType === "doctor") {
    $table = "doctors";
    $idCol = "doctor_id";
} elseif ($userType === "patient") {
    $table = "patients";
    $idCol = "patient_id";
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user type"
    ]);
    exit;
}

/* ✅ Check if email exists */
$check = $mysqli->prepare("SELECT $idCol FROM $table WHERE email = ?");
if (!$check) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    exit;
}

$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email not found"
    ]);
    exit;
}
$check->close();

/* ✅ Update password (PLAIN TEXT – consistent with login) */
$update = $mysqli->prepare(
    "UPDATE $table SET password = ? WHERE email = ?"
);

$update->bind_param("ss", $newPassword, $email);

if ($update->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Password updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update password"
    ]);
}

$update->close();
exit;
?>
