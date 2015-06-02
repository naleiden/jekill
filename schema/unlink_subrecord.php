<?php

include("../passive_authentication.php");

require_once("../base/database_schema.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$table_name = mysql_real_escape_string($_REQUEST['table_name']);
$field_name = mysql_real_escape_string($_REQUEST['field_name']);
$field_type = $SCHEMA[$table_name][$field_name][FIELD_TYPE];
$entity_ID = mysql_real_escape_string($_REQUEST['entity_ID']);
$link_ID = mysql_real_escape_string($_REQUEST['link_ID']);


$link_table_name = $SCHEMA[$table_name][$field_name][LINK_TABLE];

$map_table_name = SchemaManager::get_map_table_name($table_name, $link_table_name, $field_name);

$map_link_table_ID = "{$link_table_name}_ID";
$map_table_ID = "{$table_name}_ID";
if ($table_name == $link_table_name) {	// || $field_type == LINK_MUTUAL)
	$map_table_ID = "one_ID";
	$map_link_table_ID = "two_ID";	
}

if ($field_type != LINK_MUTUAL)
	$query = "DELETE FROM {$map_table_name} WHERE {$map_link_table_ID} = '{$link_ID}' AND {$map_table_ID} = '{$entity_ID}'";
else $query = "DELETE FROM {$map_table_name} WHERE (one_ID = '{$link_ID}' AND two_ID = '{$entity_ID}') OR (one_ID = '{$entity_ID}' AND two_ID = '{$link_ID}')";

// echo $query;

$mysql_connection->query($query);

?>