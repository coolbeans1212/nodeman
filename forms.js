let failed = false;

function formsCreateEventListenersForNewElement(element) {
    if (element.id.split("-")[0] == 'revert') {
        let formelement = document.getElementById('dialogue-form-revert-' + element.id.split("-")[1]);
        formelement.addEventListener('submit', function(event) {
            event.preventDefault();
            submitRevertForm(element.id.split("-")[1]);
        });
    }
    if (element.id.split("-")[0] == 'delete') {
        let formelement = document.getElementById('dialogue-form-delete-' + element.id.split("-")[1]);
        formelement.addEventListener('submit', function(event) {
            event.preventDefault();
            submitDeleteForm(element.id.split("-")[1]);
        });
    }
    if (element.id.split("-")[1] == 'dependency') {
        let formelement = document.getElementById('dialogue-form-installdependency-' + element.id.split("-")[0]);
        formelement.addEventListener('submit', function(event) {
            event.preventDefault();
            submitNpmForm('installDependency', element.id.split("-")[0], element, element.parentElement.querySelector("#dialogue-input-" + element.id.split("-")[0]).value);
            element.remove();
        });
    }
}

function createEventListenersForNpmCommandsOnLoad() {
    let npmInstallMissingButtons = document.getElementsByClassName("npm-install-missing-button");
    let npmPruneExtraneousButtons = document.getElementsByClassName("npm-prune-extraneous-button");
    let removeDependencyButtons = document.getElementsByClassName("npm-remove-button");
    let installNewDependencyButtons = document.getElementsByClassName("npm-install-new-dependency");
    Array.from(npmInstallMissingButtons).forEach(function (installMissingButton) {
        if (installMissingButton.dataset.listenerAdded) return;
        installMissingButton.dataset.listenerAdded = "true";
        installMissingButton.addEventListener('click', function () {
            submitNpmForm('installMissing', installMissingButton.dataset.id, installMissingButton);
        });
    });
    Array.from(npmPruneExtraneousButtons).forEach(function (pruneExtraneousButton) {
        if (pruneExtraneousButton.dataset.listenerAdded) return;
        pruneExtraneousButton.dataset.listenerAdded = "true";
        pruneExtraneousButton.addEventListener('click', function () {
            submitNpmForm('prune', pruneExtraneousButton.dataset.id, pruneExtraneousButton);
        });
    });
    Array.from(removeDependencyButtons).forEach(function (removeDependencyButton) {
        if (removeDependencyButton.dataset.listenerAdded) return;
        removeDependencyButton.dataset.listenerAdded = "true";
        removeDependencyButton.addEventListener('click', function () {
            submitNpmForm('removeDependency', removeDependencyButton.dataset.id, removeDependencyButton, removeDependencyButton.dataset.dependencyName);
        })
    });
    Array.from(installNewDependencyButtons).forEach((button) => {
        if (button.dataset.listenerAdded) return;
        button.dataset.listenerAdded = "true";
        button.addEventListener('click', function () {
            let newDependencyDialogue = new DialogueBox(button.dataset.id + '-dependency-dialogue', "Install New Dependency", [
                {element: "span", innerhtml: "Enter the name of the new NPM dependency you wish to install."},
                {element: "br"},
                {element: "form", id: "dialogue-form-installdependency-" + button.dataset.id},
                {element: "input", class:"full-width", type: "text", id: "dialogue-input-" + button.dataset.id, name: "dialogue-input-" + button.dataset.id, inside: "dialogue-form-installdependency-" + button.dataset.id},
                {element: "br", inside: "dialogue-form-installdependency-" + button.dataset.id},
                {element: "button", type: "submit", innerhtml: "INSTALL", inside: "dialogue-form-installdependency-" + button.dataset.id}]);
            newDependencyDialogue.show();
            formsCreateEventListenersForNewElement(newDependencyDialogue.element);
        });
    });
}

