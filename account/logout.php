<?php
require_once __DIR__ . '/../functions/eventViewer.php';
$mysqli = require_once __DIR__ . '/../db.php';
session_start();
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $username = $result->fetch_assoc();
    $username = $username['username'];
} else {
    die();
}
logEvent($mysqli, $_SESSION['user_id'], 'info', 'logout', sprintf('User %1$s logged out manually.', $username ?? 'unknown'));
session_destroy();
header('Location: /');
