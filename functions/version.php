<?php
function getVersionInformation(mysqli $mysqli, int $id): array {
    $sql = "SELECT id,name,upload_time,changelog,project_id,last_used FROM versions WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProjectInformation(mysqli $mysqli, int $id): array {
    $sql = "SELECT id,name,location,main_file,current_version FROM projects WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
