<?php
require_once __DIR__ . '/../../isAdminAndParseEnv.php';
function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // upload a new file
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
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') { // delete a file
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "SELECT filename FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $filename = $result->fetch_assoc();
    if ($filename) {
        $filename = $filename['filename'];
    } else {
        die('File for deletion could not be found.');
    }
    $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
    $file = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $fileExt;
    if (is_file($file) && !unlink($file)) {
        http_response_code(500);
        die("Could not delete the file.");
    }
    $sql = "DELETE FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $data['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }

    http_response_code(204);
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH') { // update a file's metadata
    $data = json_decode(file_get_contents('php://input'), true);
    var_dump($data);
    $sql = "UPDATE utility_file_uploads SET key_required = ?, access_key = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $data['requireKey'] = (int)$data['requireKey'];
    $stmt->bind_param('iss', $data['requireKey'], $data['key'], $data['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
}
