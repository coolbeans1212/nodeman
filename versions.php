<?php

require_once __DIR__ . '/isAdminAndParseEnv.php';

function getCountOfVersionsForProject($projectId) {
    global $mysqli;
    $sql = "SELECT COUNT(*) FROM versions WHERE project_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc();
    return $count['COUNT(*)'];
}

function getAllVersions($projectId) {
    global $mysqli;
    $sql = "SELECT * FROM versions WHERE project_id = ? ORDER BY id DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $versions = [];
    while ($row = $result->fetch_assoc()) {
        $versions[] = $row;
    }
    return $versions;
}

function generateVersionBox($versionAssocArray) {
    ?><div class="version">
        <span class="version-name-bg"><?php echo $versionAssocArray['name']; ?></span>
            <div class="version-inner">
                <div class="version-inner-left">
                    <span class="version-name"><?php echo $versionAssocArray['name']; ?></span><br>
                    <span class="version-datetime">Uploaded <?php echo $versionAssocArray['upload_time']; ?> <br> <?php
                    if (!$versionAssocArray['last_used']) {
                        echo 'Never used';
                    } else {
                        echo 'Last used ' . $versionAssocArray['last_used'];
                    } ?></span><br>
                    <span class="version-changelog">„<?php echo $versionAssocArray['changelog']; ?>“</span> <!-- i like these types of quotation marks -->
                </div>
                <div class="version-inner-right">
                    <button id="revert-<?php echo $versionAssocArray['id']; ?>" class="revert-button">REVERT TO</button>
                    <a href="/actions/zipversionupload.php?id=<?php echo $versionAssocArray['id'];?>"><button id="download-<?php echo $versionAssocArray['id']; ?>">DOWNLOAD</button></a>
                    <button id="delete-<?php echo $versionAssocArray['id']; ?>" class="delete-button">DELETE</button>
                </div>
            </div>
        </div><br><?php
}

function makePageOfVersionBoxes($projectId, $page = 0, $boxesPerPage = 5) {
    $versions = getAllVersions($projectId);
    $versions = array_values($versions);
    for ($i = $page * $boxesPerPage; $i < ($page+1)*$boxesPerPage; $i++) {
        if (isset($versions[$i])) {
            generateVersionBox($versions[$i]);
        } else {
            break;
        }
    }
}

function makeVersionPages($projectId, $boxesPerPage = 1) {
    $count = getCountOfVersionsForProject($projectId);
    $numberPagesNeeded = ceil($count / $boxesPerPage);
    for ($i = 0; $i < $numberPagesNeeded; $i++) {
        ?><div class="<?php if ($i == 0) { echo 'current-page'; } else { echo 'inactive-page'; }?>" id="page-proj<?php echo $projectId;?>-<?php echo $i;?>"><?php makePageOfVersionBoxes($projectId, $i, $boxesPerPage); ?></div><?php
    } // apparently they way i use the id attribute sucks but the guys on Slack won't tell me why :(
    ?><div class="page-selector-wrapper"><?php
    for ($i = 0; $i < $numberPagesNeeded; $i++) {
        ?><div class="<?php if ($i == 0) { echo 'selected-'; }?>page-selector" id="selector-proj<?php echo $projectId;?>-<?php echo $i; ?>"><?php echo $i;?></div><?php
    }
    ?></div><?php
}
