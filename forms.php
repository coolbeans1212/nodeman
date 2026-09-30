<?php

function createProjectsForm($new = false, $id = 0, $name = '', $location = '', $mainFile = '') {
    ?>
    <form action="javascript:submitForm('projects', <?php echo $id;?>)">
        <label for="projects-name-<?php echo $id;?>">Name</label>
        <input id="projects-name-<?php echo $id;?>" type="text" value="<?php echo $name;?>"><br>
        <label for="projects-location-<?php echo $id;?>">Filepath</label>
        <input id="projects-location-<?php echo $id;?>" type="text" value="<?php echo $location;?>"><br>
        <label for="projects-main-file-<?php echo $id;?>">Main file</label>
        <input id="projects-main-file-<?php echo $id;?>" type="text" value="<?php echo $mainFile;?>"><br>
        <?php if ($new !== true) { ?>
            <details>
                <summary>Danger zone</summary>
                <input type="checkbox" id="projects-delete-<?php echo $id;?>">
                <label for="projects-delete-<?php echo $id;?>">Delete project</label>
            </details>
        <?php } ?>
        <hr>
        <button type="submit">Submit</submit>
    </form>
    <?php
}

function createDirectoryUploadForm($id = 0) {
    ?>
    <form action="javascript:submitForm('version-upload', <?php echo $id;?>)">
        <div class="directory-upload-box" id="version-box-<?php echo $id;?>">
            <div>Click here to upload a new version.</div>
        </div>
        <input class="directory-upload-hidden" type="file" id="version-hidden-<?php echo $id;?>" directory webkitdirectory mozdirectory multiple>
        <div class="directory-upload-part-two" id="directory-upload-part-two-<?php echo $id;?>">
            <label for="version-name-<?php echo $id;?>">Version name:</label>
            <input type="text" name="version-name-<?php echo $id; ?>" id="version-name-<?php echo $id;?>" placeholder="v1.0.4-rc"><br>
            <label for="version-changelog-<?php echo $id;?>">Changelog:</label><br>
            <textarea name="version-changelog-<?php echo $id;?>" id="version-changelog-<?php echo $id;?>" class="version-changelog" style="width: 100%;" maxlength="500" placeholder="Fixed bugs #10, #12, #13, merged #14. Added new slash-command. Quality-of-life improvements."></textarea><br>
            <span class="version-changelog-word-count"><span id="version-changelog-word-count-<?php echo $id;?>">0</span> of 500 characters used.</span><br>
            <input type="checkbox" name="stop-and-apply-now-<?php echo $id;?>" class="stop-and-apply-now-checkbox" id="stop-and-apply-now-<?php echo $id;?>" checked>
            <label for="stop-and-apply-now-<?php echo $id;?>">Stop current version and start this one.</label><br>
            <div class="hidden" id="hidden-unless-stop-and-apply-now-is-unchecked-<?php echo $id;?>">
                <input type="checkbox" name="set-to-current-version-<?php echo $id;?>" id="set-to-current-version-<?php echo $id;?>" checked>
                <label for="set-to-current-version-<?php echo $id;?>">Set to current version.</label><br>
            </div>
            <button type="submit">UPLOAD</button>
        </div>
    </form>
    <?php
}
