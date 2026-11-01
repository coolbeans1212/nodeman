<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/../isAdminAndParseEnv.php';
require_once __DIR__ . '/../functions/eventViewer.php';

function sqlError($mysqli, $error) {
    http_response_code(500);
    logEvent($mysqli, $_SESSION['user_id'], 'error', 'account.controlpanel', sprintf("SQL error while trying to use the control panel: %s.", $error));
    die('SQL ERROR: ' . $error);

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    if ($payload['type'] == 'personalisation') {
        $sql = 'UPDATE user_preferences SET wallpaper = ?, font = ?, css_version = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sssi', $payload['wallpaper'], $payload['font'], $payload['css'], $_SESSION['user_id']);
        if (!$stmt->execute()) {
            sqlError($mysqli, $mysqli->error);
        }
    }
    if ($payload['type'] == 'layout') {
        $sql = 'UPDATE user_preferences SET versions_per_page = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $payload['versionsperpage'], $_SESSION['user_id']);
        if (!$stmt->execute()) {
            sqlError($mysqli, $mysqli->error);
        }
    }
}
