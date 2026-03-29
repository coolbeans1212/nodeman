<?php
$env = parse_ini_file(__DIR__ . '/.env');

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = trim($env['DBPASSWORD']);
$db = 'forum';
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  
if ($mysqli->connect_errno) {
    die("Could not connect: " . $mysqli->connecterrno);
}

return $mysqli;
