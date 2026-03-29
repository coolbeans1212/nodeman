<?php

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
            }
        </style>
    </head>
    <body>
        <div class="desktop-icons">
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
        <div class="window glass active hidden" id="status" style="width: 500px">
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
        <div class="window glass active hidden" id="versions" style="width: 500px">
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
        <div class="window glass active hidden" id="npm" style="width: 500px">
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
        <script> // Shortcut colouring script on hover/click
            let shortcuts = document.getElementsByClassName("shortcut");
            let shortcutContainer = document.getElementsByClassName("desktop-icons")[0];
            for (let i  = 0, len = shortcuts.length; i < len; i++) {
                shortcuts[i].addEventListener('mouseover', function() {
                    try {
                        if (selectedShortcut !== this) {
                            this.style.background = '#4580c4a0';
                        }
                    }
                    catch {
                        this.style.background = '#4580c4a0';
                    }
                });
                shortcuts[i].addEventListener('mouseout', function() {
                    try {
                        if (selectedShortcut !== this) {
                            this.style.background = '';
                        }
                    } catch {
                        this.style.background = '';
                    }
                });
                shortcuts[i].addEventListener('mousedown', function() {
                    this.style.background = '#4580c4';
                    try {
                        if (selectedShortcut !== this) {
                            selectedShortcut.style.background = '';
                        }
                    } catch {
                        // do literally nothing!!! xD
                    }
                    selectedShortcut = this;
                });
            }
            window.addEventListener('mousedown', function () {
                try {
                    if (!shortcutContainer.contains(event.target)) {
                        selectedShortcut.style.background = '';
                        selectedShortcut = null;
                    }
                } catch {
                    // why do i even need a catch block this is so stupid
                }
            })

            // Movable windows (stole half of this from codepen)
            let windows = document.getElementsByClassName("window");
            function makeDraggable (element) {
                //Make an element draggable (or if it has a .title-bar class, drag based on the .title-bar element)
                let currentPosX = 0,
                            currentPosY = 0,
                            previousPosX = 0,
                            previousPosY = 0;

                element.querySelector('.title-bar').onmousedown = dragMouseDown;

                function dragMouseDown (e) {
                    //Prevent any default action on this element (you can remove if you need this element to perform its default action)
                    e.preventDefault();
                    //Get the mouse cursor position and set the initial previous positions to begin
                    previousPosX = e.clientX;
                    previousPosY = e.clientY;
                    //When the mouse is let go, call the closing event
                    document.onmouseup = closeDragElement;
                    //call a function whenever the cursor moves
                    document.onmousemove = elementDrag;
                }

                function elementDrag (e) {
                    //Prevent any default action on this element (you can remove if you need this element to perform its default action)
                    e.preventDefault();
                    //Calculate the new cursor position by using the previous x and y positions of the mouse
                    currentPosX = previousPosX - e.clientX;
                    currentPosY = previousPosY - e.clientY;
                    //Replace the previous positions with the new x and y positions of the mouse
                    previousPosX = e.clientX;
                    previousPosY = e.clientY;
                    //Set the element's new position
                    element.style.top = (element.offsetTop - currentPosY) + 'px';
                    element.style.left = (element.offsetLeft - currentPosX) + 'px';
                }

                function closeDragElement () {
                    //Stop moving when mouse button is released and release events
                    document.onmouseup = null;
                    document.onmousemove = null;
                }
            }
            for (let i  = 0, len = windows.length; i < len; i++) {
                makeDraggable(windows[i]);
            }
            // Z-Index handling
            let windowsArray = Array.from(windows);
            for (let i  = 0, len = windowsArray.length; i < len; i++) {
                windows[i].addEventListener('mousedown', function() {
                    let clickedWindow = windowsArray.indexOf(this);
                    windowsArray.unshift(windowsArray.splice(clickedWindow, 1)[0]); // move clicked window to the start of the array
                    console.log(`hello i am window ${clickedWindow} in the array and i have caused the array to turn into ${windowsArray}`)
                    for (let j = 0, len = windowsArray.length; j < len; j++) {
                        windowsArray[j].style.zIndex = 10000 - j;
                    }
                });
            }
            // Shortcut doubleclick
            for (let i = 0, len = shortcuts.length; i < len; i++) {
                shortcuts[i].addEventListener('dblclick', function() {
                    document.getElementById(shortcuts[i].id.replace("-shortcut", "")).classList.remove("hidden");
                });
            }
        </script>
    </body>
</html>
