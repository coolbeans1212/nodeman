<?php
require_once __DIR__ . '/../../isAdminAndParseEnv.php';
if (!is_dir($env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads') && !mkdir($env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads', 0777, true)) {
    http_response_code(500);
    die('Could not create the data directory.');
}

if (!preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $_POST['id'])) {
    http_response_code(400);
    die('Invalid UUID.');
}

$fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
move_uploaded_file($_FILES['file']['tmp_name'], $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $_POST['id'] . '.' . $fileExt);

$requireKey = ($_POST['require_key'] == "true") ? 1 : 0;
$sql = "INSERT INTO utility_file_uploads (id, key_required, access_key, filename, filesize, mime) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('sissis', $_POST['id'], $requireKey, $_POST['key'], $_FILES['file']['name'], $_FILES['file']['size'], $_FILES['file']['type']);
if (!$stmt->execute()) {
    sqlError($mysqli->error);
}

