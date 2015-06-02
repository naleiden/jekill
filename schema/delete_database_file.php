<?php

include("../passive_authentication.php");

require_once("../base/mysql_connection.php");

$table_name = $_REQUEST['table_name'];
$entity_ID = $_REQUEST['entity_ID'];
$field_name = $_REQUEST['field_name'];

$query = "UPDATE {$table_name} SET {$field_name} = '' WHERE primkey = '{$entity_ID}'";
$mysql_connection->query($query);

?>