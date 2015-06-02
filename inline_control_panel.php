<?php

// include("authentication.php");

require_once("base/define.php");
require_once("base/HTML.php");
require_once("base/mysql_connection.php");
require_once("base/schema_manager.php");
require_once("base/settings.php");

$table = $_REQUEST['table'];
$entity_ID = $_REQUEST['id'];
$function = isset($_REQUEST['func']) ? $_REQUEST['func'] : "browse";

$style = array("schema/css/color_selector.css", "schema/css/calendar.css", "schema/css/form.css", "schema/css/util.css", "kernel/css/kernel_style.css");
$html = new HTML("{$SETTINGS['COMPANY_NAME']}: Control Panel", $style);

$html->import("schema/js/jquery.js");
$html->import("schema/js/color.js");
$html->import("schema/js/schema.js");
$html->import("schema/js/utils.js");
$html->import("schema/js/jquery.corner.js");


/* For Kernel Use. */
$html->import("schema/js/draggable.js");
$html->import("schema/js/MouseAdapter.js");
$html->import("schema/js/HttpRequest.js");
$html->import("schema/js/windowing.js");

$html->import("kernel/js/kernel.js");

$table_identifier = SchemaManager::get_table_unique_identifier($table);
$entity = $mysql_connection->get($table, "WHERE {$table_identifier} = '{$entity_ID}'");
$forward_page = "inline_control_panel.php?table={$table}&id={$entity_ID}";

$control_panel_div = SchemaManager::form($table, $entity, $forward_page);

$frame_div = $html->div()->add($control_panel_div);

$html->add($frame_div);

echo $html->html();

?>