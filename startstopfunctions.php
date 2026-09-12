<?php
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
    $cmd = sprintf("setsid %s %s > %s 2>&1 < /dev/null &",
    escapeshellarg($env['NODEBINARYLOCATION']),
    escapeshellarg($location . $main_file),
    escapeshellarg($env['LOGSLOCATION'] . $project . '/' . time() . '.log')); // i just discovered this function it's so cool
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        error("Could not start the node.JS process. Please check the log file (CODE: " . $returnCode . ")");
    }
}
