<?php
require_once __DIR__ . "/isAdminAndParseEnv.php";
require_once __DIR__ . "/forms.php";
require_once __DIR__ . "/statuscheck.php";
require_once __DIR__ . "/versions.php";

$sql = 'SELECT * FROM projects';
$result = $mysqli->query($sql);
$projects = [];
while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

function createTabs($projects) {
    $i = 0;
    foreach ($projects as $row) {
        ?><button role="tab" aria-controls="<?php echo str_replace(' ', '-', strtolower($row['name']));?>"<?php if ($i == 0){ ?>aria-selected="true"<?php } ?>><?php echo $row['name'];?></button><?php
        $i++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://unpkg.com/7.css">
        <title>NODEMAN</title>
        <style>
            body {
                background-color: #001310;
                background-image: url('https://mateishome.page/files/images/main_site_background_image.webp');
                background-position: top center;
                background-repeat: no-repeat;
                font: var(--w7-font);
                margin: 0;
            }
            @media screen and (min-width: 1920px) {
                body {
                    background-size: 100% auto;
                }
            }
            .desktop-icons {
                display: flex;
                flex-direction: column;
                position: absolute;
                bottom: 0;
            }
            .shortcut {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                color: white;
                font-size: 14px;
                width: 75px;
                padding: 5px;
                margin: 5px;
            }
            .icon {
                width: 60px;
                height: 60px;
            }
            .hidden {
                display: none;
            }
            .window {
                position: absolute;
                min-width: 500px;
                max-width: 80%;
            }
            .directory-upload-box {
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #ececec;
                height: 150px;
                width: 75%;
                margin-left: auto;
                margin-right: auto;
                cursor: pointer;
            }
            .directory-upload-hidden {
                display: none;
            }
            .version {
                display: flex;
                background-color: #fefefe;
                width: 75%;
                min-height: 75px;
                margin-left: auto;
                margin-right: auto;
                border: 1px solid #ececec;
            }
            .version-name-bg {
                font-size: 60px;
                overflow: hidden;
                text-wrap: nowrap;
                color: #00000011;
                position: absolute;
                -webkit-user-select: none;
                user-select: none;
                width: calc(70% - 10px);
                padding-left: 10px;
                transform: translateY(-10px);
            }
            .version-inner {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                padding: 10px;
                z-index: 0; /* this allows the text to be selectable while the text in the background still cannot be */
                width: calc(100% - 20px);
            }
            .version-inner-left {
                text-align: left;
                width: 80%;
            }
            .version-inner-right {
                display: flex;
                flex-direction: column;
                text-align: right;
            }
            .version-inner-right button {
                margin: 2px;
            }
            .version-name {
                font-size: 20px;
                max-width: 100%;
                overflow: hidden;
                display: inline-block;
            }
            .inactive-page {
                display: none;
            }
            .page-selector-wrapper {
                overflow-wrap: anywhere;
                display: flex;
                justify-content: center;
            }
            .page-selector, .selected-page-selector {
                display: inline;
                margin: 3px;
                font-size: 15px;
                border: 1px solid #ececec;
                min-width: 20px;
                text-align: center;
            }
            .page-selector {
                background: #f0f0f0;
                cursor: pointer;
            }
            .selected-page-selector {
                cursor: default;
                background: #cfcfcf;
            }
        </style>
    </head>
    <body>
        <div class="desktop-icons">
            <div class="shortcut" id="projects-shortcut">
                <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                <span>Add or view projects</span>
            </div>
            <div class="shortcut" id="status-shortcut">
                <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                <span>Check or change status</span>
            </div>
            <div class="shortcut" id="versions-shortcut">
                <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                <span>Rollback or upload version</span>
            </div>
            <div class="shortcut" id="npm-shortcut">
                <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                <span>Configure NPM</span>
            </div>
        </div>
        <div class="window glass active hidden" id="projects" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Add or view projects</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <section class="tabs">
                    <menu role="tablist" aria-label="Projects tabs" id="projects-tabs">
                        <?php createTabs($projects); ?>
                        <button role="tab" aria-controls="add">Add</button>
                    </menu>
                    <?php
                    $i = 0;
                    foreach ($projects as $row) {
                        ?><article role="tabpanel" id="<?php echo str_replace(' ', '-', strtolower($row['name']));?>" <?php if($i != 0) { echo 'hidden'; } ?>>
                            <?php createProjectsForm(false, $row['id'], $row['name'], $row['location'], $row['main_file']); ?>
                        </article><?php
                        $i++;
                    }
                    ?>
                    <article role="tabpanel" id="add" hidden>
                        <?php createProjectsForm(true, -1); // blank form for the "Add" tab ?>
                    </article>
                </section>
            </div>
        </div>
        <div class="window glass active hidden" id="status" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Check or change status</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <section class="tabs">
                    <menu role="tablist" aria-label="Status tabs" id="status-tabs">
                        <?php createTabs($projects); ?>
                    </menu>
                    <?php
                    $i = 0;
                    foreach ($projects as $row) {
                        ?><article role="tabpanel" id="<?php echo str_replace(' ', '-', strtolower($row['name']));?>" <?php if($i != 0) { echo 'hidden'; } ?>>
                            <?php statusInfo($row['name']); logs($row['name'], $env); controls($row['name']); ?>
                        </article><?php
                        $i++;
                    }
                    ?>
                </section>
            </div>
        </div>
        <div class="window glass active hidden" id="versions" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Rollback or upload version</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <section class="tabs">
                    <menu role="tablist" aria-label="Version tabs" id="version-tabs">
                        <?php createTabs($projects); ?>
                    </menu>
                    <?php
                    $i = 0;
                    foreach ($projects as $row) {
                        ?><article role="tabpanel" id="<?php echo str_replace(' ', '-', strtolower($row['name']));?>" <?php if($i != 0) { echo 'hidden'; } ?>>
                            <?php createDirectoryUploadForm($row['id']); ?><hr><?php makeVersionPages($row['id'], 2); ?>
                        </article><?php
                        $i++;
                    }
                    ?>
                </section>
            </div>
        </div>
        <div class="window glass active hidden" id="npm" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Configure NPM</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <p>The background behind is blurred.</p>
            </div>
        </div>
        <div class="window glass active hidden" id="progress" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999;">
            <div class="title-bar">
                <div class="title-bar-text">Processing...</div>
            </div>
            <div class="window-body has-space">
                <div role="progressbar" class="marquee" id="progressbar"><div class="width: 100%;"></div></div>
                <p id="progress-message">Awaiting response from the server...</p>
                <button id="progress-ok" class="hidden">OK</button>
            </div>
        </div>
        <script src="/main.js"></script>
    </body>
    <div class="hidden" id="javascript-sucks"></div>
</html>
