<?php

include("../passive_authentication.php");

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");

$table = $_REQUEST['table_name'];

$i = 1;
while (isset($_REQUEST["field_{$i}"])) {
	/* Cursory check for field definition validity
	if ($_REQUEST["field_{$i}_type"] != "" && $_REQUEST["field_{$i}_name"] != "") {
		$field_name = $_REQUEST["field_{$i}_name"];
		$field_type = $_REQUEST["field_{$i}_type"];
		$SCHEMA[$table_name][$field_name] = array(FIELD_NAME => $field_name, FIELD_TYPE => $field_type);

		$j = 1;
		while (isset($_REQUEST["field_{$i}_option_{$j}"])) {
			$option_type = $_REQUEST["field_{$i}_option_{$j}"];
			$option_value = $_REQUEST["field_{$i}_option_{$j}_value"]
			$SCHEMA[$table_name][$field_name][$option_type] = $option_value;
		}
		$query = "ALTER TABLE {$table_name} ADD COLUMN {$field_name} " . $DATATYPES[$field_type];
		$mysql_connection->query($query);
	}
	$i++;
}
unset($SCHEMA[$table_name][$field_name]);

$query = "ALTER TABLE {$table_name} DROP COLUMN {$field_name}";
$mysql_connection->query($query);

include("write_schema.php");

header("Location: ../control_pane.php?func=edit_table&table={$table_name}");
exit;

?>