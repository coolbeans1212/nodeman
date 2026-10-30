<?php
// more like Microsoft® Windows®
require_once __DIR__ . "/../isAdminAndParseEnv.php";
?>
<div class="window glass active hidden" id="utilities" style="top: 10px; left: 10px;">
    <div class="title-bar">
        <div class="title-bar-text">Utilities</div>
        <div class="title-bar-controls">
            <button aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body has-space">
        <div class="shortcut-grid">
            <div class="shortcut" id="file-upload-shortcut">
                <img src="/files/images/fileupload.png" alt="A hard drive with a server in the background" class="icon">
                Upload files
            </div>
            <div class="shortcut" id="event-viewer-shortcut">
                <img src="/files/images/eventviewer.png" alt="A notebook with warning and error icons" class="icon">
                Event viewer
            </div>
        </div>
    </div>
</div>

<div class="window glass active hidden" id="file-upload" style="top: 10px; left: 10px;">
    <div class="title-bar">
        <div class="title-bar-text">Upload a file for public or key-restricted access</div>
        <div class="title-bar-controls">
            <button aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body has-space">
        <article class="no-tabs">
            <form action="javascript:utilityUploadFile()">
                <label for="utility-file-upload-hidden">
                    <div class="file-upload-box" id="utility-file-upload-clicky-thing">
                        <div>Click here to upload a file.</div>
                    </div>
                </label>
                <input class="file-upload-hidden" type="file" id="utility-file-upload-hidden">
                <div id="utility-file-upload-form-part-two" class="hidden">
                    <input id="utility-file-upload-require-key" type="checkbox">
                    <label for="utility-file-upload-require-key">Require a key to access</label><br>
                    <div id="utility-file-upload-key-input" class="hidden">
                        <label for="utility-file-upload-key">Key:</label>
                        <input id="utility-file-upload-key" type="text"><br>
                    </div>
                    <label for="utility-file-upload-link-preview">You will be able to retrieve your file from:</label>
                    <input id="utility-file-upload-link-preview" type="text" class="full-width" readonly value=""><br>
                    <button type="submit">UPLOAD</button>
                </div>
            </form>
            <br>
            <?php require_once __DIR__ . '/filesuploadedtable.php'; ?>
        </article>
    </div>
</div>
<script src="/utilities/fileupload.js"></script>


<div class="window glass active hidden" id="event-viewer" style="top: 10px; left: 10px;">
    <div class="title-bar">
        <div class="title-bar-text">Event viewer</div>
        <div class="title-bar-controls">
            <button aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body has-space">
        <article class="no-tabs" id="event-viewer-article">
            Loading...
        </article>
    </div>
</div>
<script src="/utilities/eventviewer/eventviewer.js"></script>