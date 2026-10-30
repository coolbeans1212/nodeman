<?php
require_once __DIR__ . '/../functions/eventViewer.php';

mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

function getVersionInformation(mysqli $mysqli, int $id): array {
    $sql = "SELECT id,name,upload_time,changelog,project_id,last_used FROM versions WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProjectInformation(mysqli $mysqli, int $id): array {
    $sql = "SELECT id,name,location,main_file,current_version FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

require_once __DIR__ . "/../isAdminAndParseEnv.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    $version = getVersionInformation($mysqli, $payload['id']);
    $projectBefore = getProjectInformation($mysqli, $version['project_id']);
    $versionBefore = getVersionInformation($mysqli, $projectBefore['current_version']);

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
    logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.version.revert', sprintf('Project %1$s was reverted from version %2$s to version %3$s.', $projectBefore['name'], $versionBefore['name'], $version['name']));

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
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.version.revert.start', sprintf('Version %1$s for project %2$s was started after a revert.', $version['name'], $projectBefore['name']));
    }
}
