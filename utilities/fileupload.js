// ok so... there's a skibidi toilet
// but what if there was a skibidet? (i.e. skibidi bidet)

document.getElementById('utility-file-upload-clicky-thing').addEventListener('click', function () {
    document.getElementById('utility-file-upload-hidden').click();
});

document.getElementById('utility-file-upload-require-key').addEventListener('click', function (event) {
    if (document.getElementById('utility-file-upload-require-key').checked) {
        document.getElementById('utility-file-upload-key-input').classList.remove("hidden");
    } else {
        document.getElementById('utility-file-upload-key-input').classList.add("hidden");
    }
});

let fileUUID = crypto.randomUUID();

document.getElementById('utility-file-upload-hidden').addEventListener('change', function () {
    document.getElementById('utility-file-upload-form-part-two').classList.remove('hidden');
    fileUUID = crypto.randomUUID();
    updateLinkPreview();
});

function updateLinkPreview() {
    const linkPreview = document.getElementById('utility-file-upload-link-preview');
    const firstPart = window.location.protocol + '//' + window.location.hostname + '/utilities/retrieveFile.php';
    let secondPart = '?i=' + encodeURIComponent(fileUUID);
    if (document.getElementById('utility-file-upload-key').value != '') {
        secondPart += '&k=' + encodeURIComponent(document.getElementById('utility-file-upload-key').value);
    }
    linkPreview.value = firstPart + secondPart;
    linkPreview.scrollLeft = linkPreview.scrollWidth;
}

document.getElementById('utility-file-upload-key').addEventListener('change', function () {
    updateLinkPreview();
});


Array.from(document.getElementsByClassName('utility-file-upload-edit-button')).forEach(function (button) {
    button.addEventListener('click', function() {
        console.log(button.dataset.id);
        let editDialogue = new DialogueBox('edit-' + button.dataset.id, 'Edit uploaded file', [
            {element: "form", id: "dialogue-form-" + button.dataset.id},
            {element: "div", class: "space-between", id: "dialogue-button-container-" + button.dataset.id, inside: "dialogue-form-" + button.dataset.id},
            {element: "button", id: "dialogue-delete-button-" + button.dataset.id, type: "button", innerhtml: "DELETE", style: "color: red;", inside: "dialogue-button-container-" + button.dataset.id},
            {element: "button", id: "dialogue-reupload-button-" + button.dataset.id, type: "button", innerhtml: "RE-UPLOAD", inside: "dialogue-button-container-" + button.dataset.id},
            {element: "hr", inside: "dialogue-form-" + button.dataset.id},
            {element: "input", type: "file", class: "hidden", id: "dialogue-file-" + button.dataset.id, inside: "dialogue-form-" + button.dataset.id},
            {element: "input", type: "checkbox", id: "dialogue-checkbox-" + button.dataset.id, inside: "dialogue-form-" + button.dataset.id},
            {element: "label", for: "dialogue-checkbox-" + button.dataset.id, innerhtml: "Require a key to access", inside: "dialogue-form-" + button.dataset.id},
            {element: "br", inside: "dialogue-form-" + button.dataset.id},
            {element: "label", for: "dialogue-text-" + button.dataset.id, innerhtml: "Key: ", inside: "dialogue-form-" + button.dataset.id},
            {element: "input", type: "text", id: "dialogue-text-" + button.dataset.id, inside: "dialogue-form-" + button.dataset.id},
            {element: "br", inside: "dialogue-form-" + button.dataset.id},
            {element: "button", id: "dialogue-update-button-" + button.dataset.id, type: "button", innerhtml: "UPDATE", inside: "dialogue-form-" + button.dataset.id}]);
        if (button.dataset.keyRequired == '1') {
            editDialogue.element.querySelector('#dialogue-checkbox-' + button.dataset.id).checked = true;
        }
        editDialogue.element.querySelector('#dialogue-text-' + button.dataset.id).value = button.dataset.key;
        editDialogue.element.querySelector('#dialogue-delete-button-' + button.dataset.id).addEventListener('click', function () {deleteUploadedFile(button.dataset.id); editDialogue.remove();});
        editDialogue.element.querySelector('#dialogue-update-button-' + button.dataset.id).addEventListener('click', function () {updateUploadedFile(button.dataset.id); editDialogue.remove();});
        editDialogue.element.querySelector('#dialogue-reupload-button-' + button.dataset.id).addEventListener('click', function () {editDialogue.element.querySelector('#dialogue-file-' + button.dataset.id).click();});
        editDialogue.element.querySelector('#dialogue-file-' + button.dataset.id).addEventListener('change', function () {reuploadUploadedFile(button.dataset.id); editDialogue.remove();});
        editDialogue.show();
    });
});

function utilityUploadFile() {
    let failed = false;
    progressReset();
    let fileInput = document.getElementById("utility-file-upload-hidden");
    let requireKey = false;
    try {
        requireKey = document.getElementById("utility-file-upload-require-key").checked;
    } catch {
        requireKey = false;
    }
    let key = document.getElementById("utility-file-upload-key").value;
    let formData = new FormData(); // meow
    formData.append("id", fileUUID);
    formData.append("require_key", requireKey);
    formData.append("key", key);
    formData.append("file", fileInput.files[0]);
    fetch(globalThis.location.origin + "/utilities/actions/fileupload.php", {
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
        document.getElementById('utility-file-upload-form-part-two').classList.add('hidden');
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


function deleteUploadedFile(id) {
    let failed = false;
    progressReset();
    fetch(globalThis.location.origin + "/utilities/actions/fileupload.php", {
        method: "DELETE",
        body: JSON.stringify({
            id: id
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

function updateUploadedFile(id) {
    let failed = false;
    progressReset();
    let requireKey = false;
    try {
        requireKey = document.getElementById("dialogue-checkbox-" + id).checked;
    } catch {
        requireKey = false;
    }
    let key = document.getElementById("dialogue-text-" + id).value;
    fetch(globalThis.location.origin + "/utilities/actions/fileupload.php", {
        method: "PATCH",
        body: JSON.stringify({
            id: id,
            requireKey: requireKey,
            key: key
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

function reuploadUploadedFile(id) {
    let failed = false;
    progressReset();
    let fileInput = document.getElementById("dialogue-file-" + id);
    let formData = new FormData();
    formData.append("id", id);
    formData.append("file", fileInput.files[0]);
    fetch(globalThis.location.origin + "/utilities/actions/fileupload.php", {
        method: "PUT",
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
}