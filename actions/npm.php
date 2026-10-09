<?php
require_once __DIR__ . '/../npmdetails.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    var_dump($payload);
    $location = getLocationOfCurrentVersion($payload['id']);
    if ($payload['type'] == 'prune') {
        $cmd = sprintf(
            'cd %s && nohup npm prune > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location)
        );
        exec($cmd);
    }

    if ($payload['type'] == 'installMissing') {
        $cmd = sprintf(
            'cd %s && nohup npm install > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location)
        );
        exec($cmd);
    }

    if ($payload['type'] == 'removeDependency') {
        $cmd = sprintf(
            'cd %s && nohup npm uninstall %s > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location),
            escapeshellarg($payload['dependency'])
        );
        exec($cmd);
    }
}
