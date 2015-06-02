<?php

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$table_name = mysql_real_escape_string($_POST['table_name']);
$field_name = mysql_real_escape_string($_POST['field_name']);
$value = $_POST['value'];
$attachment_data = array($SCHEMA[$table_name][$field_name][LINK_ATTACHMENT] => $value);

$table_identifier = SchemaManager::get_table_unique_identifier($table_name);

$link_table = $SCHEMA[$table_name][$field_name][LINK_TABLE];
$link_label = $SCHEMA[$table_name][$field_name][LINK_LABEL];
$link_where = $SCHEMA[$table_name][$field_name][LINK_WHERE];
$link_limit = $SCHEMA[$table_name][$field_name][LINK_LIMIT];
$link_sort = $SCHEMA[$table_name][$field_name][LINK_SORT];

if (is_array($link_table))
	$link_table = $link_table[$value];
if (is_array($link_label))
	$link_label = $link_label[$value];
$link_where = SchemaManager::replace_field_value($link_where, $attachment_data);

$query = "SELECT * FROM {$link_table} {$link_where} {$link_sort}";
// echo $query;
$results = $mysql->sql($query);

while ($results->has_next()) {
	$row = $results->next();
	$entity_ID = $row[$table_identifier];
	$row_label = $mysql->get_row_label($row, $link_label);

	echo "<option value=\"{$entity_ID}\">{$row_label}</option>";
}

?>