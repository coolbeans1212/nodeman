<?php
$env = parse_ini_file(__DIR__ . '/.env');
if ($env['DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
require_once __DIR__ . '/../db.php';
$payload = json_decode(file_get_contents("php://input")); // php:// >>>>> https://
var_dump($payload);