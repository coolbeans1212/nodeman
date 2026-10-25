function submitPersonalisationControlPanelForm() {
    let failed = false;
    const wallpaper = document.getElementById('personalisation-wallpaper').value;
    const font = document.getElementById('personalisation-font').value;
    const css = document.getElementById('personalisation-css-version').value;
    fetch(globalThis.location.origin + "/actions/control.php", {
        method: "POST",
        body: JSON.stringify({
            type: 'personalisation',
            wallpaper: wallpaper,
            font: font,
            css: css
        })
    }).then(async response => {
        let responseText = await response.text();
        if (response.ok && !responseText.includes("error")) {
            failed = false;
        } else {
            failed = true;
            progressReset();
            progressFailure()
        }
        return responseText;
    }).then(data => {
        if (failed) {
            progressFailure(data);
        } else {
            reloadCSS();
        }
    }).catch(error => {
        failed = true;
        progressFailure(error.toString());
    });
}

function submitLayoutControlPanelForm() {
    let failed = false;
    progressReset();
    const versionsPerPage = document.getElementById('layout-versions-per-page').value;
    fetch(globalThis.location.origin + "/actions/control.php", {
        method: "POST",
        body: JSON.stringify({
            type: 'layout',
            versionsperpage: versionsPerPage,
        })
    }).then(async response => {
        let responseText = await response.text();
        if (response.ok && !responseText.includes("error")) {
            failed = false;
            progressSuccess(true);
        } else {
            failed = true;
            progressFailure()
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

document.getElementById('layout-versions-per-page').addEventListener('input', function() {
    document.getElementById('layout-versions-per-page-numeral').textContent = document.getElementById('layout-versions-per-page').value;
});