<?php

require_once("../base/database_schema.php");

$include_page = $_REQUEST['copy_include'];
$copy = stripslashes($_REQUEST['copy']);
$copy_file_handle = fopen("../" . $include_page, "w");
fwrite($copy_file_handle, $copy);
fclose($copy_file_handle);

header("Location: ../control_panel.php?func=copy");
exit;

?>