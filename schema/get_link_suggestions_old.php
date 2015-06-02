<?php

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");
require_once("../base/util.php");
// TODO: Use filter() here for input.

session_start();

$table_name = mysql_real_escape_string($_POST['table_name']);
$field_name = mysql_real_escape_string($_POST['field_name']);
$suffix = mysql_real_escape_string($_POST['suffix']);
$keywords = mysql_real_escape_string($_POST['keywords']);

if (isset($_SESSION["{$table_name}_serialization"])) {
	$SCHEMA[$table_name] = SchemaManager::unserialize_table($table_name, $_SESSION["{$table_name}_serialization"]);
}

$field = $SCHEMA[$table_name][$field_name];

echo "<div class=\"link_suggestion_results\">";

$limit = ($field[LINK_LIMIT]) ? $field[LINK_LIMIT] : 15;

// TODO: LINK_ATTACHMENT
if (isset($field[LINK_TABLE])) {
	$link_table = $field[LINK_TABLE];
	$link_label = $field[LINK_LABEL];
	$link_where = $field[LINK_WHERE];
	$table_identifier = SchemaManager::get_table_unique_identifier($link_table);

	$regex = "/([^a-zA-Z0-9_]+)/";
	$matches = preg_split($regex, $link_label, -1, PREG_SPLIT_DELIM_CAPTURE);
	$label_fields = array();
	$concat_items = array();
	// TODO: Use SchemaManager::get_link_label_comparator() here.
	foreach ($matches AS $match_delimiter) {
		if (preg_match($regex, $match_delimiter)) {
			$concat_items[] = "'{$match_delimiter}'";
		}
 		else {
			$label_fields[] = $match_delimiter;
			$concat_items[] = $match_delimiter;
		}
	
	}
	$label_fields = implode(", ", $label_fields);
	$concat_items = implode(", ", $concat_items);

	$where_and = ($link_where != "") ? "AND" : "WHERE";

	$query = "SELECT {$table_identifier}, {$label_fields} FROM {$link_table} {$link_where} {$where_and} CONCAT({$concat_items}) LIKE '%{$keywords}%' LIMIT {$limit}";
// echo $query;
	$results = $mysql->sql($query);

	if ($results->has_next()) {
		while ($results->has_next()) {
			$result = $results->next();

			$result_ID = $result[$table_identifier];
			$result_text = $mysql->get_row_label($result, $link_label);
			// $mouseover = "onmouseover=\"cueSelectLinkSuggestion('{$table_name}', '{$field_name}', '{$suffix}', '{$result_text}', $result_ID)\"";
			echo "<div class=\"link_suggestion\" {$mouseover}><a href=\"javascript: selectLinkSuggestion('{$table_name}', '{$field_name}', '{$suffix}', '{$result_text}', $result_ID)\">{$result_text}</a></div>";
		}
	}
	else echo "Sorry, no results were found.";
}
else {
	$i = 0;
	foreach ($field[FIELD_OPTIONS] AS $value => $label) {
		if (preg_match("/^{$keywords}[.]*/", $label)) {
			$value = str_replace("'", "\'", $value);
			$label = str_replace("'", "\'", $label);

			echo "<div class=\"link_suggestion\" onmouseover=\"cueSelectLinkSuggestion('{$table_name}', '{$field_name}', '{$suffix}', '{$label}', '{$value}')\"><a href=\"javascript: selectLinkSuggestion('{$table_name}', '{$field_name}', '{$suffix}', '{$label}', '{$value}')\">{$value}</a></div>";
			if ($i++ > $limit)
				break;
		}
	}
}


echo "</div>";

?>