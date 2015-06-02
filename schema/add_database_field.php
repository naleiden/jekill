<?php

include_once("../passive_authentication.php");

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.inc");
require_once("../base/schema_manager.php");

$table_name = mysql_real_escape_string($_REQUEST['table_name']);

$response = "";
$i = 1;
while (isset($_REQUEST["field_name_{$i}"])) {
	$field_name = mysql_real_escape_string($_REQUEST["field_name_{$i}"]);
	$field = $SCHEMA[$table_name][$field_name];
	$field_type = $field[FIELD_TYPE];

	if ($field_type == LINK_N_TO_N || $field_type == LINK_MUTUAL) {
		$link_table = $SCHEMA[$table_name][$field_name][LINK_TABLE];
		SchemaManager::create_map_table($table_name, $field_name);
		$response .= "<LI>Added map table between '" . $SCHEMA[$table_name][TABLE_LABEL] . "' and '" . $SCHEMA[$link_table][TABLE_LABEL] . "'";
	}
	else {
		$field_database_type = $DATATYPES[$field_type];

		if ($field_type == SET) {
			$set_options = implode("','", array_keys($field[FIELD_OPTIONS]));
			$field_database_type .= "('{$set_options}')";
		}

		if ($field_database_type == "")
			$field_database_type = $field_type;
		$query = "ALTER TABLE {$table_name} ADD COLUMN {$field_name} {$field_database_type}";
// echo $query;
		$mysql_connection->query($query);
		$response .= "<LI>Added column '{$field_name} {$field_database_type}'";
	}
	$i++;
}

if ($response != "")
	echo "The following changes have been made to the table '{$table_name}':<P><UL>{$response}</UL>";

?>