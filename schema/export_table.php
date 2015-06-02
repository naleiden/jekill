<?php

require_once("../passive_authentication.php");
require_once("../base/schema_manager.php");

$table = $_REQUEST['table'];
$search_for = $_REQUEST['search_for'];
$operator = $_REQUEST['op'];
$search_in = $_REQUEST['search_in'];

$filename = SchemaManager::export_table($table, $search_for, $operator, $search_in);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=" . $filename . "");

readfile($filename);

unlink($filename);

?>