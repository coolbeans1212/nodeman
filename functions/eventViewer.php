<?php

function logEvent(mysqli $mysqli, int $userId, string $level, string $type, string $details) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $sql = "INSERT INTO events (user_id, level, type, details) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isss', $userId, $level, $type, $details);
    if (!$stmt->execute()) {
        error_log('Could not write to the events table: ' . $stmt->error, 0);
    }
}
