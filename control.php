<?php
require_once __DIR__ . '/isAdminAndParseEnv.php';

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
$userPreferences = getUserPreferences($mysqli);
var_dump($userPreferences);

$fonts = ['Default' => 'var(--w7-font)',
'Arial' => '9pt Arial' ,
'Arial Black' => '9pt Arial Black',
'Comic Sans MS' => '9pt Comic Sans MS',
'Courier New' => '9pt Courier New',
'Georgia' => '9pt Georgia',
'Impact' => '9pt Impact',
'Sans Serif' => '9pt sans-serif',
'Tahoma' => '9pt Tahoma',
'Serif' => '9pt serif',
'Trebuchet MS' => '9pt Trebuchet MS',
'Verdana' => '9pt Verdana'];

?>

<menu role="tablist" aria-label="Control Panel Tabs" id="control-panel-tabs">
    <button role="tab" data-id="Personalisation" aria-controls="personalisation" aria-selected="true">Personalisation</button>
</menu>
<article id="personalisation" role="tabpanel" data-id="personalisation">
    <form action="javascript:submitControlPanelForm('personalisation')">
        <label for="personalisation-wallpaper">Link to wallpaper image:</label>
        <input id="personalisation-wallpaper" type="text" class="full-width" value="<?php echo $userPreferences['wallpaper'] ?? '/files/images/blissnight.webp';?>">
        <label for="personalisation-font">Font:</label>
        <select id="personalisation-font">
            <?php
                foreach ($fonts as $fontName => $fontValue) {
                    ?><option value="<?php echo $fontValue;?>" style="font: <?php echo $fontValue;?>;"><?php echo $fontName;?></option><?php
                }
            ?>
        </select>
        <details>
            <summary>Experimental (will break)</summary>
            <label for="personalisation-css-version">CSS Version</label>
            <select id="personalisation-css-version">
                <option value="7.css">Windows® 7 (default)</option>
                <option value="xp.css">Windows® XP</option>
                <option value="98.css">Windows® 98</option>
            </select>
        </details>
        <hr>
        <button type="submit">Apply</input>
    </form>
</article>
