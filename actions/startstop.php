<?php
// meow
require_once __DIR__ . "/../isAdminAndParseEnv.php";

function error($reason) {
    http_response_code(500);
    die($reason);
}

require_once __DIR__ . "/../startstopfunctions.php";

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
