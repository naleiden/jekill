<?php

require_once("../base/database_schema.php");

$setting_label = $_REQUEST['setting_name'];

$setting_name = strtoupper($setting_label);
$setting_name = str_replace(" ", "_", $setting_name);

$_SETTINGS[$setting_name] = array(FIELD_LABEL => $setting_label);

include("write_schema.php");

?>