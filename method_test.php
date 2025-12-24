<?php
header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "method" => $_SERVER["REQUEST_METHOD"] ?? "UNKNOWN",
    "message" => "method_test.php reached"
]);
