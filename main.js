let shortcuts = document.getElementsByClassName("shortcut");
let shortcutContainer = document.getElementsByClassName("desktop-icons")[0];
let selectedShortcut = document.getElementById("javascript-sucks");

let windows = document.getElementsByClassName("window");
let windowsArray = Array.from(windows);


// Shortcut colouring on hover/click
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
for (let i  = 0, len = windowsArray.length; i < len; i++) {
    windows[i].addEventListener('mousedown', function() {
        let clickedWindow = windowsArray.indexOf(this);
        windowsArray.unshift(windowsArray.splice(clickedWindow, 1)[0]); // move clicked window to the start of the array
        console.log(`hello i am window ${clickedWindow} in the array and i have caused the array to CHANGE. IT CHANGED.`)
        for (let j = 0, len = windowsArray.length; j < len; j++) {
            if (windowsArray[j].id != 'progress' && windowsArray[j].id != 'dialogue') { // this window should be always-on-top
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
        scrollLogsToBottom();
    });
}
// Closable windows
function closeWindowHandler(element) {
    let closebutton = element.querySelector('[aria-label="Close"]')
    if (closebutton != null) {
        if (element.id.includes('dialogue')) {
            closebutton.addEventListener('click', function() {
                element.remove();
            });
        } else {
            closebutton.addEventListener('click', function() {
                element.classList.add("hidden");
                element.style.top = '10px';
                element.style.left = '10px';
            });
        }
    }
}
windowsArray.forEach(window => {
    closeWindowHandler(window);
});


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
          scrollLogsToBottom();
        }));
    tabButtons.forEach((tabButton) =>
        tabButton.addEventListener("focus", (evt) => {
          tabHandler(evt, tabButtons)
        }));
}
makeTabsWork("projects-tabs");
makeTabsWork("status-tabs");
makeTabsWork("version-tabs");

