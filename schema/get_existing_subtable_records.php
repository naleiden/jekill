<?php

require_once("../base/
require_once("../base/mysql_connection.php");

include("../authentication.php");

$field_name = mysql_real_escape_string($_REQUEST['field_name']);
$field_label = mysql_real_escape_string($_REQUEST['field_label']);
$table_name = mysql_real_escape_string($_REQUEST['table_name']);

$html = new HTML();

$fields = $mysql_connection->get_associative($table_name, "{$table_name}_ID", $field_label);

$i = 0;
$subtable_links_div = $html->div();
foreach ($fields AS $field_ID => $field_text) {
	$field_link = $html->a()->href("javascript: loadExistingSubtableRecord($field_ID, '{$field_name}', '{$table_name}')->content($field_text);
	$row_class = ($i++%2) ? "row_odd" : "row_even";
	$field_link_div = $html->div()->class("{$row_class}")->add($field_link);
	$subtable_links_div)->add($field_link_div);
}

echo $subtable_links_div->html();

?>