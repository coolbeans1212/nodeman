<?php
require_once __DIR__ . '/../isAdminAndParseEnv.php';
require_once __DIR__ . '/../functions/eventViewer.php';

function compressVersion(int $id) { // returns an array with keys error, error_message, archive (archive is the location of the compressed archive file)
    global $mysqli;
    $sql = "SELECT location FROM projects WHERE id = (SELECT project_id FROM versions WHERE id = ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $location = $result->fetch_assoc()["location"];
    $location = $location . DIRECTORY_SEPARATOR . $id;
    $archivedirectory = "/tmp/NODEMAN/";
    if (!is_dir($archivedirectory) && !mkdir($archivedirectory, 0777, true)) {
        logEvent($mysqli, $_SESSION['user_id'], 'error', 'project.version.download', 'Could not create the temporary directory where the ZIP archive is to be created.');
        return ["error"=>1, "error_message"=>"Could not create the temporary directory where the ZIP archive is to be created."];
    }
    $archivelocation = $archivedirectory . $id . ".zip";
    if (file_exists($archivelocation) && !unlink($archivelocation)) {
        logEvent($mysqli, $_SESSION['user_id'], 'error', 'project.version.download', 'Could not remove already existing ZIP archive.');
        return ["error"=>1, "error_message"=>"Could not remove already existing ZIP archive."];
    }
    $zip = new ZipArchive();
    if ($zip->open($archivelocation, ZIPARCHIVE::CREATE) !== true) {
        logEvent($mysqli, $_SESSION['user_id'], 'error', 'project.version.download', 'Could not open the ZIP archive for writing.');
        return ["error"=>1, "error_message"=>"Could not open the ZIP archive for writing.", "archive"=>""];
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($location, FileSystemIterator::SKIP_DOTS)); // this is so stupid why is there a recursiveiteratoriterator
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $actualPath = $file->getPathname();
            $relativePath = substr($actualPath, strlen($location) + 1);
            $zip->addFile($actualPath, $relativePath);
        }
    }
    $zip->close();
    return ["error"=>0, "archive"=>$archivelocation];
    

}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $archive = compressVersion($_GET['id'])["archive"];
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $_GET['id'] . '.zip"');
    header('Content-Length: ' . filesize($archive));
    header('X-Content-Type-Options: nosniff');
    readfile($archive);
    unlink($archive); // we don't need it anymore :)
    die();
}
