<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/../isAdminAndParseEnv.php';

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    if ($payload['type'] == 'personalisation') {
        $sql = 'UPDATE user_preferences SET wallpaper = ?, font = ?, css_version = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sssi', $payload['wallpaper'], $payload['font'], $payload['css'], $_SESSION['user_id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
    }
    if ($payload['type'] == 'layout') {
        $sql = 'UPDATE user_preferences SET versions_per_page = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $payload['versionsperpage'], $_SESSION['user_id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
    }
}
