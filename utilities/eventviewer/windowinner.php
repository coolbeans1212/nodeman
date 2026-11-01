<?php
require_once __DIR__ . '/../../functions/eventViewer.php';
$datetime = new DateTime();
$datetime = $datetime->format('Y-m-d H:i:s');
if (isset($_GET['ascending']) && $_GET['ascending'] == 'false') {
    $ascending = false;
} elseif (!isset($_GET['ascending'])) {
    $ascending = false;
} else {
    $ascending = true;
}
displayEventsTable(require_once __DIR__ . '/../../db.php', $datetime, $_GET['page'] ?? 0, $_GET['orderby'] ?? 'id', 10, $ascending ?? false);
