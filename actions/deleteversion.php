<?php
mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}
require_once __DIR__ . "/../isAdminAndParseEnv.php";
require_once __DIR__ . '/../functions/eventViewer.php';
require_once __DIR__ . '/../functions/version.php';

function rmdirRecursive($src) { // thanks itay at itgoldman dot com
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rmdirRecursive($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    $version = getVersionInformation($mysqli, $payload['id']);
    $project = getProjectInformation($mysqli, $version['project_id']);
    $sql = "SELECT current_version FROM projects WHERE id = (SELECT project_id FROM versions WHERE id = ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $payload['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    $result = $stmt->get_result();
    $currentversion = $result->fetch_assoc();
    $currentversion = $currentversion['current_version'];
    if ($currentversion == $payload['id']) {
        http_response_code(409);
        die("This version is in use. Please revert to another version before deleting this one.");
    }
    if ($payload['deleteallfiles']) {
        $sql = "SELECT location FROM projects WHERE id = (SELECT project_id FROM versions WHERE id = ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $payload['id']);
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
        $result = $stmt->get_result();
        $location = $result->fetch_assoc();
        $location = $location['location'];
        $dir = $location . DIRECTORY_SEPARATOR . $payload['id']; // surely nothing can go wrong with this (foreshadowing)
        rmdirRecursive($dir);
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.version.files.delete', sprintf('The files from version %1$s of project %2$s was deleted.', $version['name'], $project['name']));
    }
    $sql = "DELETE FROM versions WHERE id = ?"; // 😔 goodbye version
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $payload['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.version.delete', sprintf('Version %1$s of project %2$s was deleted.', $version['name'], $project['name']));

}
