// npmdetails.php takes a lot of time to process, so we don't wanna include that index.php, so instead we load it when the user clicks on that shortcut.

function fetchAndDisplayNpmDetails(projectName, putInElement) {
    fetch(globalThis.location.origin + "/npmdetails.php?" + new URLSearchParams({projectName: projectName}), {
        method: "GET",
    }).then(async response => {
        let responseText = await response.text();
        return responseText;
    }).then(data => {
        putInElement.innerHTML = data;
    }).catch(error => {
        console.log(error.toString());
    });

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