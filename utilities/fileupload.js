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

document.getElementById('utility-file-upload-hidden').addEventListener('change', function () {
    document.getElementById('utility-file-upload-form-part-two').classList.remove('hidden');
});

const fileUUID = crypto.randomUUID();
let linkPreview = document.getElementById('utility-file-upload-link-preview');
linkPreview.value = window.location.protocol + '//' + window.location.hostname + '/utilities/retrieveFile.php?i=' + fileUUID;
linkPreview.scrollLeft = linkPreview.scrollWidth;
document.getElementById('utility-file-upload-key').addEventListener('change', function () {
    const firstPart = window.location.protocol + '//' + window.location.hostname + '/utilities/retrieveFile.php';
    let secondPart = '?i=' + encodeURIComponent(crypto.randomUUID());
    if (document.getElementById('utility-file-upload-key').value != '') {
        secondPart += '&k=' + encodeURIComponent(document.getElementById('utility-file-upload-key').value);
    }
    linkPreview.value = firstPart + secondPart;
    linkPreview.scrollLeft = linkPreview.scrollWidth;
});

