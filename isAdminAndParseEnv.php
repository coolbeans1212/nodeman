<?php
$env = parse_ini_file(__DIR__ . '/.env');
if ($env['DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mysqli = require_once __DIR__ . "/db.php";
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT admin FROM users WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
    if (!$user['admin']) {
        die();
    }
} else {
    header('Location: /account/login.php');
    die();
}
