<?php

require_once("../base/database_schema.php");
require_once("../base/schema_manager.php");
require_once("../base/mysql_connection.php");

$table_name = $_REQUEST['table_name'];
$field_name = $_REQUEST['field_name'];
$value = $_REQUEST['value'];
$entity_ID = $_REQUEST['entity_ID'];

$table_unique_identifier = SchemaManager::get_table_unique_identifier($table_name);
$count = $mysql->count($table_name, $field_name, "WHERE {$field_name} = '{$value}' AND {$table_unique_identifier} != '{$entity_ID}'");

if ($count > 0) {
	$field_label = $SCHEMA[$table_name][$field_name][FIELD_LABEL];
	echo "{ \"unique\": 0, \"message\": \"The {$field_label} '" . htmlentities($value) . "' already exists in the system. Please choose another, unique {$field_label}.\" }";
}
else {
	echo "{ \"unique\": 1, \"count\": {$count} }";
}

?>