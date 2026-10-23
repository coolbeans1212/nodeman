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
});


let linkPreview = document.getElementById('utility-file-upload-link-preview');
linkPreview.value = window.location.protocol + '//' + window.location.hostname + '/utilities/retrieveFile.php?i=' + fileUUID;
linkPreview.scrollLeft = linkPreview.scrollWidth;
document.getElementById('utility-file-upload-key').addEventListener('change', function () {
    const firstPart = window.location.protocol + '//' + window.location.hostname + '/utilities/retrieveFile.php';
    let secondPart = '?i=' + encodeURIComponent(fileUUID);
    if (document.getElementById('utility-file-upload-key').value != '') {
        secondPart += '&k=' + encodeURIComponent(document.getElementById('utility-file-upload-key').value);
    }
    linkPreview.value = firstPart + secondPart;
    linkPreview.scrollLeft = linkPreview.scrollWidth;
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
        editDialogue.element.querySelector('#dialogue-delete-button-' + button.dataset.id).addEventListener('click', function () {deleteUploadedFile(button.dataset.id); editDialogue.remove()});
        editDialogue.element.querySelector('#dialogue-update-button-' + button.dataset.id).addEventListener('click', function () {updateUploadedFile(button.dataset.id)});
        editDialogue.element.querySelector('#dialogue-reupload-button-' + button.dataset.id).addEventListener('click', function () {reuploadUploadedFile(button.dataset.id)});

        editDialogue.show();
    });
});

function deleteUploadedFile(id) {
    console.log('delete ' + id);
}

function updateUploadedFile(id) {
    console.log('update ' + id);
}

function reuploadUploadedFile(id) {
    console.log('reupload ' + id);
}