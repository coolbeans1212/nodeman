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

function isEventsColumnValueAllowed(string $column) {
    $allowedColumnValues = ['id', 'user_id', 'level', 'datetime', 'type', 'details'];
    if (!in_array($column, $allowedColumnValues, true)) {
        return 0;
    } else {
        return 1;
    }
}

function getEvents(mysqli $mysqli, string $currentDatetime, string $orderBy = 'id', int $numberPerPage = 10, int $pageNumber = 0, bool $ascending = false, array $search = [false, '', '']) {
    if (!isEventsColumnValueAllowed($orderBy)) {
        return -1;
    }
    if ($ascending) {
        $direction = 'ASC';
    } else {
        $direction = 'DESC';
    }
    if (!$search[0]) {
        $sql = "SELECT * FROM events WHERE datetime < ? ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        $stmt = $mysqli->prepare($sql);
        $offset = $pageNumber * $numberPerPage;
        $stmt->bind_param('sii', $currentDatetime, $numberPerPage, $offset);
    } else {
        if (!isEventsColumnValueAllowed($search[2])) {
            return -1;
        }
        $searchTerm = '%' . $search[1] . '%';
        $sql = "SELECT * FROM events WHERE datetime < ? AND {$search[2]} LIKE ? ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        $stmt = $mysqli->prepare($sql);
        $offset = $pageNumber * $numberPerPage;
        $stmt->bind_param('ssii', $currentDatetime, $searchTerm, $numberPerPage, $offset);
    }
    if (!$stmt->execute()) {
        error_log('Could not get events: ' . $stmt->error, 0);
    }
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNumberOfEvents(mysqli $mysqli, string $currentDatetime, array $search): int {
    if (!$search[0]) {
        $sql = "SELECT COUNT(*) FROM events WHERE datetime < ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $currentDatetime);
    } else {
        if (!isEventsColumnValueAllowed($search[2])) {
            var_dump($search[2]);
            return -1;
        }
        $searchTerm = '%' . $search[1] . '%';
        $sql = "SELECT COUNT(*) FROM events WHERE datetime < ? AND {$search[2]} LIKE ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss', $currentDatetime, $searchTerm);
    }
    if (!$stmt->execute()) {
        error_log('Could not get events: ' . $stmt->error, 0);
    }
    $result = $stmt->get_result();
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

function displayEventsTable(mysqli $mysqli, string $currentDatetime, int $pageNumber = 0, string $orderBy = 'id', int $numberPerPage = 10, bool $ascending = false, array $search = [false, '', '']) {
    require_once __DIR__ . '/account.php';
    $userCache = [];
    $events = getEvents($mysqli, $currentDatetime, $orderBy, $numberPerPage, $pageNumber, $ascending, $search);
    if ($search[0]) {
        ?><sup>Currently filtering: <?php echo $search[2];?> contains <?php echo $search[1]; ?>. <a id="event-viewer-stop-filtering" href="#">Click here to stop filtering.</a></sup><?php
    }
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
    $count = getNumberOfEvents($mysqli, $currentDatetime, $search);
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

