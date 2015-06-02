<?php

require_once("../base/database_schema.php");

$copy_label = $_REQUEST['copy_name'];

$copy_name = str_replace(" ", "_", strtolower($copy_label));
$copy_include_page = "{$copy_name}.inc";
$copy_name = strtoupper($copy_name);

$COPY[$copy_name] = array(FIELD_LABEL => $copy_label, COPY_INCLUDE_PAGE => $copy_include_page);

include("write_schema.php");

header("Location: ../control_panel.php?func=copy_form&table={$copy_name}");
exit;

?>