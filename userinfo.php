<?php
require_once __DIR__ . '/isAdminAndParseEnv.php';
$sql = "SELECT * FROM users WHERE id = ?"; // name should be UNIQUE.
$stmt = $mysqliAccount->prepare($sql);
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

function userInfo($user) {
    echo 'You are currently logged in as ' . $user['username'] . '.';
    ?><br>
    <a href="/account/logout.php"><button>LOG OUT</button></a>
    <?php
}
