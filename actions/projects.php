<?php
$env = parse_ini_file(__DIR__ . '/../.env');
if ($env['DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
require_once __DIR__ . '/../db.php';
$payload = json_decode(file_get_contents("php://input"), true); // php:// >>>>> https://

if ($payload['id'] == -1) { // id of -1 == new proj
    $sql = 'INSERT INTO projects (name, location, main_file) VALUES (?, ?, ?)';
    $stmt = $mysqli->stmt_init();
    if (!$stmt->prepare($sql)) {
        header('HTTP 1/1 500 INTERNAL SERVER ERROR');
        die('SQL ERROR: ' . $mysqli->error);
    }
    $stmt->bind_param('sss', $payload['name'], $payload['location'], $payload['main_file']);
    if (!$stmt->execute()) {
        header('HTTP 1/1 500 INTERNAL SERVER ERROR');
        die('SQL ERROR: ' . $mysqli->error);
    }
}
