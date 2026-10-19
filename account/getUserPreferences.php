<?php
function getUserPreferences($mysqli) { // this is in a function so it can be called from inside itself, to make preferences for a user that doesn't have them
    $sql = "SELECT * FROM user_preferences WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userPreferences = $result->fetch_assoc();
    if ($userPreferences == null) {
        $sql = "INSERT INTO user_preferences (id) VALUES (?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $userPreferences = getUserPreferences($mysqli);
    }
    return $userPreferences;
}
