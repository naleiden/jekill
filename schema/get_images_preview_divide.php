<?php

require_once("../base/HTML.php");
require_once("../base/schema_manager.php");

$field_name = $_REQUEST['field_name'];
$serialized = stripslashes($_REQUEST['serialized']);

$html = new HTML();

$preview_div = SchemaManager::get_images_preview_divide($field_name, $serialized);

echo $preview_div->html();

?>