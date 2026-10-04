<?php
mysqli_report(MYSQLI_REPORT_OFF); // revert to pre-php 8.1 behaviour :)

function sqlError($error) {
    http_response_code(500);
    die('SQL ERROR: ' . $error);
}

function moreHelpfulMkdirError($error) {
    $error = match ($error) {
        'mkdir(): File exists' => $error . '. Make sure that there are no other versions for this project with the same name and changelog.',
        'mkdir(): Permission denied' => $error . '. Make sure that ' . posix_getpwuid(posix_geteuid())['name'] . ' owns ' . $location . ' and has full permissions.',
        default => $error,
    };
    return $error;
}

require_once __DIR__ . "/../isAdminAndParseEnv.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // pop the new deets in
    $sql = "INSERT INTO versions (name, changelog, project_id) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssi', $_POST['ver_name'], $_POST['changelog'], $_POST['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }

    // we need to save the files now... get where we want the files to be saved :3
    $sql = "SELECT location FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $_POST['id']);
    if (!$stmt->execute()) {
        sqlError($mysqli->error);
    }
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();
    $location = $location['location']; // location location location ah (https://en.wikipedia.org/wiki/Location%2C_Location%2C_Location)

    $sql = "SELECT id FROM versions WHERE name = ? AND changelog = ? AND project_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssi', $_POST['ver_name'], $_POST['changelog'], $_POST['id']);
    if (!$stmt->execute()) {
        sqlError($stmt->error);
    }
    $result = $stmt->get_result();
    $locationid = $result->fetch_assoc();
    $locationid = $locationid['id'];

    $fulllocation = $location . DIRECTORY_SEPARATOR . $locationid;
    
    // now we have location. we must SAVE to the location.
    if (!mkdir($fulllocation)) {
        $error = moreHelpfulMkdirError(error_get_last()["message"]);
        http_response_code(500);
        die($error);
    }
    // NOW we have to put the files INSIDE the folders AAAAAAA so cool!!!!!!
    foreach($_FILES['files']['full_path'] as $key => $path) {
        if (dirname($path) != '' && dirname($path) != '.') { // we need to create another directory inside the one we just created
            $dir = dirname($fulllocation . DIRECTORY_SEPARATOR . $path);
            if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
                $error = moreHelpfulMkdirError(error_get_last()["message"]);
                http_response_code(500);
                die($error);
            }
        } else {
            $dir = $fulllocation . DIRECTORY_SEPARATOR;
        }
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $filename = $_FILES['files']['name'][$key];
        if (!move_uploaded_file($tmpName, $dir . $filename)) {
            http_response_code(500);
            die('Could not move the uploaded file into the specified directory');
        }
    }

    // ok NOW we need to set it to the current version in le database
    if ($_POST['stop_and_restart'] === 'true' || $_POST['set_to_current_version'] === 'true') {
        $sql = 'UPDATE projects SET current_version = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $locationid, $_POST['id']); // reusing $locationid from the bit where we get the directory to save the files to
        if (!$stmt->execute()) {
            sqlError($mysqli->error);
        }
    }

    // and NOW we need to start this version
    if ($_POST['stop_and_restart'] == 'true') {
        require_once __DIR__ . "/../startstopfunctions.php";
        stopAndRestartProjectWithId($_POST['id'], $mysqli, $env);
    }

    
}

