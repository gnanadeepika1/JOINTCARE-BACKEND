<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success"        => false,
    "message"        => "",
    "investigations" => []
];

try {
    // 1. Read JSON body
    $raw = file_get_contents("php://input");
    if (!$raw) {
        throw new Exception("No JSON body received");
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    $patient_id = trim($data["patient_id"] ?? "");
    if ($patient_id === "") {
        throw new Exception("patient_id is required");
    }

    // 2. Query DB
    $sql = "SELECT
                hb, total_leukocyte, differential_count, platelet_count,
                esr, crp,
                lft_total_bilirubin, lft_direct_bilirubin,
                ast, alt, albumin, total_protein, ggt,
                urea, creatinine, uric_acid,
                urine_routine, urine_pcr,
                ra_factor, anti_ccp,
                created_at
            FROM investigations
            WHERE patient_id = ?
            ORDER BY created_at DESC, id DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        // return all fields exactly with Java's expected keys
        $rows[] = [
            "hb"                   => $row["hb"],
            "total_leukocyte"      => $row["total_leukocyte"],
            "differential_count"   => $row["differential_count"],
            "platelet_count"       => $row["platelet_count"],
            "esr"                  => $row["esr"],
            "crp"                  => $row["crp"],
            "lft_total_bilirubin"  => $row["lft_total_bilirubin"],
            "lft_direct_bilirubin" => $row["lft_direct_bilirubin"],
            "ast"                  => $row["ast"],
            "alt"                  => $row["alt"],
            "albumin"              => $row["albumin"],
            "total_protein"        => $row["total_protein"],
            "ggt"                  => $row["ggt"],
            "urea"                 => $row["urea"],
            "creatinine"           => $row["creatinine"],
            "uric_acid"            => $row["uric_acid"],
            "urine_routine"        => $row["urine_routine"],
            "urine_pcr"            => $row["urine_pcr"],
            "ra_factor"            => $row["ra_factor"],
            "anti_ccp"             => $row["anti_ccp"],
            "created_at"           => $row["created_at"]
        ];
    }

    $stmt->close();

    $response["success"]        = true;
    $response["investigations"] = $rows;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
