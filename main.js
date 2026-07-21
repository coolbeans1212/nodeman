// Shortcut colouring on hover/click
let shortcuts = document.getElementsByClassName("shortcut");
let shortcutContainer = document.getElementsByClassName("desktop-icons")[0];
let selectedShortcut = document.getElementById("javascript-sucks");
for (let i  = 0, len = shortcuts.length; i < len; i++) {
    shortcuts[i].addEventListener('mouseover', function() {
        if (selectedShortcut !== this) {
            this.style.background = '#4580c4a0';
        }
    });
    shortcuts[i].addEventListener('mouseout', function() {
        if (selectedShortcut !== this) {
            this.style.background = '';
        }
    });
    shortcuts[i].addEventListener('mousedown', function() {
        this.style.background = '#4580c4';
        if (selectedShortcut !== this) {
            selectedShortcut.style.background = '';
        }
        selectedShortcut = this;
    });
}
globalThis.addEventListener('mousedown', function () {
    if (!shortcutContainer.contains(event.target)) {
        selectedShortcut.style.background = '';
        selectedShortcut = document.getElementById("javascript-sucks");
    }
});
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
        console.log(`hello i am window ${clickedWindow} in the array and i have caused the array to CHANGE. IT CHANGED.`)
        for (let j = 0, len = windowsArray.length; j < len; j++) {
            if (windowsArray[j].id != 'progress') { // this window should be always-on-top
                windowsArray[j].style.zIndex = 10000 - j;
            }
        }
    });
}
// Shortcut doubleclick
for (let i = 0, len = shortcuts.length; i < len; i++) {
    shortcuts[i].addEventListener('dblclick', function() {
        document.getElementById(shortcuts[i].id.replace("-shortcut", "")).classList.remove("hidden");
        document.getElementById(shortcuts[i].id.replace("-shortcut", "")).style.zIndex = 50000; //make it appear on top of all windows initially
    });
}
// Closable windows
let closeButtons = document.querySelectorAll('[aria-label="Close"]');
for (let i = 0, len = closeButtons.length; i < len; i++) {
    closeButtons[i].addEventListener('click', function() {
        this.parentNode.parentNode.parentNode.classList.add("hidden"); //thing.thing.thing.thing.AAAAAAAAAA
        this.parentNode.parentNode.parentNode.style.top = '10px';
        this.parentNode.parentNode.parentNode.style.left = '10px';
    });
}

// Tabs that actually work (stolen from 7.css)
function tabHandler(e, tabButtons) {
    e.preventDefault();
    const tabContainer = e.target.parentElement.parentElement;
    const targetId = e.target.getAttribute("aria-controls");
    tabButtons.forEach((_tabButton) =>
        _tabButton.setAttribute("aria-selected", false)
    );
    e.target.setAttribute("aria-selected", true);
    e.target.focus();
    tabContainer
        .querySelectorAll("[role=tabpanel]")
        .forEach((tabPanel) => tabPanel.setAttribute("hidden", true));
    tabContainer
        .querySelector(`[role=tabpanel]#${targetId}`)
        .removeAttribute("hidden");
}

function makeTabsWork(tabListId) {
    const tabList = document.getElementById(tabListId);
    const tabButtons = tabList.querySelectorAll("[role=tab]");
    tabButtons.forEach((tabButton) =>
        tabButton.addEventListener("mousedown", (evt) => {
          tabHandler(evt, tabButtons)
        }));
    tabButtons.forEach((tabButton) =>
        tabButton.addEventListener("focus", (evt) => {
          tabHandler(evt, tabButtons)
        }));
}
makeTabsWork("projects-tabs");
makeTabsWork("status-tabs");

// Form submission
function submitForm(type, id) {
    console.log(type);
    if (type == "projects") {
        let name = document.getElementById("projects-name-" + id).value;
        let location = document.getElementById("projects-location-" + id).value;
        let mainFile = document.getElementById("projects-main-file-" + id).value;
        let doDelete = false;
        try {
            doDelete = document.getElementById("projects-delete-" + id).checked; // JavaScript is so inconsistent... and NOT in a good way like PHP is >:(
        } catch {
            doDelete = false;
        }
        console.log(name, location, mainFile, doDelete);
        fetch(globalThis.location.origin + "/actions/projects.php", {
            method: "POST",
            body: JSON.stringify({
                id: id,
                name: name,
                location: location,
                main_file: mainFile,
                delete: doDelete
            })
        }).then(async response => {
            let responseText = await response.text();
            if (response.ok && !responseText.includes("error")) {
                progressSuccess(true);
            } else {
                progressFailure();
            }
            return responseText;
        }).then(data => {
            console.log('Server response: ' + data);
        });
        progressReset();
    }
}

// Progressbar shenanigans
let progressWindow = document.getElementById("progress");
let progressOk = document.getElementById("progress-ok");
let progressbar = document.getElementById("progressbar");
let progressMessage = document.getElementById("progress-message");
function progressReset() {
    progressMessage.innerHTML = 'Awaiting response from the server...';
    progressOk.classList.add("hidden");
    progressWindow.classList.remove("hidden");
    progressbar.classList.add("marquee");
    progressbar.classList.remove("error");
}
function progressFailure() {
    progressOk.classList.remove("hidden");
    progressbar.classList.remove("marquee");
    progressbar.classList.add("error");
    progressMessage.innerHTML = 'Failed. Check console.';
}
function progressCancel() {
    progressOk.classList.remove("hidden");
    progressbar.classList.remove("marquee");
    progressbar.classList.add("error");
    progressMessage.innerHTML = 'Cancelled.';
}
function progressSuccess(refreshRequired) {
    progressOk.classList.remove("hidden");
    progressbar.classList.remove("marquee");
    if (refreshRequired) {
        progressMessage.innerHTML = 'Success. Refresh the page to see the changes.';
    } else {
        progressMessage.innerHTML = 'Success.';
    }
}
progressOk.addEventListener('click', function() {
    progressWindow.classList.add("hidden");
});