let eventViewerPage = 0;
let sort = 'id';
let ascending = false;

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
        fetchEventsAndPutInEventViewer(pageSelector.dataset.id, sort, ascending);
        eventViewerPage = pageSelector.dataset.id;
    }));
}

function makeSortingWork() {
    let eventViewerHeaders = document.getElementsByClassName('event-viewer-header');
    Array.from(eventViewerHeaders).forEach(function (header) {
        header.addEventListener('click', function () {
            if (header.classList.contains('indicator') && !header.classList.contains('up')) {
                fetchEventsAndPutInEventViewer(eventViewerPage, header.dataset.id, true);
                sort = header.dataset.id;
                ascending = true;
            } else {
                fetchEventsAndPutInEventViewer(eventViewerPage, header.dataset.id);
                sort = header.dataset.id;
                ascending = false;
            }
        });
    });
}


function fetchEventsAndPutInEventViewer(page = 0, orderBy = 'id', ascending = false) {
    fetch(globalThis.location.origin + "/utilities/eventviewer/windowinner.php?page=" + page + "&orderby=" + orderBy + "&ascending=" + ascending, {
        method: "GET",
    }).then(async response => {
        let responseText = await response.text();
        return responseText;
    }).then(data => {
        document.getElementById("event-viewer-article").innerHTML = data;
        makeEventPagesWork();
        makeSortingWork();
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
