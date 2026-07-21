<?php
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

function runningFor($timeWithColon) { //converts 33:58 into 33 hours, 58 minutes for example.
    $timeArray = explode(':', $timeWithColon);
    return $timeArray[0] . ' hours, ' . $timeArray[1] . ' minutes';
}

function checkStatus($projectName) {
    $output = []; // i love arrays!!!!!!
    exec('/bin/ps aux', $output);
    $process = array_filter($output, function($line) use ($projectName) { //omg its just like JavaScript® this is so meta
        return stripos($line, $projectName) !== false;
    });
    $process = array_values($process);
    return $process;
}

function statusInfo($projectName) {
    $runStatus = checkStatus($projectName);
    $runStatus = end($runStatus);
    $runStatus = preg_split('/\s+/', trim($runStatus), 11); // splits the output by spaces, only eleven times so that it doesn't split the COMMAND field of ps aux. side note: i may or may not be a little bit tipsy.
    $runStatus = array_filter($runStatus, function($string) {
        if (!empty($string)) {
            return true;
        }
    });
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
    echo '<hr>';
}
