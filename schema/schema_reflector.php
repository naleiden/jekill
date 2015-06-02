<?php

require_once("../base/schema_manager.php");

$function = $_REQUEST['function'];

SchemaManager::$function($_REQUEST);

?>