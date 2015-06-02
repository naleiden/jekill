<?php

include("../passive_authentication.php");

require_once("../base/HTML.php");
// require_once("../base/mysql_connection.php");
require_once("../base/util.php");

$table_name = $_REQUEST['table_name'];	// mysql_real_escape_string($_REQUEST['table_name']);
$field_name = $_REQUEST['field_name'];	// mysql_real_escape_string($_REQUEST['field_name']);
$suffix = $_REQUEST['suffix'];

$html = new HTML();

$records = include_capture("schema/get_subtable_records.php");		// ?table_name={$table_name}&field_name={$field_name}");

$search_input = $html->text()->id("existing_record_search")->onKeyUp("searchExistingSubtableRecords('{$field_name}', '{$table_name}')");
$search_div = $html->div()->content("Search<br />")->add($search_input);
$existing_records_div = $html->div()->id("subrecord_chooser")->content($records);

$add_records = $html->button()->value("Add")->onclick("addExistingSubtableRecords('{$table_name}', '{$field_name}', '{$suffix}')");
$control_div = $html->div()->add($add_records);

$records_div = $html->div()->add($search_div)->add($existing_records_div)->add($control_div);

echo $records_div->html();

?>