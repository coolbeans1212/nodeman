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

