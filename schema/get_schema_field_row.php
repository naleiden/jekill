<?php

require_once("../base/HTML.php");
require_once("../base/schema_manager.php");

$field = array();
$field_num = $_REQUEST['field_num'];
$field_name = $_REQUEST['field_name'];
$table_name = $_REQUEST['table_name'];

$html = new HTML();

$field_label = str_replace("_", " ", $field_name);
$field_label = ucwords($field_label);

$field[FIELD_LABEL] = $field_label;

$field_row = SchemaManager::get_schema_field_row($table_name, $field_num, $field_name, $field);

echo $field_row->html();

?>