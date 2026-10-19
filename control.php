<?php
require_once __DIR__ . '/isAdminAndParseEnv.php';
require_once __DIR__ . '/account/getUserPreferences.php';

$userPreferences = getUserPreferences($mysqli);

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
        <input id="personalisation-wallpaper" type="text" class="full-width" value="<?php echo $userPreferences['wallpaper'] ?: '/files/images/blissnight.webp';?>">
        <label for="personalisation-font">Font:</label>
        <select id="personalisation-font">
            <?php
                foreach ($fonts as $fontName => $fontValue) {
                    ?><option <?php echo $fontValue == $userPreferences['font'] ? 'selected' : ''; ?> value="<?php echo $fontValue;?>" style="font: <?php echo $fontValue;?>;"><?php echo $fontName;?></option><?php
                }
            ?>
        </select>
        <details>
            <summary>Experimental (will break)</summary>
            <label for="personalisation-css-version">CSS Version</label>
            <select id="personalisation-css-version">
                <option value="7.css" <?php echo '7.css' == $userPreferences['css_version'] ? 'selected' : ''; ?>>Windows® 7 (default)</option>
                <option value="xp.css" <?php echo 'xp.css' == $userPreferences['css_version'] ? 'selected' : ''; ?>>Windows® XP</option>
                <option value="98.css" <?php echo '98.css' == $userPreferences['css_version'] ? 'selected' : ''; ?>>Windows® 98</option>
            </select>
        </details>
        <hr>
        <button type="submit">Apply</input>
    </form>
</article>
