<?php

function createProjectsForm($new = false, $name = '', $location = '', $mainFile = '') {
    ?>
    <form action="javascript:submitForm('projects')">
        <label for="projects-name">Name</label>
        <input id="projects-name" type="text" value="<?php echo $name;?>"><br>
        <label for="projects-location">Filepath</label>
        <input id="projects-location" type="text" value="<?php echo $location;?>"><br>
        <label for="projects-main-file">Main file</label>
        <input id="projects-main-file" type="text" value="<?php echo $mainFile;?>"><br>
        <?php if ($new !== true) { ?>
            <details>
                <summary>Danger zone</summary>
                <input type="checkbox" id="projects-delete">
                <label for="projects-delete">Delete project</label>
            </details>
        <?php } ?>
        <hr>
        <button type="submit">Submit</submit>
    </form>
    <?php
}

