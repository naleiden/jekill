<?php

require_once("../base/schema_manager.php");

$table_name = $_REQUEST['table'];
$entity_ID = $_REQUEST['entity_ID'];

SchemaManager::delete($table_name, $entity_ID);

?>