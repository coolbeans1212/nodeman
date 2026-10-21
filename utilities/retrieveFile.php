<?php
$env = parse_ini_file(__DIR__ . '/../.env'); // no isAdminAndParseEnv.php 😔 because this should be PUBLICLY ACCESSIBLE AAAAA SCARY
$mysqli = require_once __DIR__ . "/../db.php";

$sql = "SELECT key_required, access_key, filename, filesize, mime FROM utility_file_uploads WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $_GET['i']);
$stmt->execute();
$result = $stmt->get_result();
$fileInfo = $result->fetch_assoc();

if (!$fileInfo) {
    http_response_code(404);
    die('File could not be found.');
}

if ($fileInfo['key_required'] && $fileInfo['access_key'] != $_GET['k']) {
    die('Key is required and specified key is incorrect.');
} else {
    header('Content-Type: '. $fileInfo['mime']);
    header('Content-Length: '. $fileInfo['filesize']);
    header('Content-Disposition: filename="' . $fileInfo['filename'] . '"');
    $realFileName = $_GET['i'] . '.' . pathinfo($fileInfo['filename'], PATHINFO_EXTENSION);
    $filePath = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $realFileName;
    readfile($filePath);
}

