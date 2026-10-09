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

function getLocationOfCurrentVersion($projectId) {
    global $mysqli;
    $sql = "SELECT location FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();
    $location = $location['location'];
    $location = $location . DIRECTORY_SEPARATOR . getCurrentVersionOfProject($projectId) . DIRECTORY_SEPARATOR;
    return $location;
}

function getDetails($projectId) {
    $locationOfVersion = getLocationOfCurrentVersion($projectId);
    $cmd = sprintf("cd %s && npm list --json", escapeshellarg($locationOfVersion));
    exec($cmd, $cmdout);
    $npmDetails = "";
    foreach($cmdout as $line) {
        $npmDetails .= $line;
    }
    $npmDetails = json_decode($npmDetails, true);
    return $npmDetails;
}

function echoIfIsset($checkIsset, $echoMe, $alsoCheckNotFalse = false) { // To prevent a warning from being emitted, $checkIsset should be of the form `$variable ?? null`
    if (isset($checkIsset) && !$alsoCheckNotFalse) {
        echo $echoMe;
        return 0;
    }
    if (isset($checkIsset) && $alsoCheckNotFalse && $checkIsset) {
        echo $echoMe;
        return 0;
    }
}

function makeDetailsTable($projectDetails, $projectId) {
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
        ?><tr><td data-id="<?php echo $dependencyName;?>"><?php echo $dependencyName;?></td>
        
        <td>
        <?php
        echoIfIsset($dependency['required'] ?? null, $dependency['required'] ?? null . ' required');
        echoIfIsset($dependency['version'] ?? null, $dependency['version'] ?? null); ?>
        </td>
        <td>
        <?php
        echoIfIsset($dependency['resolved'] ?? null, $dependency['resolved'] ?? null) ; ?>
        </td>

        <td><?php
        echoIfIsset($dependency['overriden'] ?? null, 'Overriden. ', true);
        echoIfIsset($dependency['missing'] ?? null, 'Missing. ', true);
        echoIfIsset($dependency['extraneous'] ?? null, 'Extraneous. ', true); ?>
        </td>
        
        <td>
            <?php if (!isset($dependency['missing'])) { ?>
            <button class="thin-button npm-remove-button" data-id="<?php echo $projectId; ?>" data-dependency-name="<?php echo $dependencyName; ?>">Remove</button>
            <?php } ?>
        </td>
        <?php
    }
    ?>

</tbody></table></div><?php
}

function displayDetails($projectId) {
    $projectDetails = getDetails($projectId);
    if (!$projectDetails) {
        echo 'There is no NPM environment for this project.';
        return 0;
    }
    if (isset($projectDetails['error'])) {
        echo '<div class="bad">' . nl2br($projectDetails['error']['summary']) . '</div><br>';
    }
    makeDetailsTable($projectDetails, $projectId);
    $isAnyDependencyMissing = false;
    $isAnyDependencyExtraneous = false;
    foreach ($projectDetails['dependencies'] as $dependency) {
        if (isset($dependency['missing']) && $dependency['missing']) {
            $isAnyDependencyMissing = true;
        }
        if (isset($dependency['extraneous']) && $dependency['extraneous']) {
            $isAnyDependencyExtraneous = true;
        }
    }
    ?>
    <?php if ($isAnyDependencyMissing) { ?>
        <button class="npm-install-missing-button" data-id="<?php echo $projectId;?>">Install Missing</button>
    <?php } ?>
    <?php if ($isAnyDependencyExtraneous) { ?>
        <button class="npm-prune-extraneous-button" data-id="<?php echo $projectId;?>">Prune Extraneous Dependencies</button>
    <?php } ?>
    <button class="npm-install-new-dependency" data-id="<?php echo $projectId;?>">Install New Dependency</button>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    displayDetails(getProjectIdFromName($_GET['projectName']));
}
