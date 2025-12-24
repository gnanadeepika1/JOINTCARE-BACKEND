<?php
header('Content-Type: application/json');

// $mysqli = new mysqli("localhost", "root", "", "jointcare");

// if ($mysqli->connect_errno) {
//     echo json_encode(["error" => "Database connection failed"]);
//     exit;
// }

require_once "db_config.php";

// Base query
$sql = "SELECT id, patient_id, name, email, age, sex, occupation, address, mobile, created_at 
        FROM patients WHERE 1";

// Array to hold the conditions
$conditions = [];
$params = [];
$types = "";

// Add filters if provided
if (!empty($_GET['name'])) {
    $conditions[] = "name LIKE ?";
    $params[] = "%" . $_GET['name'] . "%";
    $types .= "s";
}

if (!empty($_GET['age'])) {
    $conditions[] = "age = ?";
    $params[] = $_GET['age'];
    $types .= "i";
}

if (!empty($_GET['sex'])) {
    $conditions[] = "sex = ?";
    $params[] = $_GET['sex'];
    $types .= "s";
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$stmt = $mysqli->prepare($sql);

// Bind parameters if needed
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$patients = [];

while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

echo json_encode($patients);
?>