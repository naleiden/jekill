<?php

include("../passive_authentication.php");

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");

if (!isset($_REQUEST['table_name']) || !isset($_REQUEST['field_name']))
	exit;

$table_name = $_REQUEST['table_name'];
$field_name = $_REQUEST['field_name'];

unset($SCHEMA[$table_name][$field_name]);

$query = "ALTER TABLE {$table_name} DROP COLUMN {$field_name}";
$mysql_connection->query($query);

include("write_schema.php");

// header("Location: ../control_pane.php?func=edit_table&table={$table_name}");
// exit;

?>