<?php
require_once __DIR__ . "/isAdminAndParseEnv.php";
require_once __DIR__ . "/forms.php";
require_once __DIR__ . "/statuscheck.php";
require_once __DIR__ . "/versions.php";
require_once __DIR__ . "/userinfo.php";
require_once __DIR__ . '/account/getUserPreferences.php';
$userPreferences = getUserPreferences($mysqli);

$sql = 'SELECT * FROM projects';
$result = $mysqli->query($sql);
$projects = [];
while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

function createTabs($projects) {
    $i = 0;
    foreach ($projects as $row) {
        ?><button role="tab" data-id="<?php echo $row['name'];?>" aria-controls="<?php echo str_replace(' ', '-', strtolower($row['name']));?>"<?php if ($i == 0){ ?>aria-selected="true"<?php } ?>><?php echo $row['name'];?></button><?php
        $i++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>NODEMAN</title>
        <link id="stylesheet" rel="stylesheet" href="/customisablestyles.css.php?i=<?php echo time();?>"> <!-- also includes all the other stylesheets -->
    </head>
    <body>
        <div class="desktop-icons">
            <div class="shortcut-group-left">
                <div class="shortcut-column">
                    <div class="shortcut" id="projects-shortcut">
                        <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                        <span class="shortcut-text">Add or view projects</span>
                    </div>
                    <div class="shortcut" id="status-shortcut">
                        <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                        <span class="shortcut-text">Check or change status</span>
                    </div>
                    <div class="shortcut" id="versions-shortcut">
                        <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                        <span class="shortcut-text">Rollback or upload version</span>
                    </div>
                    <div class="shortcut" id="npm-shortcut">
                        <img src="/files/images/node.svg" alt="NodeJS" class="icon">
                        <span class="shortcut-text">Configure NPM</span>
                    </div>
                </div>
                <div class="shortcut-column">
                    <div class="shortcut" id="utilities-shortcut">
                        <img src="/files/images/folderstar.png" alt="Utilities" class="icon">
                        <span class="shortcut-text">Utilities</span>
                    </div>
                </div>
            </div>
            <div class="shortcut-group-right">
                <div class="shortcut-column">
                    <div class="shortcut" id="account-shortcut">
                        <img src="/files/images/user.png" alt="Account" class="icon">
                        <span class="shortcut-text">Your account</span>
                    </div>
                    <div class="shortcut" id="control-panel-shortcut">
                        <img src="/files/images/controlpanel.png" alt="Control Panel" class="icon">
                        <span class="shortcut-text">Control Panel</span>
                    </div>
                </div>
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
                            <?php createDirectoryUploadForm($row['id']); ?><hr><div class="version-pages-container"><?php makeVersionPages($row['id'], $userPreferences['versions_per_page'] ?? 2); ?></div>
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
                <section class="tabs">
                    <menu role="tablist" aria-label="NPM tabs" id="npm-tabs">
                        <?php createTabs($projects); ?>
                    </menu>
                    <?php
                    $i = 0;
                    foreach ($projects as $row) {
                        ?><article role="tabpanel" data-id="<?php echo $row['name'];?>" class="npm-tabpanel-contents" id="<?php echo str_replace(' ', '-', strtolower($row['name']));?>" <?php if($i != 0) { echo 'hidden'; } ?>>
                            Loading...
                        </article><?php
                        $i++;
                    }
                    ?>
                </section>
            </div>
        </div>
        <div class="window glass active hidden" id="account" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Your account</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <?php userInfo($user); ?>
            </div>
            </div>
        </div>
        <div class="window glass active hidden" id="control-panel" style="top: 10px; left: 10px;">
            <div class="title-bar">
                <div class="title-bar-text">Control Panel</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
                <?php require_once __DIR__ . '/control.php'; ?>
            </div>
            </div>
        </div>
        <?php require_once __DIR__ . '/utilities/windows.php'; // has all the windows from the Utilities shortcut :) ?>


        <div class="window glass active hidden topmost" id="progress" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999;">
            <div class="title-bar">
                <div class="title-bar-text">Processing...</div>
            </div>
            <div class="window-body has-space">
                <div role="progressbar" class="marquee" id="progressbar"><div class="width: 100%;"></div></div>
                <p id="progress-message">Awaiting response from the server...</p>
                <button id="progress-ok" class="hidden">OK</button>
            </div>
        </div>
    <div class="window glass active hidden topmost" id="dialogue" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999;">
            <div class="title-bar">
                <div class="title-bar-text">Dialogue Box</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
            </div>
    </div>
    <script src="/main.js" defer></script>
    <script src="/forms.js" defer></script>
    <script src="/live.js" defer></script>
    </body>
    <div class="hidden" id="javascript-sucks"></div>
</html>
