<?php
require_once __DIR__ . "/isAdminAndParseEnv.php";
require_once __DIR__ . "/forms.php";
require_once __DIR__ . "/statuscheck.php";
require_once __DIR__ . "/versions.php";
//require_once __DIR__ . "/npmdetails.php";

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
        <link rel="stylesheet" href="https://unpkg.com/7.css">
        <title>NODEMAN</title>
        <link rel="stylesheet" href="/styles.css?i=<?php echo time();?>">
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
                            <?php createDirectoryUploadForm($row['id']); ?><hr><div class="version-pages-container"><?php makeVersionPages($row['id'], 2); ?></div>
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
    <div class="window glass active hidden" id="dialogue" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999;">
            <div class="title-bar">
                <div class="title-bar-text">Dialogue Box</div>
                <div class="title-bar-controls">
                    <button aria-label="Close"></button>
                </div>
            </div>
            <div class="window-body has-space">
            </div>
    </div>
        <script src="/main.js"></script>
        <script src="/forms.js"></script>
        <script src="/live.js"></script>
    </body>
    <div class="hidden" id="javascript-sucks"></div>
</html>
