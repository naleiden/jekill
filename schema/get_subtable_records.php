<?php

include("../base/define.php");
include("../base/database_schema.php");

require_once("../base/HTML.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.inc");
require_once("../base/schema_manager.php");
require_once("../base/util.php");

if (!isset($html))
	$html = new HTML();

$parent_table_name = mysql_real_escape_string($_REQUEST['table_name']);
$field_name = mysql_real_escape_string($_REQUEST['field_name']);
$field = $SCHEMA[$parent_table_name][$field_name];
$table_name = $field[LINK_TABLE];
$suffix = mysql_real_escape_string($_REQUEST['suffix']);
$search = mysql_real_escape_string($_REQUEST['search']);

$delimiters = "/[^A-z0-9_]+/";	// ([^a-z]|[^A-Z]|[^0-9]|^_)*/";
$label_fields = array();
if (preg_match($delimiters, $field[LINK_LABEL], $matches)) {
	$label_parts = preg_split($delimiters, $field[LINK_LABEL]);	// , -1, PREG_SPLIT_DELIM_CAPTURE);

	$where = "";
	foreach ($label_parts AS $field_delim) {
		if ($search != "") {
			if ($where == "")
				$where .= "WHERE ";
			else $where .= " OR ";
		}

		//if (!preg_match($delimiters, $field_delim)) {
			if ($search != "")
				$where .= " {$table_name}.{$field_delim} LIKE '%{$search}%'";
			$label_fields[] = $field_delim;
		//}
	}
	// echo $where;
}
else {
	$label_fields[] = $field[LINK_LABEL];
	if ($search != "")
		$where = "WHERE " . $field[LINK_LABEL] . " LIKE '%{$search}%'";
}

$field_ID = SchemaManager::get_unique_identifier($SCHEMA[$table_name], $table_name);
// $records = $mysql_connection->get_associative($table_name, $field_ID, $field[LINK_LABEL], $where, $limit, $field[LINK_SORT]);
$sort = $field[LINK_SORT];
if ($sort[0] == "-") {
	$sort = substr($sort, 1);
	$sort_order = "DESC";
}

// Construct a query that attempts to join associated tables, if LINK type fields are used in the label. */
$joined = array();
$record_label = $field[LINK_LABEL];
foreach ($label_fields AS $label_field) {
	if ($SCHEMA[$table_name][$label_field][FIELD_TYPE] == LINK) {
		$associated_table = $SCHEMA[$table_name][$label_field][LINK_TABLE];
		if (!in_array($associated_table, $joined)) {
			$associated_table_identifier = SchemaManager::get_unique_identifier($SCHEMA[$associated_table], $associated_table);
			// Alias joined table in case it is the same as $table_name
			$associated_table_alias = "{$label_field}_{$associated_table}";
			$associated_label = $SCHEMA[$table_name][$label_field][LINK_LABEL];
			// See if the label of the joined field is complex. Split it if so.
			if (preg_match($delimiters, $assocaited_label, $matches)) {
				$label_parts = preg_split($delimiters, $field[LINK_LABEL], -1, PREG_SPLIT_DELIM_CAPTURE);
				$associated_label = "";
				foreach ($label_parts AS $field_delim) {
					if (!preg_match($delimiters, $field_delim)) {
						$query_tables .= ", {$associated_table_alias}.{$field_delim} AS {$label_field}_{$field_delim}";
						$associated_label .= "{$label_field}_{$field_delim}";
						if (!isset($associated_sort_label))
							$associated_sort_label = $associated_label;
					}
					else $associated_label .= $field_delim;
				}
			}
			else {
				$query_tables .= ", {$associated_table_alias}.{$associated_label} AS {$label_field}_{$associated_label}";
				$associated_label = "{$label_field}_{$associated_label}";
				$associated_sort_label = $associated_label;
			}
			$query_join .= " LEFT JOIN {$associated_table} AS {$associated_table_alias} ON ({$associated_table_alias}.{$associated_table_identifier} = {$table_name}.{$label_field})";

			// If results are to be sorted by an associated record, sort by the defined label
			if ($sort == $label_field)
				$sort = $associated_sort_label;

			/* Replace the record's link field (ID) with it's defined label. */
			$record_label = str_replace($label_field, $associated_label, $record_label);
		}
	}
}

$records_query = "SELECT {$table_name}.*{$query_tables} FROM {$table_name}{$query_join} {$where}";
if ($sort) {
	$records_query .= "ORDER BY {$sort} {$sort_order}";
}
$results = $mysql_connection->sql($records_query);

// echo $records_query . "<p>" . $record_label . "<p>";

// TODO: Merge this with the 'foreach' loop below.
$records = array();
while ($results->has_next()) {
	$result = $results->next();
	$records[$result[$field_ID]] = $mysql_connection->get_row_label($result, $record_label);
}

$existing_records = $html->div();

$i = 0;
foreach ($records AS $ID => $label) {
	$row_class = ($i++%2) ? "row_odd" : "row_even";
	if (trim($label) == "")
		$label = "&lt; No Name &gt;";
	$record_checkbox = $html->checkbox()->value($ID)->class("add_existing_record");
	$record_link = $html->a()->href("javascript: addExistingSubtableRecord('{$parent_table_name}', '{$field_name}', $ID, '{$suffix}')")->content($label);
	$record_div = $html->div()->class("row {$row_class}")->add($record_checkbox)->add($record_link);
	$existing_records->add($record_div);
}

echo $existing_records->html();

?>