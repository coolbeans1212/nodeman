let eventViewerPage = 0;
let eventViewerSort = 'id';
let eventViewerIsAscending = false;

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
        fetchEventsAndPutInEventViewer(pageSelector.dataset.id, eventViewerSort, eventViewerIsAscending);
        eventViewerPage = pageSelector.dataset.id;
    }));
}

function makeSortingWork() {
    let eventViewerHeaders = document.getElementsByClassName('event-viewer-header');
    Array.from(eventViewerHeaders).forEach(function (header) {
        header.addEventListener('click', function () {
            if (header.classList.contains('indicator') && !header.classList.contains('up')) {
                fetchEventsAndPutInEventViewer(eventViewerPage, header.dataset.id, true);
                eventViewerSort = header.dataset.id;
                eventViewerIsAscending = true;
            } else {
                fetchEventsAndPutInEventViewer(eventViewerPage, header.dataset.id);
                eventViewerSort = header.dataset.id;
                eventViewerIsAscending = false;
            }
        });
    });
}

function findOnClick() {
    let findDialogue = new DialogueBox('event-viewer-find-dialogue', 'Find...', [
        {element: "form", id: "dialogue-find-event-viewer-form"},
        {element: "label", for: "dialogue-find-event-viewer-text", innerhtml: "Text to find: ", inside: "dialogue-find-event-viewer-form"},
        {element: "input", id: "dialogue-find-event-viewer-text", type: "text", placeholder: "account.logon", inside: "dialogue-find-event-viewer-form"},
        {element: "br", inside: "dialogue-find-event-viewer-form"},
        {element: "div", style: "display: flex;", id: "dialogue-find-event-viewer-form-radio-container-container", inside: "dialogue-find-event-viewer-form"},
        {element: "label", for: "dialogue-find-event-viewer-select", innerhtml: "In: ", inside: "dialogue-find-event-viewer-form-radio-container-container"},
        {element: "div", style: "display: inline; margin: 0; padding-left: 20px;", id: "dialogue-find-event-viewer-form-radio-container", inside: "dialogue-find-event-viewer-form-radio-container-container"},
        {element: "input", type: "radio", id: "dialogue-find-event-viewer-type", name: "dialogue-find-event-viewer-radio", value: "type", checked: true, inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "label", for: "dialogue-find-event-viewer-type", innerhtml: "Type", inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "br", inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "input", type: "radio", id: "dialogue-find-event-viewer-details", name: "dialogue-find-event-viewer-radio", value: "details", inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "label", for: "dialogue-find-event-viewer-details", innerhtml: "Details", inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "br", inside: "dialogue-find-event-viewer-form-radio-container"},
        {element: "button", type: "submit", innerhtml: "Find", inside: "dialogue-find-event-viewer-form"},
    ]);
    findDialogue.show();
    document.getElementById('dialogue-find-event-viewer-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const textToFind = document.getElementById('dialogue-find-event-viewer-text').value;
        const findIn = document.querySelector('input[name="dialogue-find-event-viewer-radio"]:checked').value;
        fetchEventsAndPutInEventViewer(eventViewerPage, eventViewerSort, eventViewerIsAscending, true, textToFind, findIn);
    });
}

function makeMenuBarWork() {
    let find = document.getElementById('event-viewer-menubar-find');
    console.log(find);
    find.removeEventListener('click', findOnClick);
    find.addEventListener('click', findOnClick);
}

function fetchEventsAndPutInEventViewer(page = 0, orderBy = 'id', ascending = false, find = false, textToFind = '', findIn = 'type') {
    const url = new URL(globalThis.location.origin + "/utilities/eventviewer/windowinner.php");
    url.search = new URLSearchParams({
        page: page,
        orderby: orderBy,
        ascending: ascending,
        find: find,
        texttofind: textToFind,
        findin: findIn
    });
    fetch(url, {
        method: "GET",
    }).then(async response => {
        let responseText = await response.text();
        return responseText;
    }).then(data => {
        document.getElementById("event-viewer-article").innerHTML = data;
        makeEventPagesWork();
        makeSortingWork();
        makeMenuBarWork();
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
