<?php

session_start();
$mysqli = require_once __DIR__ . "/../db.php";
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT username FROM users WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die("SQL prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
   
    if ($user && password_verify($_POST["password"], $user["password"])) {
        session_start();
        session_regenerate_id();
        $_SESSION["user_id"] = $user["id"];
        header("Location: /");
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://unpkg.com/7.css">
    <title>NODEMAN::Login</title>
    <link rel="stylesheet" href="/customisablestyles.css.php?i=<?php echo time();?>">
</head>
<body>
    <div class="window glass active" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="title-bar">
            <div class="title-bar-text">Login to NODEMAN</div>
            <div class="title-bar-controls">
                <button aria-label="Help" id="login-help-button"></button>
                <button aria-label="Close" disabled></button>
            </div>
        </div>
        <div class="window-body has-space">
            <form method="post">
                <?php if (isset($_POST['password'])) { //if this is set and the user is still on the login page, it means the credentials are incorrect ?>
                    <div class="bad">Invalid login credentials</div><br>
                <?php } ?>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password"><br>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
    <div class="window glass active hidden topmost" style="top: 50%; left: 50%; z-index: 99999;" id="login-help-window">
        <div class="title-bar">
            <div class="title-bar-text">Account help</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="login-help-close"></button>
            </div>
        </div>
        <div class="window-body has-space">
            Administrators: to make a new account, please follow the instructions in INSTALL.md, under the subheading "Set up the database".<br>
            Not logging in? Make sure that the correct table definitions are set in the correct databases, and that there is at least one account in the users table.</br>
        </div>
    </div>
    <script src="login.js"></script>
    <script src="/main.js"></script>
</body>
</html>
