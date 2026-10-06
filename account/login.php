<?php

session_start();
$mysqli = require_once __DIR__ . "/../db_account.php";
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
   
    if ($user && password_verify($_POST["password"], $user["hashed_password"])) {
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
</head>
<body>
      <h1>Log in</h1>
    <form method="post">
        <label for="username">Username</label><br>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"><br>
        <label for="password">Password</label><br>
        <input type="password" name="password" id="password"><br>
        <input type="submit" value="Log in">
        <?php if (isset($_POST['password'])): //if this is set and the user is still on the login page, it means the credentials are incorrect ?>
          <br><oblique>Invalid login</oblique>
        <?php endif; ?>
    </form>
    </div>
</body>
</html>
