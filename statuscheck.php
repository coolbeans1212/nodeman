<?php

function ago($unixTimestamp) {
    $unixTimeAgo = time() - $unixTimestamp;
    $times = ['year' => 31557600, 'month' => 2629800, 'day' => 86400, 'hour' => 3600, 'minute' => 60, 'second' => 1];
    $timeAgo = [0, 0, 0, 0, 0, 0];
    $i = 0;
    foreach ($times as $unit => $seconds) {
        while (true) {
            if ($unixTimeAgo - $seconds >= 0) {
                $timeAgo[$i]++;
                $unixTimeAgo = $unixTimeAgo - $seconds;
            } else {
                break;
            }
        }
        $i++;
    }
    $timeAgoStr = '';
    $i = 0;
    foreach ($timeAgo as $time) {
        if ($time != 0) {
            $timeAgoStr .= $time . ' ' . array_keys($times)[$i];
            if ($time > 1) {
                $timeAgoStr .= 's'; // plural
            }
            if ($i < count($times) - 1) {
                $timeAgoStr .= ', ';
            }
        }
        $i++;
    }
    return $timeAgoStr;
}

function stateCodes($codeString) {
    $stateCodes = ['D' => 'Uninterruptible sleep',
                   'I' => 'Idle kernel thread',
                   'R' => 'Running or runnable',
                   'S' => 'Interruptible sleep',
                   'T' => 'Stopped by job control signal',
                   't' => 'Stopped by debugger',
                   'W' => 'Paging (kernel ver < 2.6.0)',
                   'X' => 'Dead',
                   'Z' => 'Zombie',
                   '<' => 'High-priority',
                   'N' => 'Low-priority',
                   'L' => 'Has pages locked into memory',
                   's' => 'Session leader',
                   'l' => 'Multi-threaded',
                   '+' => 'In the foreground process group'];
    $statesInCodeString = [];
    foreach ($stateCodes as $code => $readableCode) {
        if (str_contains($codeString, $code)) {  // i updated my server to php 8 now i get to use this function ^_^
            $statesInCodeString[] = $readableCode;
        }
    }
    return $statesInCodeString;
}

function runningFor($timeWithColon) { //converts 33:08 into 33 hours, 8 minutes for example.
    $timeArray = explode(':', $timeWithColon);
    $minutes = str_split($timeArray[1]);
    if ($minutes[0] == '0') {
        unset($minutes[0]);
    }
    $timeArray[1] = implode('', $minutes);
    if ($timeArray[0] == 0) {
        return $timeArray[1] . ' minutes';
    } else {
        return $timeArray[0] . ' hours, ' . $timeArray[1] . ' minutes';
    }

}

function checkStatus($projectName) {
    $output = []; // i love arrays!!!!!!
    exec('/bin/ps aux', $output);
    $process = array_filter($output, function($line) use ($projectName) { //omg its just like JavaScript® this is so meta
        return stripos($line, $projectName) !== false;
    });
    $process = array_values($process);
    $process = end($process);
    $process = preg_split('/\s+/', trim($process), 11); // splits the output by spaces, only eleven times so that it doesn't split the COMMAND field of ps aux. side note: i may or may not be a little bit tipsy.
    $process = array_filter($process, function($string) {
        if (!empty($string)) {
            return true;
        }
    });
    return $process;
}

function statusInfo($projectName) {
    $runStatus = checkStatus($projectName);
    if (empty($runStatus)){
        echo $projectName . ' is <b style="color: red;">DOWN</b>.<br>';
    } else {
        echo $projectName . ' is <b style="color: green;">UP</b>.<br>';
        ?>
            Command line: <?php echo $runStatus[10];?><br>
            Running under user: <?php echo $runStatus[0];?><br>
            CPU Usage: <?php echo $runStatus[2] * 10;?>‰<br> <!-- the client (a.k.a. me) requested this to be in permille instead of percent -->
            RAM Usage: <?php echo $runStatus[3] * 10;?>‰<br>
            Running for: <?php echo runningFor($runStatus[9]);?>
            <div style="display: flex;">
            State: <ul style="display: inline; margin: 0; padding-left: 20px;"><?php foreach (stateCodes($runStatus[7]) as $stateCode) {
                echo '<li>' . $stateCode . '</li>';
            } ?></ul></div><br>
        <?php
    }
}

function logs($projectName, $env) {
    ?><hr><?php
    if (!is_dir($env['LOGSLOCATION'] . $projectName)) {
        echo 'No logs...';
        return -1;
    }
    $scandir = scandir($env['LOGSLOCATION'] . $projectName, SCANDIR_SORT_DESCENDING);
    if (is_file($env['LOGSLOCATION'] . $projectName . '/' . $scandir[0])) {
        echo 'Logs from the start-up ' . ago(pathinfo($scandir[0], PATHINFO_FILENAME)) . ' ago.<br>';
        echo '<textarea readonly style="width: 100%; height: 200px;" class="logs">' . file_get_contents($env['LOGSLOCATION'] . $projectName . '/' . $scandir[0]) . '</textarea>'; // with SCANDIR_SORT_DESCENDING and logs created with the filename of the UNIX timestamp, $scandir[0] will be the latest log
    } else {
        echo 'No logs...';
        return -1;
    }
}

function controls($projectName) {
    ?><hr><?php
    if (empty(checkStatus($projectName))) {
        ?><button class="status-change-button start-project-button" id="start-<?php echo $projectName;?>">START</button>
          <button disabled>STOP</button>
          <button disabled>FORCE STOP</button><?php
    } else {
        ?><button disabled>START</button>
          <button class="status-change-button stop-project-button" id="stop-<?php echo $projectName;?>">STOP</button>
          <button class="status-change-button force-stop-project-button" id="force-stop-<?php echo $projectName;?>">FORCE STOP</button><?php
    }
    ?><?php
}
