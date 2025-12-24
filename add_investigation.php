<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "db_config.php";

$response = [
    "success" => false,
    "message" => ""
];

try {
    // 1. Read raw JSON body
    $raw = file_get_contents("php://input");
    if (!$raw) {
        throw new Exception("No JSON body received");
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new Exception("Invalid JSON format");
    }

    // 2. Extract fields (same keys as in your Java)
    $patient_id       = trim($data["patient_id"]            ?? "");
    $hb               = trim($data["hb"]                    ?? "");
    $total_leukocyte  = trim($data["total_leukocyte"]       ?? "");
    $differential_cnt = trim($data["differential_count"]    ?? "");
    $platelet_count   = trim($data["platelet_count"]        ?? "");
    $esr              = trim($data["esr"]                   ?? "");
    $crp              = trim($data["crp"]                   ?? "");
    $lft_total        = trim($data["lft_total_bilirubin"]   ?? "");
    $lft_direct       = trim($data["lft_direct_bilirubin"]  ?? "");
    $ast              = trim($data["ast"]                   ?? "");
    $alt              = trim($data["alt"]                   ?? "");
    $albumin          = trim($data["albumin"]               ?? "");
    $total_protein    = trim($data["total_protein"]         ?? "");
    $ggt              = trim($data["ggt"]                   ?? "");
    $urea             = trim($data["urea"]                  ?? "");
    $creatinine       = trim($data["creatinine"]            ?? "");
    $uric_acid        = trim($data["uric_acid"]             ?? "");
    $urine_routine    = trim($data["urine_routine"]         ?? "");
    $urine_pcr        = trim($data["urine_pcr"]             ?? "");
    $ra_factor        = trim($data["ra_factor"]             ?? "");
    $anti_ccp         = trim($data["anti_ccp"]              ?? "");

    if ($patient_id === "") {
        throw new Exception("patient_id is required");
    }

    // Optionally enforce at least one non-empty test (Java already does this)
    if (
        $hb === "" && $total_leukocyte === "" && $differential_cnt === "" &&
        $platelet_count === "" && $esr === "" && $crp === "" &&
        $lft_total === "" && $lft_direct === "" &&
        $ast === "" && $alt === "" && $albumin === "" && $total_protein === "" && $ggt === "" &&
        $urea === "" && $creatinine === "" && $uric_acid === "" &&
        $urine_routine === "" && $urine_pcr === "" &&
        $ra_factor === "" && $anti_ccp === ""
    ) {
        throw new Exception("Please fill at least one investigation field");
    }

    // 3. Insert into DB
    $sql = "INSERT INTO investigations (
                patient_id,
                hb, total_leukocyte, differential_count, platelet_count,
                esr, crp,
                lft_total_bilirubin, lft_direct_bilirubin,
                ast, alt, albumin, total_protein, ggt,
                urea, creatinine, uric_acid,
                urine_routine, urine_pcr,
                ra_factor, anti_ccp
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param(
        "sssssssssssssssssssss",
        $patient_id,
        $hb, $total_leukocyte, $differential_cnt, $platelet_count,
        $esr, $crp,
        $lft_total, $lft_direct,
        $ast, $alt, $albumin, $total_protein, $ggt,
        $urea, $creatinine, $uric_acid,
        $urine_routine, $urine_pcr,
        $ra_factor, $anti_ccp
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Investigation added successfully";

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
