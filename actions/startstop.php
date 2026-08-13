<?php
// meow
require_once __DIR__ . "/../isAdminAndParseEnv.php";

function error($reason) {
    http_response_code(500);
    die($reason);
}

function stopProcess($pid, $force = false) {
    if (!is_int($pid) || $pid < 0) {
        error('PID must be an unsigned integer.');
    }
    $processName = [];
    exec("ps -p " . $pid . " -o comm=", $processName);
    if ($processName[0] != 'node') {
        error('Process with PID ' . $pid . ' is not node.');
    }
    if (!$force) {
        $killResult = posix_kill($pid, 15); // SIGTERM (it says it's an undefined constant :( ) (xkcd 541)
    } else {
        $killResult = posix_kill($pid, 9); // SIGKILL
    }
    if (!$killResult) {
        error(posix_strerror(posix_get_last_error()));
    }
}

function startProject($project, $location, $main_file, $env) {
    if (!is_dir($env['LOGSLOCATION'] . $project)) { // we need to make sure that www-data has access to this directory!!!!!
        mkdir($env['LOGSLOCATION'] . $project, 0775, true);
    }
    $cmd = sprintf("setsid %s %s > %s 2>&1",
    escapeshellarg($env['NODEBINARYLOCATION']),
    escapeshellarg($location . $main_file),
    escapeshellarg($env['LOGSLOCATION'] . $project . '/' . time() . '.log')); // i just discovered this function it's so cool
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        error("Could not start the node.JS process. Please check the log file (CODE: " . $returnCode . ")");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    $payload = explode('-', $payload["id"]);
    if ($payload[0] == 'force') {
        $action = 'force-stop';
        unset($payload[0]); unset($payload[1]);
    } else {
        $action = $payload[0];
        unset($payload[0]);
    }
    $projectName = implode("-", $payload);
    include_once __DIR__ . '/../statuscheck.php';
    switch ($action) {
        case "stop":
            $pid = checkStatus($projectName)[1]; //2nd entry of ps aux
            stopProcess((int)$pid);
            break;
        case "force-stop":
            $pid = checkStatus($projectName)[1];
            stopProcess((int)$pid, true);
            break;
        case "start":
            $sql = "SELECT * FROM projects WHERE name = ?"; // name should be UNIQUE.
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('s', $projectName);
            $stmt->execute();
            $result = $stmt->get_result();
            $projectDeets = $result->fetch_assoc();
            startProject($projectName, $projectDeets['location'], $projectDeets['main_file'], $env);
            break;
        default:
            error("No action was provided.");
            break;
    }
}
