<?php

function logEvent(mysqli $mysqli, ?int $userId, string $level, string $type, string $details) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $sql = "INSERT INTO events (user_id, level, type, details) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isss', $userId, $level, $type, $details);
    if (!$stmt->execute()) {
        error_log('Could not write to the events table: ' . $stmt->error, 0);
    }
}

function getEvents(mysqli $mysqli, string $currentDatetime, string $orderBy = 'id', int $numberPerPage = 10, int $pageNumber = 0, bool $ascending = false) {
    $allowedColumnValues = ['id', 'user_id', 'level', 'datetime', 'type', 'details'];
    if (!in_array($orderBy, $allowedColumnValues, true)) {
        return -1;
    }
    if ($ascending) {
        $direction = 'ASC';
    } else {
        $direction = 'DESC';
    }
    $sql = "SELECT * FROM events WHERE datetime < ? ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $offset = $pageNumber * $numberPerPage;
    $stmt->bind_param('sii', $currentDatetime, $numberPerPage, $offset);
    if (!$stmt->execute()) {
        error_log('Could not get events: ' . $stmt->error, 0);
    }
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNumberOfEvents(mysqli $mysqli): int {
    $sql = "SELECT COUNT(*) FROM events";
    $result = $mysqli->query($sql);
    return $result->fetch_assoc()["COUNT(*)"];
}

function eventLevelToImage($level): string {
    return match ($level) {
        'info' => '<img src="/files/images/info.png" alt="Info" width="16px">',
        'warning' => '<img src="/files/images/warning.png" alt="Warning" width="16px">',
        'error' => '<img src="/files/images/error.png" alt="Error" width="16px">',
        default => $level,
    };
}

function displayEventsTable(mysqli $mysqli, string $currentDatetime, int $pageNumber = 0, string $orderBy = 'id', int $numberPerPage = 10, bool $ascending = false) {
    require_once __DIR__ . '/account.php';
    $userCache = [];
    $events = getEvents($mysqli, $currentDatetime, $orderBy, $numberPerPage, $pageNumber, $ascending);
    ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <?php
                    $headers = [['ID', 'id'], ['LV', 'level'], ['Datetime', 'datetime'], ['User', 'user_id'], ['Type', 'type'], ['Details', 'details']];
                    foreach ($headers as $header) {
                        ?><th class="event-viewer-header
                        <?php
                        if ($orderBy == $header[1]) {
                            echo ' highlighted indicator';
                        }
                        if ($ascending) {
                            echo ' up';
                        }
                        ?>
                        " data-id="<?php echo $header[1];?>"><?php echo $header[0];?></th><?php
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event) {
                ?><tr>
                <td><?php echo $event['id']; ?></td>
                <td><?php echo eventLevelToImage($event['level']);?></td>
                <td><?php echo $event['datetime'];?></td>
                <td><?php
                if (!isset($userCache[$event['user_id']])) {
                    $userCache[$event['user_id']] = getUserInfoFromId($mysqli, $event['user_id']);
                }
                echo $userCache[$event['user_id']]['username']; // ffs
                ?></td>
                <td><?php echo $event['type'];?></td>
                <td><?php echo $event['details'];?></td></tr><?php
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
    $count = getNumberOfEvents($mysqli);
    ?>
    <sup>Showing <?php echo $pageNumber*$numberPerPage + 1; ?> to <?php echo min($pageNumber*$numberPerPage + $numberPerPage, $count); ?> of <?php echo $count; ?> events.</sup>
    <div class="page-selector-wrapper"><?php
    $numberPagesNeeded = ceil($count / $numberPerPage);
    for ($i = 0; $i < $numberPagesNeeded; $i++) {
        ?><div class="<?php if ($i == $pageNumber) { echo 'selected-'; }?>page-selector" data-type="page-selector-eventviewer" data-id="<?php echo $i;?>" id="selector-eventviewer-<?php echo $i; ?>"><?php echo $i;?></div><?php
    }
    ?></div>
    <?php
}

