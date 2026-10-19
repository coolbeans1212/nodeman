<?php
header('Content-Type: text/css');
session_start();
if (isset($_SESSION["user_id"])) {
    require_once __DIR__ . "/isAdminAndParseEnv.php";
    require_once __DIR__ . '/account/getUserPreferences.php';
    $userPreferences = getUserPreferences($mysqli);
} else {
    $userPreferences = null;
}
?>
@import url("https://unpkg.com/<?php echo $userPreferences['css_version'] ?: '7.css'; ?>");
@import url("/styles.css?i=<?php echo time();?>");

:root {
    --default-font: <?php
        if ($userPreferences['css_version'] == '7.css' || $userPreferences['css_version'] == '') {
            echo 'var(--w7-font)';
        } else {
            echo '11pt "Pixelated MS Sans Serif", Arial';
        }
    ?>;
}


body {
    background-image: url('<?php echo $userPreferences['wallpaper'] ?: '/files/images/blissnight.webp'; ?>');
    font: <?php echo $userPreferences['font'] ?: 'var(--default-font)'; ?>
}
