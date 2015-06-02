<?php

require_once("../base/database_schema.php");
require_once("../base/HTML.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

// $parent_ID = mysql_real_escape_string($_REQUEST['parent_ID']);
$record_ID = mysql_real_escape_string($_REQUEST['record_ID']);
$table_name = mysql_real_escape_string($_REQUEST['table_name']);
$field_name = mysql_real_escape_string($_REQUEST['field_name']);
$record_num = mysql_real_escape_string($_REQUEST['record_num']);
$suffix = mysql_real_escape_string($_REQUEST['suffix']);

$html = new HTML();

$record = array();
if ($record_ID != "") {
	$link_table_name = $SCHEMA[$table_name][$field_name][LINK_TABLE];
	$table_identifier = SchemaManager::get_table_unique_identifier($link_table_name);
	$query = "SELECT * FROM {$link_table_name} WHERE {$table_identifier} = '{$record_ID}'";
	$results = $mysql_connection->sql($query);
	if ($results->has_next()) {
		$record_data = $results->next();
	}
}
/* For adding One-to-N records with a SUBTABLE_DEFAULT. Not implemented yet. */
// $parent_table_identifier = SchemaManager::get_table_unique_identifier($link_table_name);
// $record[$parent_table_identifier] = $parent_ID;

$record_form = SchemaManager::get_subtable_form($record_ID, $table_name, $field_name, $record_num, $record_data, $suffix);

/* Strings in Javascript can have no newlines... */
/*	OLD JSON way of passing back both form and validation script. Now validation script is generated with parent form
$form_html = str_replace("\n", "", $record_form->html());
$form_html = str_replace("\r", "", $form_html);
$form_html = str_replace("'", "\'", $form_html);

$script = str_replace("\n", "", $html->script->html());
$script = str_replace("\r", "", $script);
$script = str_replace("'", "\'", $script);

$row_class = ($record_num%2) ? "row_odd" : "row_even";

echo "{ form: '" . $form_html . "', script: '" . $script . "' }";
*/

/* Now just pass back the form. */
echo $record_form->html();

?>