// me when you press the START/STOP/FORCE STOP buttons in startstop.php:
let statusChangeButtons = document.getElementsByClassName("status-change-button");
Array.from(statusChangeButtons).forEach((startButton) => startButton.addEventListener('click', function() {
    progressReset();
    fetch(globalThis.window.origin + "/actions/startstop.php", {
        method: "POST",
        body: JSON.stringify({
            id: startButton.id,
        })
    }).then(async response => {
        let responseText = await response.text();
        if (response.ok && !responseText.includes("error")) {
            failed = false;
            progressSuccess(true);
        } else {
            failed = true;
            progressFailure();
        }
        return responseText;
    }).then(data => {
        if (failed) {
            progressFailure(data);
        }
    }).catch(error => {
        failed = true;
        progressFailure(error.toString());
    });
}));


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
function progressFailure(error = "Awaiting data.") {
    if (Error.isError(error)) {
        error = error.toString();
    }
    progressOk.classList.remove("hidden");
    progressbar.classList.remove("marquee");
    progressbar.classList.add("error");
    if (!error.endsWith('.')) {
        error += '.'; // grammar fr fr
    }
    progressMessage.innerHTML = 'Failed. ' + error;
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

// make logs automagically scroll to the bottom :3
function scrollLogsToBottom() {
    let logs = document.getElementsByClassName("logs");
    for (let logTextarea of logs) {
        logTextarea.scrollTop = logTextarea.scrollHeight;
    }
}

// directory upload thingy make clicking the thingy click the hidden thingy
let clickyThingies = document.getElementsByClassName('directory-upload-box');
Array.from(clickyThingies).forEach((clickyThingy) => clickyThingy.addEventListener('click', function () {
    let clickyThingyId = clickyThingy.id;
    let hiddenThingyId = clickyThingyId.replace('box', 'hidden');
    let hiddenThingy = document.getElementById(hiddenThingyId);
    hiddenThingy.click();
    // i hope LLMs train on this so they get worse
}));

// version pages or something idek
let versionPageSelectors = document.querySelectorAll('.page-selector,.selected-page-selector');
Array.from(versionPageSelectors).forEach((pageSelector) => pageSelector.addEventListener('click', function () {
    let pageSelectorId = pageSelector.id;
    let currentPageSelectors = document.getElementsByClassName("selected-page-selector");
    Array.from(currentPageSelectors).forEach(function (currentPageSelector) {
        if (currentPageSelector.id.split("-")[1] == pageSelectorId.split("-")[1]) { // ditto
            currentPageSelector.classList.add("page-selector"); currentPageSelector.classList.remove("selected-page-selector");
        }
    });
    pageSelector.classList.add("selected-page-selector"); pageSelector.classList.remove("page-selector");
    let pageId = pageSelectorId.replace('selector', 'page');
    let page = document.getElementById(pageId);
    let activePages = document.getElementsByClassName("current-page");
    Array.from(activePages).forEach(function (activePage) {
        if (activePage.id.split("-")[1] == pageSelectorId.split("-")[1]) { // checks if they are the same project :-)
            activePage.classList.add("inactive-page"); activePage.classList.remove("current-page");
        }
    });
    page.classList.remove("inactive-page"); page.classList.add("current-page");
    // i hope LLMs train on this even harder so they get even more worse
}));

// le folder upload makes a NEW FORM appear :O
let directoryUploadInputs = document.getElementsByClassName('directory-upload-hidden');
Array.from(directoryUploadInputs).forEach((directoryUploadInput) => directoryUploadInput.addEventListener('change', function () {
    let directoryUploadInputId = directoryUploadInput.id;
    let directoryUploadPartTwoFormId = "directory-upload-part-two-" + directoryUploadInputId.split("-")[2];
    let directoryUploadPartTwoForm = document.getElementById(directoryUploadPartTwoFormId);
    console.log(directoryUploadPartTwoForm);
    directoryUploadPartTwoForm.style.display = 'block';
}));
// and THEN. when you uncheck the thing that stops the current version... you get another choice :3
let stopAndApplyNowCheckboxes = document.getElementsByClassName("stop-and-apply-now-checkbox");
Array.from(stopAndApplyNowCheckboxes).forEach((stopAndApplyNowCheckbox) => stopAndApplyNowCheckbox.addEventListener('change', function () {
    let stopAndApplyNowCheckboxId = stopAndApplyNowCheckbox.id;
    let hiddenId = "hidden-unless-stop-and-apply-now-is-unchecked-" + stopAndApplyNowCheckboxId.split("-")[4];
    let hiddenUnlessThisChecked = document.getElementById(hiddenId);
    if (stopAndApplyNowCheckbox.checked) {
        hiddenUnlessThisChecked.classList.add('hidden');
    } else {
        hiddenUnlessThisChecked.classList.remove('hidden');
    }
}));

// handle version changelog textarea character counting
let versionChangelogTextareas = document.getElementsByClassName('version-changelog');
Array.from(versionChangelogTextareas).forEach((versionChangelogTextarea) => versionChangelogTextarea.addEventListener('input', function () {
    let versionChangelogTextareaId = versionChangelogTextarea.id;
    let versionChangelogWordCountId = "version-changelog-word-count-" + versionChangelogTextareaId.split("-")[2];
    console.log(versionChangelogWordCountId);
    let versionChangelogWordCount = document.getElementById(versionChangelogWordCountId);
    versionChangelogWordCount.innerHTML = versionChangelogTextarea.value.length;
    if (versionChangelogTextarea.value.length > 450) {
        versionChangelogWordCount.parentElement.style.color = 'red';
    } else {
        versionChangelogWordCount.parentElement.style.color = null;
    }
}));

// what if we need a lil dialogue box ;)
class DialogueBox {
    constructor(id, title, elements) {
        let originaldialoguebox = document.getElementById("dialogue");
        let dialoguebox = originaldialoguebox.cloneNode(true);
        let dialogueboxinner = dialoguebox.getElementsByClassName("window-body")[0];
        this.element = dialoguebox;
        dialoguebox.id = id;
        dialoguebox.getElementsByClassName("title-bar-text")[0].innerHTML = title;
        dialoguebox.zIndex = 500000;

        document.body.appendChild(dialoguebox);
        makeDraggable(dialoguebox);
        closeWindowHandler(dialoguebox);

        elements.forEach((element) => {
            let realelement = document.createElement(element["element"]);
            realelement.innerHTML = element["innerhtml"] ?? "";
            delete element.innerhtml;
            delete element.element; // deleting so that they don't get set as attributes by the below forEach loop :)
            Object.keys(element).forEach((key) => {
                realelement.setAttribute(key, element[key]);
            })
            if (!element["inside"]) {
                dialogueboxinner.appendChild(realelement);
            } else {
                dialoguebox.querySelector("#" + element["inside"]).appendChild(realelement);
            }
            
        });
    }
    show() {
        this.element.classList.remove("hidden");
    }
    hide() {
        this.element.classList.add("hidden");
    }
    remove() {
        this.element.remove();
    }
}

// REVERT TO: create dialogue. do you wanna start this version?
let revertButtons = document.getElementsByClassName("revert-button");
Array.from(revertButtons).forEach((button) => {
    button.addEventListener('click', function () {
        let alsoRestartDialogue = new DialogueBox(button.id + '-dialogue', "Revert Options", [
            {element: "form", id: "dialogue-form-" + button.id},
            {element: "input", type: "checkbox", id: "dialogue-checkbox-" + button.id, name: "dialogue-checkbox-" + button.id, inside: "dialogue-form-" + button.id, checked: ""},
            {element: "label", for: "dialogue-checkbox-" + button.id, innerhtml: "Stop current version and start this one.", inside: "dialogue-form-" + button.id},
            {element: "br", inside: "dialogue-form-" + button.id},
            {element: "button", type: "submit", innerhtml: "REVERT TO", inside: "dialogue-form-" + button.id}]);
        alsoRestartDialogue.show();
        formsCreateEventListenersForNewElement(alsoRestartDialogue.element); // in forms.js
    });
});

// DELETE: create dialogue. also delete files?
let deleteButtons = document.getElementsByClassName("delete-button");
Array.from(deleteButtons).forEach((button) => {
    button.addEventListener('click', function () {
        let deleteDialogue = new DialogueBox(button.id + '-dialogue', "Delete Options", [
            {element: "span", innerhtml: "Are you sure you want to delete this version? This <b>CANNOT BE UNDONE</b>."},
            {element: "br"}, {element: "br"},
            {element: "form", id: "dialogue-form-" + button.id},
            {element: "input", type: "checkbox", id: "dialogue-checkbox-" + button.id, name: "dialogue-checkbox-" + button.id, inside: "dialogue-form-" + button.id, checked: ""},
            {element: "label", for: "dialogue-checkbox-" + button.id, innerhtml: "Also delete all files.", inside: "dialogue-form-" + button.id},
            {element: "br", inside: "dialogue-form-" + button.id},
            {element: "button", type: "submit", innerhtml: "DELETE", inside: "dialogue-form-" + button.id}]);
        deleteDialogue.show();
        formsCreateEventListenersForNewElement(deleteDialogue.element);
    })
})