<?php
function getUserInfoFromId(mysqli $mysqli, ?int $id): array {
    $nullUser = ['username' => '', 'id' => null, 'email' => '', 'admin' => '', 'date_created' => '1900-01-01 00:00:00', 'pfp' => ''];
    if (!$id) {
        return $nullUser;
    }

    $sql = "SELECT username,id,email,admin,date_created,pfp FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        error_log('Could not get user info: ' . $stmt->error, 0);
    }
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?? $nullUser;
}
