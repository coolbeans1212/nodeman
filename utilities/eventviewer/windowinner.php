<?php
require_once __DIR__ . '/../../functions/eventViewer.php';
$datetime = new DateTime();
$datetime = $datetime->format('Y-m-d H:i:s');
displayEventsTable(require_once __DIR__ . '/../../db.php', $datetime, $_GET['page'] ?? 0);
