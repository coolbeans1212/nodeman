<?php
$env = parse_ini_file(__DIR__ . '/.env');
if ($env['DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
ini_set('session.cookie_domain', '.mateishome.page');
session_start();
$mysqliAccount = require_once __DIR__ . "/db_account.php"; // accounts are stored on a seperate database to NODEMAN on MY machine.
$mysqli = require_once __DIR__ . "/db.php";
if (isset($_SESSION["user_id"])) {
  $sql = "SELECT username FROM users WHERE id = {$_SESSION["user_id"]}";
  $result = $mysqliAccount->query($sql);
  $user = $result->fetch_assoc();
  if ($user['username'] != 'admin' && $user['username'] != 'nodeman') {
    die();
  }
} else {
    die();
}

require_once __DIR__ . "/forms.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://unpkg.com/7.css">
        <title>NODEMAN</title>
        <style>
            body {
                background-color: #001310;
                background-image: url('https://mateishome.page/files/images/main_site_background_image.png');
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
                        <?php
                        $sql = 'SELECT * FROM projects';
                        $result = $mysqli->query($sql);
                        $projects = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $projects[] = $row;
                        }
                        $i = 0;
                        foreach ($projects as $row) {
                            ?><button role="tab" aria-controls="<?php echo strtolower($row['name']);?>"<?php if ($i == 0){ ?>aria-selected="true"<?php } ?>><?php echo $row['name'];?></button><?php
                            $i++;
                        }
                        ?>
                        <button role="tab" aria-controls="add">Add</button>
                    </menu>
                    <?php
                    $i = 0;
                    foreach ($projects as $row) {
                        ?><article role="tabpanel" id="<?php echo strtolower($row['name']);?>" <?php if($i != 0) { echo 'hidden'; } ?>>
                            <?php createProjectsForm(false, $row['name'], $row['location'], $row['main_file']); ?>
                        </article><?php
                        $i++;
                    }
                    ?>
                    <article role="tabpanel" id="add" hidden>
                        <?php createProjectsForm(true); // blank form for the "Add" tab ?>
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
                <p>The background behind is blurred.</p>
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
                <p>The background behind is blurred.</p>
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
        <div class="window glass active hidden" id="progress" style="top: 10px; left: 10px; z-index: 99999;">
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
