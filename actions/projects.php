<?php
mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

require_once __DIR__ . "/../isAdminAndParseEnv.php";
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
} else {
    if ($payload['delete'] === true) { //gulp,,,,
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
    } else {
        $sql = 'UPDATE projects SET name = ?, location = ?, main_file = ? WHERE id = ?';
        $stmt = $mysqli->stmt_init();
        if (!$stmt->prepare($sql)) {
            sqlError($mysqli->error);
        }
        $stmt->bind_param('sssi', $payload['name'], $payload['location'], $payload['main_file'], $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
    }
}
