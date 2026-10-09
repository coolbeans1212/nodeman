// npmdetails.php takes a lot of time to process, so we don't wanna include that index.php, so instead we load it when the user clicks on that shortcut.
let npmHasLoaded = false;
function fetchAndDisplayNpmDetails(projectName, putInElement) {
    npmHasLoaded = true;
    fetch(globalThis.location.origin + "/npmdetails.php?" + new URLSearchParams({projectName: projectName}), {
        method: "GET",
    }).then(async response => {
        let responseText = await response.text();
        return responseText;
    }).then(data => {
        putInElement.innerHTML = data;
        if (npmHasLoaded) {
            createEventListenersForNpmCommandsOnLoad(); // in forms.js
        }
    }).catch(error => {
        console.log(error.toString());
    });

}

function RepeatFetchNpmDetailsUntilNoMissing(projectName, putInElement) {
    let fetchLoop = setInterval(function () {
        fetchAndDisplayNpmDetails(projectName, putInElement);
        if (document.getElementsByClassName('npm-install-missing-button').length == 0) {
            clearInterval(fetchLoop);
        }
    }, 5000);
}
function RepeatFetchNpmDetailsUntilNoExtraneous(projectName, putInElement) {
    let fetchLoop = setInterval(function () {
        if (document.getElementsByClassName('npm-prune-extraneous-button').length == 0) {
            clearInterval(fetchLoop);
            return 0;
        }
        fetchAndDisplayNpmDetails(projectName, putInElement);
    }, 5000);
}
function RepeatFetchNpmDetailsUntilDoesNotContainDependency(projectName, putInElement, dependency) {
    let fetchLoop = setInterval(function () {
        if (document.querySelector('[data-id="' + dependency + '"]') == null) {
            clearInterval(fetchLoop);
            return 0;
        }
        fetchAndDisplayNpmDetails(projectName, putInElement);
    }, 5000);
}

let npmWindow = document.getElementById("npm");
let npmTabs = document.getElementsByClassName("npm-tabpanel-contents");
let npmDetailsLoaded = false;
let isVisibleObserver = new MutationObserver(function() {
    if (!npmWindow.classList.contains("hidden") && !npmDetailsLoaded) {
        Array.from(npmTabs).forEach(function (npmTab) {
            fetchAndDisplayNpmDetails(npmTab.dataset.id, npmTab.parentElement.querySelector('#' + npmTab.id));
        });
        npmDetailsLoaded = true;
    }
});
isVisibleObserver.observe(npmWindow, {attributes: true, attributeFilter: ["class"]});