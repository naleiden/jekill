<?php

require_once("../base/database_schema.php");
require_once("../base/HTML.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$table_name = mysql_real_escape_string($_REQUEST['table_name']);
$field_name = mysql_real_escape_string($_REQUEST['field_name']);
$entity_ID = mysql_real_escape_string($_REQUEST['entity_ID']);

$field = $SCHEMA[$table_name][$field_name];
$field_type = $field[FIELD_TYPE];
$link_table = $field[LINK_TABLE];

$html = new HTML();

$subrecord_header = $html->div()->class("subrecord_header")->content($field[FIELD_LABEL]);
if ($link_table != $table_name)
	$rows_header = SchemaManager::row_header($link_table, true);
$rows_div = $html->div()->class("subrecord_rows")->add($rows_header);

if ($field_type == LINK_N_TO_N) {
	$map_table = SchemaManager::get_map_table_name($table_name, $link_table, $field_name);
	$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
	$query = "SELECT {$link_table}.* FROM {$map_table}
			LEFT JOIN {$link_table} ON ($map_table.{$link_table}_ID = {$link_table}.{$link_table_identifier})
			WHERE {$map_table}.{$table_name}_ID = '{$entity_ID}'";
}
else if ($field_type == LINK_MUTUAL) {
	$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
	$map_table = SchemaManager::get_map_table_name($table_name, $link_table, $field_name);
	$query = "SELECT {$table_name}.* FROM {$map_table}
			LEFT JOIN {$table_name} ON ({$table_name}.{$table_identifier} = {$map_name}.one_ID OR {$table_name}.{$table_identifier} = {$map_name}.two_ID)
			WHERE {$table_name}.{$table_identifier} != '{$entity_ID}'";
}
else {	// LINK_ONE_TO_N
	$link_field = $field[LINK_FIELD];
	$query = "SELECT * FROM {$link_table} WHERE {$link_field} = '{$entity_ID}'";
}

if (isset($SCHEMA[$table_name][$field_name][LINK_SORT]))
	$query .= " ORDER BY {$link_table}." . $SCHEMA[$table_name][$field_name][LINK_SORT];

$results = $mysql_connection->sql($query);

$i = 0;
while ($results->has_next()) {
	$row = $results->next();
	$row_class = ($i++%2) ? "row_odd" : "row_even";
	$row_div = SchemaManager::row($link_table, $row, $row_class);
	$rows_div->add($row_div);
}

$subrecord_rows = $html->div()->add($subrecord_header)->add($rows_div);

echo $subrecord_rows->html();

?>