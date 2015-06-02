<?php

include("../passive_authentication.php");

require_once("../base/schema_manager.php");

$table_name = mysql_real_escape_string($_REQUEST['table_name']);

SchemaManager::init_table($table_name);

?>