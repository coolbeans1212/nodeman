let failed = false;

function formsCreateEventListenersForNewElement(element) {
    if (element.id.split("-")[0] == 'revert') {
        let formelement = document.getElementById('dialogue-form-revert-' + element.id.split("-")[1]);
        formelement.addEventListener('click', function(event) {
            event.preventDefault();
            submitRevertForm(element.id.split("-")[1]);
        });
    }
}

function submitRevertForm(id) {
    progressReset();
    let stopAndStartThisOne = false;
    try {
        stopAndStartThisOne = document.getElementById("dialogue-checkbox-" + id).checked;
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
