<?php
require_once __DIR__ . '/../npmdetails.php';
require_once __DIR__ . '/../functions/version.php';
require_once __DIR__ . '/../functions/eventViewer.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);
    $location = getLocationOfCurrentVersion($payload['id']);
    $project = getProjectInformation($mysqli, $payload['id']);
    if ($payload['type'] == 'prune') {
        $cmd = sprintf(
            'cd %s && nohup npm prune > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location)
        );
        exec($cmd);
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.npm.prune', sprintf('Extraneous NPM dependencies for project %s were pruned.', $project['name']));
    }

    if ($payload['type'] == 'installMissing') {
        $cmd = sprintf(
            'cd %s && nohup npm install > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location)
        );
        exec($cmd);
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.npm.install.missing', sprintf('Missing NPM dependencies for project %s were installed.', $project['name']));
    }

    if ($payload['type'] == 'removeDependency') {
        $cmd = sprintf(
            'cd %s && nohup npm uninstall %s > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location),
            escapeshellarg($payload['dependency'])
        );
        exec($cmd);
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.npm.uninstall', sprintf('NPM dependency %1$s for project %2$s was removed.', $payload['dependency'], $project['name']));
    }

    if ($payload['type'] == 'installDependency') {
        $cmd = sprintf(
            'cd %s && nohup npm i %s > /dev/null 2>&1 < /dev/null &',
            escapeshellarg($location),
            escapeshellarg($payload['dependency'])
        );
        exec($cmd);
        logEvent($mysqli, $_SESSION['user_id'], 'info', 'project.npm.install', sprintf('NPM dependency %1$s for project %2$s was installed.', $payload['dependency'], $project['name']));
    }
}
