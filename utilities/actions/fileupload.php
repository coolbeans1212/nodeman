<?php
require_once __DIR__ . '/../../isAdminAndParseEnv.php';
function sqlError(string $error): void {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

function dieIfUuidInvalid(string $id): void {
    if (!preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $id)) {
        http_response_code(400);
        die('Invalid UUID.');
    }
}

function getFileExtensionFromId(string $id, mysqli $mysqli): string {
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

function checkIdAlreadyExists(string $id, mysqli $mysqli): bool {
    $sql = "SELECT COUNT(1) FROM utility_file_uploads WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();
    return (bool)$result['COUNT(1)'];
}

function uploadErrorMessage(int $code): string {
    return match ($code) {
        UPLOAD_ERR_OK => 'No error.',
        UPLOAD_ERR_INI_SIZE => 'Uploaded file exceeded the server file size limit. Ask the administrator to increase upload_max_filesize in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeded the maximum file size allowed.',
        UPLOAD_ERR_PARTIAL => 'Uploaded file could not finish uploading.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded or uploaded data exceeded the server data size limit. If you are 1000‰ sure that a file was uploaded, ask the administrator to increase post_max_size in php.ini.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server error: no temporary directory to store uploads exists.',
        UPLOAD_ERR_CANT_WRITE => 'Server error: could not write the uploaded file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension prevented the upload.',
        default => 'Upload error code ' . $code . '.'
    };
}

function uploadErrorHttpStatusCode(int $code): int {
    return match ($code) {
        UPLOAD_ERR_OK => 200,
        UPLOAD_ERR_INI_SIZE => 413,
        UPLOAD_ERR_FORM_SIZE => 413,
        UPLOAD_ERR_PARTIAL => 400,
        UPLOAD_ERR_NO_FILE => 400,
        UPLOAD_ERR_NO_TMP_DIR => 500,
        UPLOAD_ERR_CANT_WRITE => 500,
        UPLOAD_ERR_EXTENSION => 500,
        default => 400
    };
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // upload a new file
    $fileError = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($fileError !== UPLOAD_ERR_OK) {
        http_response_code(uploadErrorHttpStatusCode($fileError));
        die(uploadErrorMessage($fileError));
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
    try {
        [$data, $files] = request_parse_body(); // gatekept to php 8.4 😔
    } catch (RequestParseBodyException $e) {
        http_response_code(413);
        die('Uploaded data exceeded the server data size limit. Ask the administrator to increase post_max_size in php.ini.');
    }
    
    dieIfUuidInvalid($data['id']);
    if (!checkIdAlreadyExists($data['id'], $mysqli)) {
        http_response_code(404);
        die('File with ID does not exist.');
    }
    $fileError = $files['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($fileError !== UPLOAD_ERR_OK) {
        http_response_code(uploadErrorHttpStatusCode($fileError));
        die(uploadErrorMessage($fileError));
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
    $tempfile = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp-' . rand();
    if (!move_uploaded_file($files['file']['tmp_name'], $tempfile)) {
        $mysqli->rollback();
        http_response_code(500);
        die("Could not save the updated file.");
    }
    $oldFile = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $oldFileExt;
    if (is_file($oldFile) && !unlink($oldFile)) {
        $mysqli->rollback();
        http_response_code(500);
        die("Could not delete the original file.");
    }
    $newFile = $env['MISCDATADIR'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $data['id'] . '.' . $newFileExt;
    rename($tempfile, $newFile);
    $mysqli->commit();

}
