<?php

include("../passive_authentication.php");

require_once("../base/schema_manager.php");
require_once("../base/mysql_connection.php");

$table_name = $_REQUEST['table_name'];
$entity_ID = $_REQUEST['entity_ID'];
$field_name = $_REQUEST['field_name'];
$filename = $_GET['filename'];

$query = "UPDATE {$table_name} SET {$field_name} = '' WHERE {$table_name}_ID  = '{$entity_ID}'";
$mysql_connection->query($query);

// echo $query;

SchemaManager::delete_uploaded_file($table_name, $field_name, $entity_ID, $filename);

?>