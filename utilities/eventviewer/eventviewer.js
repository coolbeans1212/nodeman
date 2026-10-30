// pagination
function makeEventPagesWork() {
    let eventPageSelectors = document.querySelectorAll('[data-type="page-selector-eventviewer"]');
    Array.from(eventPageSelectors).forEach((pageSelector) => pageSelector.addEventListener('click', function () {
        let currentPageSelectors = document.getElementsByClassName("selected-page-selector");
        Array.from(currentPageSelectors).forEach(function (currentPageSelector) {
            if (currentPageSelector.dataset.type == pageSelector.dataset.type) { // ditto
                currentPageSelector.classList.add("page-selector"); currentPageSelector.classList.remove("selected-page-selector");
            }
        });
        pageSelector.classList.add("selected-page-selector"); pageSelector.classList.remove("page-selector");
        fetchEventsAndPutInEventViewer(pageSelector.dataset.id);
    }));
}

// event viewer
function fetchEventsAndPutInEventViewer(page = 0) {
    fetch(globalThis.location.origin + "/utilities/eventviewer/windowinner.php?page=" + page, {
        method: "GET",
    }).then(async response => {
        let responseText = await response.text();
        return responseText;
    }).then(data => {
        document.getElementById("event-viewer-article").innerHTML = data;
        makeEventPagesWork();
    }).catch(error => {
        console.log(error.toString());
    });
}
let eventsLoaded = false;
let eventViewerWindow = document.getElementById('event-viewer');
let isEventViewerVisibleObserver = new MutationObserver(function() {
    if (!eventViewerWindow.classList.contains("hidden") && !eventsLoaded) {
        fetchEventsAndPutInEventViewer();
        eventsLoaded = true;
    }
});
isEventViewerVisibleObserver.observe(eventViewerWindow, {attributes: true, attributeFilter: ["class"]});