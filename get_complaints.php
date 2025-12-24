<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";   // this must define $mysqli (new mysqli(...))

$response = [
    "success"    => false,
    "message"    => "",
    "complaints" => []
];

try {
    // 1. Read raw JSON
    $input = file_get_contents("php://input");
    if ($input === false || $input === "") {
        throw new Exception("No JSON received");
    }

    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    // 2. Get patient_id from JSON
    $patient_id = trim($data["patient_id"] ?? "");
    if ($patient_id === "") {
        throw new Exception("patient_id is required");
    }

    // 3. Query DB
    $sql = "SELECT title, created_at 
            FROM complaints 
            WHERE patient_id = ?
            ORDER BY created_at DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("DB prepare error: " . $mysqli->error);
    }

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $complaints = [];
    while ($row = $result->fetch_assoc()) {
        $complaints[] = [
            "title"      => $row["title"],
            "created_at" => $row["created_at"]
        ];
    }
    $stmt->close();

    // 4. Build success response
    $response["success"]    = true;
    $response["message"]    = "Complaints fetched";
    $response["complaints"] = $complaints;

} catch (Throwable $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

// 5. Output clean JSON
ob_clean();
echo json_encode($response);
exit;
