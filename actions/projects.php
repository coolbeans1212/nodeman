<?php
mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

function getProjectInfo(mysqli $mysqli, int $projId): array {
    $sql = "SELECT * FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $projId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

require_once __DIR__ . "/../isAdminAndParseEnv.php";
require_once __DIR__ . '/../functions/eventViewer.php';
$payload = json_decode(file_get_contents("php://input"), true); // php:// >>>>> https://

if ($payload['id'] == -1) { // id of -1 == new proj
    $sql = 'INSERT INTO projects (name, location, main_file) VALUES (?, ?, ?)';
    $stmt = $mysqli->stmt_init();
    if (!$stmt->prepare($sql)) {
        sqlError($mysqli->error);
    }
    $stmt->bind_param('sss', $payload['name'], $payload['location'], $payload['main_file']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    logEvent($mysqli, $_SESSION["user_id"], 'info', 'project-create', sprintf('New project %1$s was created at %2$s', $payload['name'], $payload['location']));
} else {
    if ($payload['delete'] === true) { //gulp,,,,
        $project = getProjectInfo($mysqli, $payload['id']);

        $sql = "UPDATE projects SET current_version = null WHERE id = ?"; // we need to do this or sql will cry about foreign keys
        $stmt = $mysqli->stmt_init();
        if (!$stmt->prepare($sql)) {
            sqlError($mysqli->error);
        }
        $stmt->bind_param('i', $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
        $sql = 'DELETE FROM projects WHERE id = ?';
        $stmt = $mysqli->stmt_init();
        if (!$stmt->prepare($sql)) {
            sqlError($mysqli->error);
        }
        $stmt->bind_param('i', $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
        logEvent($mysqli, $_SESSION["user_id"], 'info', 'project-delete', sprintf('Project %1$s was deleted', $project['name']));
    } else {
        $project = getProjectInfo($mysqli, $payload['id']);
        $sql = 'UPDATE projects SET name = ?, location = ?, main_file = ? WHERE id = ?';
        $stmt = $mysqli->stmt_init();
        if (!$stmt->prepare($sql)) {
            sqlError($mysqli->error);
        }
        $stmt->bind_param('sssi', $payload['name'], $payload['location'], $payload['main_file'], $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
        logEvent($mysqli, $_SESSION["user_id"], 'info', 'project-update', sprintf('Project %1$s was updated. Previous: name = %2$s, location = %3$s, main_file = %4$s. Now: name = %5$s, location = %6$s, main_file = %7$s',
        $payload['name'], $project['name'], $project['location'], $project['main_file'], $payload['name'], $payload['location'], $payload['main_file']));
    }
}
