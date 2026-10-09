<?php
require_once __DIR__ . '/../npmdetails.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    var_dump($payload);
    $location = getLocationOfCurrentVersion($payload['id']);
    if ($payload['type'] == 'prune') {
        $cmd = sprintf("setsid -w cd %s && npm prune", escapeshellarg($location));
        exec($cmd, $cmdout);
        var_dump($cmdout);
    }
    if ($payload['type'] == 'installMissing') {
        $cmd = sprintf("setsid -w cd %s && npm i", escapeshellarg($location));
        exec($cmd, $cmdout);
        var_dump($cmdout);
    }
}
