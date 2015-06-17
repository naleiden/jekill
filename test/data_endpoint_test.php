<?php

require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$schema = "subject_group,subjects,lessons{visible:1}";
$data = SchemaManager::data($schema);

echo "<pre>";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

?>