<?php

// include("../passive_authentication.php");

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");

$table_name = $_REQUEST['table_name'];
$table_label = $_REQUEST['table_label'];
$table_sort = $_REQUEST['table_sort'];
$num_fields = $_REQUEST['num_fields'];

if ($table_name == "") {	// New table
	$table_name = $table_label;
	$table_name = strtolower($table_name);
	$table_name = str_replace(" ", "_", $table_name);
}

$OLD_TABLE_SCHEMA = $SCHEMA[$table_name];
unset($SCHEMA[$table_name]);

$SCHEMA[$table_name] = array(TABLE_LABEL => $table_label, TABLE_SORT => $table_sort);

for ($i=1; $i<=$num_fields; $i++) {
	$field_name = $_REQUEST["name_{$i}"];
	if ($field_name == "")
		continue;

	/* If this is an existing table, but a new field. */
	if ($OLD_TABLE_SCHEMA != "" && !isset($OLD_TABLE_SCHEMA[$field_name])) {
		$query = "ALTER TABLE {$table_name} ADD COLUMN {$field_name} " . $DATATYPES[$_REQUEST["type_{$i}"]];
		$mysql_connection->query($query);
	}

	$SCHEMA[$table_name][$field_name] = array(
					FIELD_NAME => $field_name,
					FIELD_TYPE => $_REQUEST["type_{$i}"],
					FIELD_LABEL => $_REQUEST["label_{$i}"],
					FIELD_REQUIRED => $_REQUEST["required_{$i}"]
				);

	$num_field_modifiers = $_REQUEST["num_modifiers_{$i}"];
	for ($j=1; $j<=$num_modifiers; $j++) {
		$modifier = $_REQUEST["modifier_{$i}_{$j}"];
		$modifier_value = $_REQUEST["modifier_{$i}_{$j}_value"];
		$SCHEMA[$table_name][$field_name][$modifier] = $modifier_value;
	}
}

/* If this is a new table, create it. */
if ($OLD_TABLE_SCHEMA == "") {
	// echo $table_name; exit;
	$result = SchemaManager::init_table($table_name);
}

include("write_schema.php");

header("Location: ../control_panel.php?func=schema&table={$table_name}");
exit;

?>