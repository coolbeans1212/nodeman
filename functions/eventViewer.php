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

function getEvents(mysqli $mysqli, string $currentDatetime, string $orderBy = 'id', int $numberPerPage = 10, int $pageNumber = 0) {
    $allowedColumnValues = ['id', 'user_id', 'level', 'datetime', 'type', 'details'];
    if (!in_array($orderBy, $allowedColumnValues, true)) {
        return -1;
    }
    $sql = "SELECT * FROM events WHERE datetime < ? ORDER BY {$orderBy} DESC LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $offset = $pageNumber * $numberPerPage;
    $stmt->bind_param('sii', $currentDatetime, $numberPerPage, $offset);
    if (!$stmt->execute()) {
        error_log('Could not get events: ' . $stmt->error, 0);
    }
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function displayEventsTable(mysqli $mysqli, string $currentDatetime, string $orderBy = 'id', int $numberPerPage = 10, int $pageNumber = 0) {
    require_once __DIR__ . '/account.php';
    $userCache = [];
    $events = getEvents($mysqli, $currentDatetime, $orderBy, $numberPerPage, $pageNumber);
    ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>LV</th>
                    <th>Datetime</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event) {
                ?><tr><td><?php
                if ($event['level'] == 'info') {
                    ?><img src="/files/images/info.png" alt="Info" width="16px"><?php
                } elseif ($event['level'] == 'warning') {
                    ?><img src="/files/images/warning.png" alt="Warning" width="16px"><?php
                } elseif ($event['level'] == 'error') {
                    ?><img src="/files/images/error.png" alt="Error" width="16px"><?php
                } else {
                    echo $event['level'];
                }
                ?></td>
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
}

