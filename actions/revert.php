<?php
mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}
require_once __DIR__ . "/../isAdminAndParseEnv.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    $sql = "UPDATE versions SET last_used = NOW() WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $payload['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }

    $sql = "UPDATE projects SET current_version = ? WHERE id = (SELECT project_id FROM versions WHERE id = ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $payload['id'], $payload['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }

    if ($payload['stopandstartthisone']) {
        $sql = "SELECT project_id FROM versions WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
        $result = $stmt->get_result();
        $projectId = $result->fetch_assoc();
        $projectId = $projectId['project_id'];
        require_once __DIR__ . '/../startstopfunctions.php';
        require_once __DIR__ . '/../statuscheck.php';
        stopAndRestartProjectWithId($projectId, $mysqli, $env);
    }
}
