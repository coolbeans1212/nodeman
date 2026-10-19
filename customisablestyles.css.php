<?php
header('Content-Type: text/css');
require_once __DIR__ . '/isAdminAndParseEnv.php';
require_once __DIR__ . '/account/getUserPreferences.php';
$userPreferences = getUserPreferences($mysqli);
?>
@import url("https://unpkg.com/<?php echo $userPreferences['css_version'] ?: '7.css'; ?>");
@import url("/styles.css?i=<?php echo time();?>");


body {
    background-image: url('<?php echo $userPreferences['wallpaper'] ?: '/files/images/blissnight.webp'; ?>');
    font: <?php echo $userPreferences['font'] ?: 'var(--w7-font)'; ?>;
}
