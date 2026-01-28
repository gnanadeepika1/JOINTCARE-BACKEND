<?php
header("Content-Type: application/json");

$sdai = floatval($_POST['sdai']);
$das28 = floatval($_POST['das28']);

if ($sdai <= 3.3 && $das28 < 2.6) {
    $activity = "Remission";
    $suggestion = "Continue current medication";
}
elseif ($sdai <= 11 && $das28 <= 3.2) {
    $activity = "Low disease activity";
    $suggestion = "NSAIDs or low-dose DMARD";
}
elseif ($sdai <= 26 && $das28 <= 5.1) {
    $activity = "Moderate disease activity";
    $suggestion = "Modify or escalate DMARD therapy";
}
else {
    $activity = "High disease activity";
    $suggestion = "Consider biologics or specialist referral";
}

echo json_encode([
    "disease_activity" => $activity,
    "ai_suggestion" => $suggestion
]);
?>
