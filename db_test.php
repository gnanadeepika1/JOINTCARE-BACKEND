<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db_config.php";

echo json_encode([
    "success" => true,
    "message" => "DB connected OK and POST allowed"
]);
