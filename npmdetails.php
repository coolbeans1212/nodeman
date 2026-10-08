<?php
require_once __DIR__ . '/versions.php';

function getProjectIdFromName($projectName) {
    global $mysqli;
    $sql = "SELECT id FROM projects WHERE name = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $projectName);
    $stmt->execute();
    $result = $stmt->get_result();
    $projectId = $result->fetch_assoc();
    $projectId = $projectId['id'];
    return $projectId;
}

function getDetails($projectId) {
    global $mysqli;
    $sql = "SELECT location FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();
    $location = $location['location'];

    $locationOfVersion = $location . DIRECTORY_SEPARATOR . getCurrentVersionOfProject($projectId) . DIRECTORY_SEPARATOR;
    $cmd = sprintf("cd %s && npm list --json", $locationOfVersion);
    exec($cmd, $cmdout);
    $npmDetails = "";
    foreach($cmdout as $line) {
        $npmDetails .= $line;
    }
    $npmDetails = json_decode($npmDetails, true);
    return $npmDetails;
}

function displayDetails($projectId) {
    $projectDetails = getDetails($projectId);
    var_dump($projectDetails);
    if (!$projectDetails) {
        echo 'There is no NPM environment for this project.';
        return 0;
    }
    if (isset($projectDetails['error'])) {
        echo '<div class="bad">' . nl2br($projectDetails['error']['summary']) . '</div><br>';
    }
    ?>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Version</th>
                <th>Source</th>
                <th>Other Information</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ($projectDetails['dependencies'] as $dependencyName => $dependency) {
        ?><tr><td><?php echo $dependencyName;?></td>
        
        <td>
        <?php
        if (isset($dependency['required'])) { echo $dependency['required'] . ' required';}
        if (isset($dependency['version'])) { echo $dependency['version'];} ?>
        </td>
        <td>
        <?php
        if (isset($dependency['resolved'])) { echo $dependency['resolved'];} ?>
        </td>

        <td><?php
        if (isset($dependency['overridden']) && $dependency['overridden']) {
            echo 'Overridden. ';
        }
        if (isset($dependency['extraneous']) && $dependency['extraneous']) {
            echo 'Extraneous. ';
        }
        if (isset($dependency['missing']) && $dependency['missing']) {
            echo 'Missing. ';
        }
    }
    ?></tbody></table></div><?php
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    displayDetails(getProjectIdFromName($_GET['projectName']));
}
