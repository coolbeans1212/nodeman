<?php

function createProjectsForm($name = '', $location = '', $main_file = '') {
    ?>
    <form action="javascript:submitForm()">
        <label for="name">Name</label>
        <input id="name" type="text" value="<?php echo $name;?>"><br>
        <label for="location">Filepath</label>
        <input id="location" type="text" value="<?php echo $location;?>"><br>
        <label for="main_file">Main file</label>
        <input id="main_file" type="text" value="<?php echo $main_file;?>"><br>
        <button type="submit">Submit</submit>
    </form>
    <?php
}

