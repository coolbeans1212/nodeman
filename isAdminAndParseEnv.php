<?php
$env = parse_ini_file(__DIR__ . '/.env');
if ($env['DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();
$mysqliAccount = require_once __DIR__ . "/db_account.php"; // accounts are stored on a seperate database to NODEMAN on MY machine.
$mysqli = require_once __DIR__ . "/db.php";
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT admin FROM users WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqliAccount->query($sql);
    $user = $result->fetch_assoc();
    if (!$user['admin']) {
        die();
    }
} else {
    die();
}