function submitNpmForm(type, id, buttonFrom, dependency = null) {
    progressReset();
    fetch(globalThis.location.origin + "/actions/npm.php", {
        method: "POST",
        body: JSON.stringify({
            id: id,
            type: type,
            dependency: dependency
        })
    }).then(async response => {
        let responseText = await response.text();
        console.log(response.ok);
        if (response.ok && !responseText.includes("error")) {
            failed = false;
            let article = buttonFrom.parentElement;
            if (type != 'removeDependency') {
                progressProcessing();
            } else {
                progressProcessing(true);
            }
            if (type == 'installMissing') {
                RepeatFetchNpmDetailsUntilNoMissing(article.dataset.id, article);
            }
            if (type == 'prune') {
                RepeatFetchNpmDetailsUntilNoExtraneous(article.dataset.id, article);
            }
            if (type == 'removeDependency') {
                article = buttonFrom.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;
                RepeatFetchNpmDetailsUntilDoesNotContainDependency(article.dataset.id, article, dependency);
            }
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
}

function submitDeleteForm(id) {
    progressReset();
    let deleteAllFiles = false;
    try {
        deleteAllFiles = document.getElementById("dialogue-checkbox-delete-" + id).checked;
    } catch {
        deleteAllFiles = false;
    }
    console.log(id, deleteAllFiles);
    document.getElementById("delete-" + id + "-dialogue").remove();
    fetch(globalThis.location.origin + "/actions/deleteversion.php", {
        method: "POST",
        body: JSON.stringify({
            id: id,
            deleteallfiles: deleteAllFiles
        })
    }).then(async response => {
        let responseText = await response.text();
        console.log(response.ok);
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
}

function submitRevertForm(id) {
    progressReset();
    let stopAndStartThisOne = false;
    try {
        stopAndStartThisOne = document.getElementById("dialogue-checkbox-revert-" + id).checked;
    } catch {
        stopAndStartThisOne = false;
    }
    console.log(id, stopAndStartThisOne);
    document.getElementById("revert-" + id + "-dialogue").remove();
    fetch(globalThis.location.origin + "/actions/revert.php", {
        method: "POST",
        body: JSON.stringify({
            id: id,
            stopandstartthisone: stopAndStartThisOne
        })
    }).then(async response => {
        let responseText = await response.text();
        console.log(response.ok);
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
}

function submitProjectsForm(id) {
    progressReset();
    let name = document.getElementById("projects-name-" + id).value;
    let location = document.getElementById("projects-location-" + id).value;
    let mainFile = document.getElementById("projects-main-file-" + id).value;
    let doDelete = false;
    try {
        doDelete = document.getElementById("projects-delete-" + id).checked; // JavaScript 
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
        console.log(response.ok);
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
}

function submitVersionUploadForm(id) {
    progressReset();
    let directoryInput = document.getElementById("version-hidden-" + id);
    let verName = document.getElementById("version-name-" + id).value;
    let changelog = document.getElementById("version-changelog-" + id).value;
    let stopAndRestart = false;
    try {
        stopAndRestart = document.getElementById("stop-and-apply-now-" + id).checked; // ditto
    } catch {
        stopAndRestart = false;
    }
    let setToCurrentVersion = false;
    try {
        setToCurrentVersion = document.getElementById("set-to-current-version-" + id).checked; // ditto
    } catch {
        setToCurrentVersion = false;
    }
    let formData = new FormData(); // meow
    formData.append("id", id);
    formData.append("ver_name", verName);
    formData.append("changelog", changelog);
    formData.append("stop_and_restart", stopAndRestart);
    formData.append("set_to_current_version", setToCurrentVersion);
    for (const file of directoryInput.files) {
        try {
            let path = file.webkitRelativePath;
            path = path.substring(path.indexOf('/') + 1); // remove everything up to and including the first / character (so that trying to run index.js doesn't fail because it's actually in project/index.js)
            formData.append("files[]", file, path);
        } catch {
            progressFailure("Your browser does not support webkitRelativePath. Please update to one of the browsers listed <a href=\"https://developer.mozilla.org/en-US/docs/Web/API/File/webkitRelativePath#browser_compatibility\">here</a>, or newer.");
            return -1;
        }
    }
    fetch(globalThis.location.origin + "/actions/version.php", {
        method: "POST",
        body: formData,
    }).then(async response => {
        let responseText = await response.text();
        console.log(response.ok);
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
    console.log(verName, changelog, stopAndRestart, formData);
}

function submitForm(type, id) { // some old stuff uses this and i can't be bothered to change it
    console.log(type);
    if (type == "projects") {
        submitProjectsForm(id);
    }
    if (type == "version-upload") {
        submitVersionUploadForm(id);
    }
    if (type == 'revert') {
        submitRevertForm(id);
    }
}
