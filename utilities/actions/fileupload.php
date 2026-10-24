<?php
require_once __DIR__ . '/../../isAdminAndParseEnv.php';
function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

function dieIfUuidInvalid($id) {
    if (!preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $id)) {
        http_response_code(400);
        die('Invalid UUID.');
    }
}

function getFileExtensionFromId($id, $mysqli) {
    $sql = "SELECT filename FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $filename = $result->fetch_assoc();
    if ($filename) {
        $filename = $filename['filename'];
    } else {
        return -1;
    }
    return pathinfo($filename, PATHINFO_EXTENSION);
}

function checkIdAlreadyExists($id, $mysqli) {
    $sql = "SELECT COUNT(1) FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();
    return $result['COUNT(1)'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // upload a new file
    var_dump($_FILES);
    var_dump(ini_get('upload_max_filesize'), ini_get('post_max_size'));

    if ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE !== UPLOAD_ERR_OK) {
        http_response_code(400);
        die('File was not uploaded OK.');
    }

    if (!is_dir($env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads') && !mkdir($env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads', 0755, true)) {
        http_response_code(500);
        die('Could not create the data directory.');
    }

    dieIfUuidInvalid($_POST['id']);
    if (checkIdAlreadyExists($_POST['id'], $mysqli)) {
        http_response_code(409);
        die('File with ID already exists.');
    }

    $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $_POST['id'] . '.' . $fileExt;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
        http_response_code(500);
        die('Could not save the uploaded file.');
    }

    $requireKey = ($_POST['require_key'] == "true") ? 1 : 0;
    $sql = "INSERT INTO utility_file_uploads (id, key_required, access_key, filename, filesize, mime) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sissis', $_POST['id'], $requireKey, $_POST['key'], $_FILES['file']['name'], $_FILES['file']['size'], $_FILES['file']['type']);
    if (!$stmt->execute()) {
        if (is_file($file)) {
            unlink($file);
        }
        sqlError($mysqli->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') { // delete a file
    $data = json_decode(file_get_contents('php://input'), true);
    dieIfUuidInvalid($data['id']);
    if (!checkIdAlreadyExists($data['id'], $mysqli)) {
        http_response_code(404);
        die('File with ID does not exist.');
    }
    $fileExt = getFileExtensionFromId($data['id'], $mysqli);
    $mysqli->begin_transaction();
    $sql = "DELETE FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $data['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    if ($fileExt == -1) {
        $mysqli->rollback();
        http_response_code(404);
        die('Could not find the file for deletion.');
    }
    $file = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $fileExt;
    if (is_file($file) && !unlink($file)) {
        $mysqli->rollback();
        http_response_code(500);
        die("Could not delete the file.");
    }
    $mysqli->commit();
    http_response_code(204);
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH') { // update a file's metadata
    $data = json_decode(file_get_contents('php://input'), true);
    dieIfUuidInvalid($data['id']);
    if (!checkIdAlreadyExists($data['id'], $mysqli)) {
        http_response_code(404);
        die('File with ID does not exist.');
    }
    $sql = "UPDATE utility_file_uploads SET key_required = ?, access_key = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $data['requireKey'] = (int)$data['requireKey'];
    $stmt->bind_param('iss', $data['requireKey'], $data['key'], $data['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') { // update a file's contents
    [$data, $files] = request_parse_body(); // gatekept to php 8.4 😔
    dieIfUuidInvalid($data['id']);
    if (!checkIdAlreadyExists($data['id'], $mysqli)) {
        http_response_code(404);
        die('File with ID does not exist.');
    }
    $oldFileExt = getFileExtensionFromId($data['id'], $mysqli);
    $newFileExt = pathinfo($files['file']['name'], PATHINFO_EXTENSION);
    if ($oldFileExt == -1) {
        http_response_code(404);
        die('File with ID does not exist.');
    }
    $mysqli->begin_transaction();
    $sql = "UPDATE utility_file_uploads SET filename = ?, filesize = ?, mime = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('siss', $files['file']['name'], $files['file']['size'], $files['file']['type'], $data['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    $file = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $oldFileExt;
    if (is_file($file) && !unlink($file)) {
        $mysqli->rollback();
        http_response_code(500);
        die("Could not delete the original file.");
    }
    if (!move_uploaded_file($files['file']['tmp_name'], $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $newFileExt)) {
        $mysqli->rollback();
        http_response_code(500);
        die("Could not save the updated file.");
    }
    $mysqli->commit();

}
