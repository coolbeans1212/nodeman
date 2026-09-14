<?php
$env = parse_ini_file(__DIR__ . '/.env');

$dbhost = trim($env['DBADDRESS']);
$dbuser = trim($env['DBUSER']);
$dbpass = trim($env['DBPASSWORD']);
$db = trim($env['ACCOUNTDBNAME']);
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  
if ($mysqli->connect_errno) {
    die("Could not connect: " . $mysqli->connecterrno);
}

return $mysqli;
